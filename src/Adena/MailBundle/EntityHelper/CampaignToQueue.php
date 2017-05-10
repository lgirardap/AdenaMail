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

    public function __construct(EntityManagerInterface $em, MailingListEmailsFetcher $emailsFetcher)
    {
        $this->emailsFetcher = $emailsFetcher;
        $this->em            = $em;
    }

    /**
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     *
     * @return array - We return the Emails Queue array
     */
    public function createQueue(Campaign $campaign)
    {
        // Get the associated mailinglists
        $mailingLists = $campaign->getMailingLists();

        $emails = array();
        foreach ($mailingLists as $mailingList) {
            // Fetch the emails associated to the mailing list using the mailing list email fetcher Library
            $newEmails = $this->emailsFetcher->fetch($mailingList);
            $emails = array_merge($emails, $newEmails);
        }

        // Remove duplicates
        $emails = $this->removeDuplicates($emails);

        // Create the Queue rows needed
        $this->em->getRepository('AdenaMailBundle:Queue')->nativeBulkInsertForCampaign($emails, $campaign);
    }

    /**
     * @param $emails
     *
     * @return array
     */
    private function removeDuplicates($emails): array
    {
        // Remove duplicate emails from the list
        $uniques = [];
        foreach ($emails as $key => $email) {
            $uniques[$email] = true;
        }
        $emails  = array_keys($uniques);
        $uniques = null;

        return $emails;
    }
}