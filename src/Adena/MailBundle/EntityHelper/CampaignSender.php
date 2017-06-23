<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\ActionControl\CampaignActionControl;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Entity\SendersList;
use Adena\MailBundle\MailEngine\MailEngine;
use Adena\MailBundle\Queue\QueueDatabaseIterator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class CampaignSender
{
    const INTERRUPTED = 'interrupted';
    const SUCCESS = 'success';
    const PAUSED = 'paused';


    private $em;
    private $mailEngine;
    private $campaignToQueue;
    private $campaignActionControl;
    /** @var \Adena\MailBundle\Queue\QueueDatabaseIterator */
    private $queueDatabaseIterator;
    private $logsDir;
    private $logsLocation;

    public function __construct(
        EntityManagerInterface $em,
        MailEngine $mailEngine,
        CampaignToQueue $campaignToQueue,
        CampaignActionControl $campaignActionControl,
        QueueDatabaseIterator $queueDatabaseIterator,
        $logsDir)
    {
        $this->mailEngine = $mailEngine;
        $this->em            = $em;
        $this->campaignToQueue = $campaignToQueue;
        $this->campaignActionControl = $campaignActionControl;
        $this->queueDatabaseIterator = $queueDatabaseIterator;
        $this->logsDir = $logsDir;
    }

    public function pause(Campaign $campaign){
        if(!$this->campaignActionControl->isAllowed("pause", $campaign)){
            throw new \InvalidArgumentException('This campaign cannot be paused its status is : '.$campaign->getStatus());
        }

        $campaign->setStatus(Campaign::STATUS_PAUSED);
        $this->em->flush();
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

        $this->logsLocation = $this->logsDir."/campaign_".$campaign->getName()."_".$campaign->getId()."_test.log";
        $result = $this->_doSend($campaign);
        if($result === self::SUCCESS) {
            // Done with this campaign, change the status.
            $campaign->setStatus(Campaign::STATUS_TESTED);
        }else if($result === self::INTERRUPTED){
            // Something happened, pause the campaign
            $campaign->setStatus(Campaign::STATUS_NEW);
        }
        $this->em->flush();
    }

    public function startResume(Campaign $campaign)
    {
        // If the campaign cannot be start_resume, we have to stop the script and throw an exception
        if(!$this->campaignActionControl->isAllowed("start_resume", $campaign)){
            throw new \InvalidArgumentException('The provided campaign cannot be sent because its status is: '.$campaign->getStatus());
        }

        // If the campaign can be send, we need to create the queue before sending it
        if($this->campaignActionControl->isAllowed("start", $campaign)){
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
        $this->logsLocation = $this->logsDir."/campaign_".$campaign->getName()."_".$campaign->getId().".log";
        $result = $this->_doSend($campaign);
        if($result === self::SUCCESS) {
            // Done with this campaign, change the status.
            $campaign->setStatus(Campaign::STATUS_ENDED);
        }else if($result === self::INTERRUPTED){
            // Something happened, pause the campaign
            $campaign->setStatus(Campaign::STATUS_PAUSED);
        }
        $this->em->flush();
    }

    private function _doSend(Campaign $campaign)
    {
        // Get the queue for the specified campaign AS ARRAYS, not objects
        $this->queueDatabaseIterator->setQueues(
            $this->em->getRepository('AdenaMailBundle:Queue')->getAsArrayByCampaign($campaign)
        );

        /** @var SendersList $sendersList */
        $sendersList = $this->em->getRepository('AdenaMailBundle:SendersList')->getWithSenders($campaign->getSendersList()->getId());

        $fromEmail = $campaign->getFromEmail() ?? $sendersList->getFromEmail();
        $fromName = $campaign->getFromName() ?? $sendersList->getFromName();

        $this->mailEngine->initialize($sendersList->getSenders()->toArray());
        foreach($this->queueDatabaseIterator as $queue){
            $this->em->refresh($campaign);
            if(!$this->campaignActionControl->isAllowed('send', $campaign)){
                return self::PAUSED;
            }

            try {
                $message = \Swift_Message::newInstance();
                $message
                    ->setSubject($campaign->getEmail()->getSubject())
                    ->setTo($queue['email'])
                    ->setFrom($fromEmail, $fromName)
                    ->setBody(
                        $campaign->getEmail()->getTemplate(),
                        'text/html'
                    );

                if($this->mailEngine->send($message)){
                    $this->_log($queue['email']);
                }
            }catch(\Swift_RfcComplianceException $e){ // Invalid email address
                // Log it
                $this->_log("ERROR EMAIL INVALID ".$queue['email']);
                // Continue the loop without doing anything else (will skip the invalid email)
                continue;
            }catch(\Swift_TransportException $e) {
                return self::INTERRUPTED;
            }catch(Exception $e){
                $this->_log("Unhandled exception: ".$e->getCode()." : ".$e->getMessage());
                return self::INTERRUPTED;
            }
        }
        return self::SUCCESS;
    }

    private function _log($message){
        file_put_contents($this->logsLocation, "[".date('Y-m-d H:i:s')."] ".$message.PHP_EOL, FILE_APPEND);
    }
}