<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\ActionControl\CampaignActionControl;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\MailEngine\MailEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class CampaignSender
{

    private $em;
    private $mailEngine;
    private $campaignToQueue;
    private $campaignActionControl;
    private $logDirs;

    public function __construct(
        EntityManagerInterface $em,
        MailEngine $mailEngine,
        CampaignToQueue $campaignToQueue,
        CampaignActionControl $campaignActionControl,
        $logsDir)
    {
        $this->mailEngine = $mailEngine;
        $this->em            = $em;
        $this->campaignToQueue = $campaignToQueue;
        $this->campaignActionControl = $campaignActionControl;
        $this->logsDir = $logsDir;
    }

    public function test(Campaign $campaign)
    {

        // If the campaign cannot be test, we have to stop the script and throw an exception
        if(!$this->campaignActionControl->isAllowed("test", $campaign)){
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
        // If the campaign cannot be start_resume, we have to stop the script and throw an exception
        if(!$this->campaignActionControl->isAllowed("start_resume", $campaign)){
            throw new \InvalidArgumentException('The provided campaign cannot be sent because its status is: '.$campaign->getStatus());
        }

        // If the campaign can be send, we need to create the queue before sending it
        if($this->campaignActionControl->isAllowed("send", $campaign)){
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
        }catch(Exception $e){
            // The send was interrupted, let's pause the campaign
            $campaign->setStatus($errorStatus);
            file_put_contents($this->logsDir."/mail_engine_".$logName.".error.log", "Unhandled exception: ".$e->getCode()." : ".$e->getMessage().PHP_EOL, FILE_APPEND);
            throw $e;
        }finally{
            $this->em->flush();
        }
    }

}