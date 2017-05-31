<?php

namespace Adena\MailBundle\MailEngine;

use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(EntityManagerInterface $em, $kernelLogDir)
    {
        $this->em = $em;
        $this->logsDir = $kernelLogDir;
        $this->initialized = false;
    }

    /**
     * @param string         $logName
     */
    public function initialize($logName = "default")
    {
        $this->logName = $this->logsDir."/mail_engine_".$logName.".log";
        $this->errorLogName = $this->logsDir."/mail_engine_".$logName.".error.log";

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
     * @param                $logName
     *
     * @return bool
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Message $message, $logName = "default"){
        if(!$this->initialized){
            $this->initialize($logName);
        }

        // The goal here is to test sending the email until we tried all the senders or the email is sent.
        // It looks like we are looping indefinitely, but we are not.
        // Two things can happen:
        // 1: We raised no exception while sending the email, and we return TRUE or FALSE, thus exiting the loop
        // 2: We raised an exception and we removed the current sender from the list. When we have no more senders
        // available, we throw an exception, thus exiting the loop (if we have senders remaining, we keep looping,
        // which is exactly the behavior wanted).
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
                    return TRUE;
                }
                return FALSE;
            } catch (\Swift_TransportException $e) {
                switch ($e->getCode()) {
                    // Invalid Login
                    case 535:
                        file_put_contents($this->errorLogName, "Error 535: Login for sender " . $currentSender->getName() . " invalid" . PHP_EOL, FILE_APPEND);
                        $this->_removeCurrentSender();
                        break;

                    // Email limit exceeded
                    case 550:
                        file_put_contents($this->errorLogName, "Error 550: Limit exceeded for " . $currentSender->getName() . PHP_EOL, FILE_APPEND);
                        $this->_removeCurrentSender();
                        break;

                    default:
                        file_put_contents($this->errorLogName, "Unhandled Swift_TransportException: " . $e->getCode() . " : " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                        throw $e;
                        break;
                }
            } catch (\Swift_IoException $e) {

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
            file_put_contents($this->errorLogName, "No more senders available".PHP_EOL, FILE_APPEND);
            throw new \Swift_TransportException('No more senders available.');
        }
    }
}
