<?php

namespace Adena\MailBundle\MailEngine;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;

class MailEngine
{
    private $em;
    private $logsDir;
    /** @var  \InfiniteIterator */
    private $senders;
    private $logName;
    private $errorLogName;
    /** @var \Swift_mailer */
    private $mailer;
    /** @var \Swift_SmtpTransport */
    private $transport;
    private $initialized;
    /** @var \Monolog\Logger */
    private $logger;

    public function __construct(EntityManagerInterface $em, Logger $logger, $kernelLogDir)
    {
        $this->em = $em;
        $this->logsDir = $kernelLogDir;
        $this->initialized = false;
        $this->logger = $logger;
    }

    public function initialize()
    {
        // Get the senders
        // Allows us to loop infinitely on our senders array (goes back to the beginning if reached the end)
        $this->senders = new \InfiniteIterator(
            new \ArrayIterator($this->em->getRepository('AdenaMailBundle:Sender')->findBy(array('active' => 1)))
        );

        // Create SMTP Transport
        $this->transport = \Swift_SmtpTransport::newInstance();
        $this->transport
            ->setHost("ssl://smtp.gmail.com")
            ->setPort("465");

        // Because we use a specific transport, we can't use $this->get('mailer'), so we build our own
        // instance instead.
        $this->mailer = \Swift_Mailer::newInstance($this->transport);

        $this->initialized = true;
    }

    /**
     * @param \Swift_Message $message We expect everything to be already set in the $message parameter.
     *
     * @return bool
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Message $message){
        if(!$this->initialized){
            $this->initialize();
        }

        // The goal here is to test sending the email until we tried all the senders or the email is sent.
        // It looks like we are looping indefinitely, but we are not.
        // Three things can happen:
        // 1: We raised no exception while sending the email, and we return TRUE or FALSE, thus exiting the loop
        // 2: We raised an exception and we removed the current sender from the list. When we have no more senders
        // available, we throw an exception, thus exiting the loop (if we have senders remaining, we keep looping,
        // which is exactly the behavior wanted).
        // 3 : The connection timed out more than $tries times, we exit the loop.
        $tries = 5;
        $currentTry = 0;
        while(true) {
            // Get the next sender
            $this->senders->next();
            $currentSender = $this->senders->current();

            // Connect to the new current sender
            $this->transport
                ->setUsername($currentSender->getEmail())
                ->setPassword($currentSender->getPlainPassword())
                ->stop() // stop() forces SwiftMailer to re-connect with the new information
            ;

            // Send it!
            try {
                if ($this->mailer->send($message) > 0) {
                    // Reset current try
                    $currentTry = 0;
                    return TRUE;
                }
                return FALSE;
            } catch (\Swift_TransportException $e) {
                switch ($e->getCode()) {
                    // Invalid Login
                    case 535:
                        $this->logger->warning("Error 535: Login for sender " . $currentSender->getName() . " invalid.");
                        $this->_removeCurrentSender();
                        break;

                    // Email limit exceeded
                    case 550:
                        $this->logger->warning("Error 550: Limit exceeded for " . $currentSender->getName());
                        $this->_removeCurrentSender();
                        break;

                    default:
                        // Test if it's a Timed Out error
                        if(stristr($e->getMessage(), 'timed out')){
                            if($currentTry >= $tries){
                                $this->logger->error("STOPPING: Time outed too many times.");
                                throw new \Swift_TransportException('Timed out too many times.');
                            }

                            $currentTry++;
                            continue;
                        }else {
                            // We don't know how to handle this exception, let's log it and stop sending.
                            $this->logger->error("STOPPING: Unhandled Swift_TransportException: " . $e->getCode() . " : " . $e->getMessage());
                            throw $e;
                        }
                        break;
                }
            }
        }
    }

    private function _removeCurrentSender(){
        // Remove from the list
        /** @var $arrayIterator  \ArrayIterator*/
        $arrayIterator = $this->senders->getInnerIterator();
        $arrayIterator->offsetUnset($this->senders->key());

        // If no more senders available
        if(count($arrayIterator) < 1) {
            // Throw an error so we can stop sending emails (we can't send them anyways)
            $this->logger->error("STOPPING: No more senders available.");
            throw new \Swift_TransportException('No more senders available.');
        }
    }
}
