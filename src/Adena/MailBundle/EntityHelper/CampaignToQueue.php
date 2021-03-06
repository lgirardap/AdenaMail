<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/8/2017
 * Time: 12:48 PM
 */

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\Entity\Campaign;
use Doctrine\ORM\EntityManagerInterface;

class CampaignToQueue
{

    private $em;
    private $emailsFetcher;

    public function __construct(EntityManagerInterface $em, MailingListDataFetcher $emailsFetcher)
    {
        $this->emailsFetcher = $emailsFetcher;
        $this->em            = $em;
    }

    /**
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     */
    public function createQueue(Campaign $campaign)
    {
        // Get the associated mailingLists
        $mailingLists = $campaign->getMailingLists();
        $emailsCount = $this->_createQueues($mailingLists, $campaign);

        // Update the number of emails for this campaign
        $campaign->setEmailsCount($emailsCount);
        $this->em->flush();
    }

    public function createTestEmailQueue( Campaign $campaign)
    {
        // Get the associated mailingLists
        $mailingLists = $campaign->getTestMailingLists();

        $this->_createQueues($mailingLists, $campaign);
    }

    public function emptyQueue(Campaign $campaign){
        $this->em->getRepository('AdenaMailBundle:Queue')->removeAllForCampaign($campaign);
    }

    private function _createQueues($mailingLists, Campaign $campaign){
        $queues = array();
        foreach ($mailingLists as $mailingList) {
            // Fetch the emails associated to the mailing list using the mailing list email fetcher Library
            $newQueues = $this->emailsFetcher->fetch($mailingList);
            $queues = array_merge($queues, $newQueues);
        }

        // Remove duplicates
        $queues = $this->removeDuplicates($queues);

        // Create the Queue rows needed
        $this->em->getRepository('AdenaMailBundle:Queue')->nativeBulkInsertForCampaign($queues, $campaign);
        return count($queues);
    }
    
    /**
     * @param $rows
     *
     * @return array
     */
    private function removeDuplicates($rows): array
    {
        // Remove duplicate emails from the list
        $uniques = [];
        foreach ($rows as $key => $row) {

            $email = strtolower($row['email']);

            if(in_array($email, $uniques)){
                unset($rows[$key]);
                continue;
            }
            $uniques[] = $email;
        }
        $uniques = null;

        return $rows;
    }
}