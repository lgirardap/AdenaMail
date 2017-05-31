<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\ActionControl\CampaignActionControl;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\MailEngine\MailEngine;
use Adena\MailBundle\Queue\QueueDatabaseIterator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class CampaignSender
{

    private $em;
    private $mailEngine;
    private $campaignToQueue;
    private $campaignActionControl;
    /**
     * @var \Adena\MailBundle\Queue\QueueDatabaseIterator
     */
    private $queueDatabaseIterator;
    private $logsDir;

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
        $logName = $campaign->getId()."_".$campaign->getSentAt()->format('Ymd');
        $this->_doSend($campaign, Campaign::STATUS_ENDED, Campaign::STATUS_PAUSED, $logName);
    }

    private function _doSend(Campaign $campaign, $successStatus, $errorStatus, $logName)
    {
        // Get the queue for the specified campaign AS ARRAYS, not objects
        $this->queueDatabaseIterator->setQueues(
            $this->em->getRepository('AdenaMailBundle:Queue')->getAsArrayByCampaign($campaign)
        );

        $success = TRUE;
        foreach($this->queueDatabaseIterator as $queue){

            $this->em->refresh($campaign);
            if(!$this->campaignActionControl->isAllowed('send', $campaign)){
                return;
            }

            try {
                $message = \Swift_Message::newInstance();
                $message
                    ->setSubject($campaign->getEmail()->getSubject())
                    ->setTo($queue['email'])
                    ->setFrom('account@land-fx.com', 'Land-FX')
                    ->setBody(
                        $campaign->getEmail()->getTemplate(),
                        'text/html'
                    );

                if($this->mailEngine->send($message)){
                    file_put_contents($this->logsDir."/mail_engine_".$logName.".log", $queue['email'].PHP_EOL, FILE_APPEND);
                }
            }catch(\Swift_RfcComplianceException $e){ // Invalid email address
                // Log it
                file_put_contents($logName, 'ERROR EMAIL INVALID '.$queue['email'].PHP_EOL, FILE_APPEND);
                // Continue the loop without doing anything else (will skip the invalid email)
                continue;
            }catch(\Swift_TransportException $e) {
                // The send was interrupted, let's pause the campaign
                $success = false;
                break;
            }catch(Exception $e){
                // The send was interrupted, let's pause the campaign
                $success = false;
                file_put_contents($this->logsDir."/mail_engine_".$logName.".error.log", "Unhandled exception: ".$e->getCode()." : ".$e->getMessage().PHP_EOL, FILE_APPEND);
                break;
            }
        }
        // Done with this campaign, change the status.
        $campaign->setStatus($success ? $successStatus : $errorStatus);
        $this->em->flush();
    }
}