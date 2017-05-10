<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/8/2017
 * Time: 12:48 PM
 */

namespace Adena\MailBundle\MailEngine;

use Doctrine\ORM\EntityManagerInterface;

class MailEngine
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function run(\Swift_Message $message, array $queues){
        // Get the senders
        $senders = $this->em->getRepository('AdenaMailBundle:Sender')->findBy(array('active' => 1));

        // Create SMTP Transport
        $transport = \Swift_SmtpTransport::newInstance();
        $transport
            ->setHost("ssl://smtp.gmail.com")
            ->setPort("465");

        // Because we use a specific transport, we can't use $this->get('mailer'), so we build our own
        // instance instead.
        $mailer = \Swift_Mailer::newInstance($transport);

        // Allows us to loop infinitely on our senders array (goes back to the beginning if reached the end)
        $infiniteSenders = new \InfiniteIterator(new \ArrayIterator($senders));
        // Loop on all the email addresses
        foreach($queues as $queue){
            // Get the next sender
            $infiniteSenders->next();
            $currentSender = $infiniteSenders->current();

            // Connect to the new current sender
            $transport
                ->setUsername($currentSender->getEmail())
                ->setPassword($currentSender->getPassword())
                ->stop() // stop() forces SwiftMailer to re-connect with the new information
            ;

            // The message recipient
            $message
                ->setTo($queue['email']);

            // Send it!
            if($mailer->send($message) > 0){
                // Successfully sent, delete it
                $this->em->getRepository('AdenaMailBundle:Queue')->removeById($queue['id']);
            }
        }
    }
}