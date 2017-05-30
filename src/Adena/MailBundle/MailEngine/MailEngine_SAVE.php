<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/8/2017
 * Time: 12:48 PM
 */

namespace Adena\MailBundle\MailEngine;

use Adena\MailBundle\Entity\MailEngineInstance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class MailEngine
{
    private $em;
    private $logsDir;
    /** @var  \InfiniteIterator */
    private $senders;
    private $queues;
    private $logName;
    private $errorLogName;
    /** @var  \Adena\MailBundle\Entity\MailEngineInstance */
    private $instance;

    public function __construct(EntityManagerInterface $em, $kernelLogDir)
    {
        $this->em = $em;
        $this->logsDir = $kernelLogDir;
    }

    /**
     * @param \Swift_Message $message Only the "to" part of the message will be set here, we expect everything else
     *                                to be already set in the $message parameter.
     * @param array          $queues  Should be an array of ARRAYS, not objects.
     *
     * @param string         $logName
     *
     * @throws \Swift_TransportException
     */
    public function run(\Swift_Message $message, array $queues, $logName = "default")
    {
        $this->instance = new MailEngineInstance();
        $this->instance->setCampaign('???');

        $this->logName = $this->logsDir."/mail_engine_".$logName.".log";
        $this->errorLogName = $this->logsDir."/mail_engine_".$logName.".error.log";
        $this->queues = $queues;

        // Get the senders
        // Allows us to loop infinitely on our senders array (goes back to the beginning if reached the end)
        $this->senders = new \InfiniteIterator(
            new \ArrayIterator($this->em->getRepository('AdenaMailBundle:Sender')->findBy(array('active' => 1)))
        );

        // Create SMTP Transport
        $transport = \Swift_SmtpTransport::newInstance();
        $transport
            ->setHost("ssl://smtp.gmail.com")
            ->setPort("465");

        // Because we use a specific transport, we can't use $this->get('mailer'), so we build our own
        // instance instead.
        $mailer = \Swift_Mailer::newInstance($transport);

        // Loop on all the email addresses
        // todo add check if still running
        $run = true;
        while(!empty($this->queues) && $run){
            // Get the queue
            $queue = end($this->queues);

            // Get the next sender
            $this->senders->next();
            $currentSender = $this->senders->current();

            // Connect to the new current sender
            $transport
                ->setUsername($currentSender->getEmail())
                ->setPassword($currentSender->getPlainPassword())
                ->stop() // stop() forces SwiftMailer to re-connect with the new information
            ;
            try {

                // The message recipient
                $message
                    ->setTo($queue['email']);
            }catch(\Swift_RfcComplianceException $e){
                // Email incorrect, delete it from the Queue and the $this->queues array
                $this->_removeFromQueue($queue);
                // Log it
                file_put_contents($this->logName, 'ERROR EMAIL INVALID '.$queue['email'].PHP_EOL, FILE_APPEND);
                // Continue the loop without doing anything else
                continue;
            }


            // Send it!
            try {
                if ($mailer->send($message) > 0) {
                    // Successfully sent, delete it from the Queue and the $this->queues array
                    $this->_removeFromQueue($queue);

                    // Log it
                    file_put_contents($this->logName, $queue['email'].PHP_EOL, FILE_APPEND);
                }
            }catch(\Swift_TransportException $e){
                switch($e->getCode()){
                    // Invalid Login
                    case 535:
                        file_put_contents($this->errorLogName, "Error 535: Login for sender ".$currentSender->getName()." invalid".PHP_EOL, FILE_APPEND);
                        $this->_removeCurrentSender();
                        break;

                    // Email limit exceeded
                    case 550:
                        file_put_contents($this->errorLogName, "Error 550: Limit exceeded for ".$currentSender->getName().PHP_EOL, FILE_APPEND);
                        $this->_removeCurrentSender();
                        break;

                    default:
                        file_put_contents($this->errorLogName, "Unhandled Swift_TransportException: ".$e->getCode()." : ".$e->getMessage().PHP_EOL, FILE_APPEND);
                        throw $e;
                        break;
                }
            }
        }
    }

    private function _removeFromQueue($queue)
    {
        $this->em->getRepository('AdenaMailBundle:Queue')->removeById($queue['id']);
        array_pop($this->queues);
    }

    private function _removeCurrentSender(){
        // Remove from the list
        /** @var $arrayIterator  \ArrayIterator*/
        $arrayIterator = $this->senders->getInnerIterator();
        $arrayIterator->offsetUnset($this->senders->key());

        // If no more senders available
        if(count($arrayIterator) < 1) {
            // Throw an error so we can stop sending emails (we can't send them anyways)
            file_put_contents($this->errorLogName, "No more senders available".PHP_EOL, FILE_APPEND);
            throw new \Swift_TransportException('No more senders available.');
        }
    }
}