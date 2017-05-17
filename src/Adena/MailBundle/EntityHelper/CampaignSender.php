<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\MailEngine\MailEngine;
use Doctrine\ORM\EntityManagerInterface;

class CampaignSender
{

    private $em;
    private $mailEngine;
    private $campaignToQueue;

    public function __construct(EntityManagerInterface $em, MailEngine $mailEngine, CampaignToQueue $campaignToQueue)
    {
        $this->mailEngine = $mailEngine;
        $this->em            = $em;
        $this->campaignToQueue = $campaignToQueue;
    }

    public function test(Campaign $campaign)
    {
        // We can only test new and already tested campaigns.
        if(!in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED,
            ]
        )){
            throw new \InvalidArgumentException('The provided campaign cannot be sent because its status is: '.$campaign->getStatus());
        }

        // Empty the queue for that campaign
        $this->campaignToQueue->emptyQueue($campaign);

        // Run the CampaignToQueue service for test email
        $this->campaignToQueue->createTestEmailQueue($campaign);

        // Change Campaign status to TESTING
        $campaign->setStatus(Campaign::STATUS_TESTING);
        $this->em->flush();

        $logName = $campaign->getId()."_test_";
        $this->_doSend($campaign, Campaign::STATUS_TESTED, Campaign::STATUS_NEW, $logName);
    }

    public function startResume(Campaign $campaign)
    {
        // Only new, tested or paused campaigns can be started or resumed.
        if(!in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED,
                Campaign::STATUS_PAUSED
            ]
        )){
            throw new \InvalidArgumentException('The provided campaign cannot be sent because its status is: '.$campaign->getStatus());
        }

        // If the campaign is currently in NEW orTESTED status, we need to create the queue before sending it
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED,
            ]
        )){
            // Empty the queue for that campaign
            $this->campaignToQueue->emptyQueue($campaign);

            // Run the CampaignToQueue service
            $this->campaignToQueue->createQueue($campaign);

            // Update the campaign sent at time
            $campaign->setSentAt(new \DateTime());
        }

        // Campaign is sending...
        $campaign->setStatus(Campaign::STATUS_IN_PROGRESS);
        $this->em->flush();

        // If it's not new or tested (so it's paused), just restart the engine
        $logName = $campaign->getId()."_".$campaign->getSentAt()->format('Ymd');
        $this->_doSend($campaign, Campaign::STATUS_ENDED, Campaign::STATUS_PAUSED, $logName);
    }

    private function _doSend(Campaign $campaign, $successStatus, $errorStatus, $logName)
    {
        // Get the queue for the specified campaign AS ARRAYS, not objects
        $queues = $this->em->getRepository('AdenaMailBundle:Queue')->getAsArrayByCampaign($campaign);

        // The parameters common to each message (email) sent
        $message = \Swift_Message::newInstance();
        $message
            ->setSubject($campaign->getEmail()->getSubject())
            ->setFrom('account@land-fx.com', 'Land-FX')
            ->setBody(
                $campaign->getEmail()->getTemplate(),
                'text/html'
            );

        // Run the mail engine
        try {
            $this->mailEngine->run($message, $queues, $logName);

            // Done with this campaign, change the status.
            $campaign->setStatus($successStatus);
        }catch(\Swift_TransportException $e) {
            // The send was interrupted, let's pause the campaign
            $campaign->setStatus($errorStatus);
        }finally{
            $this->em->flush();
        }
    }
}