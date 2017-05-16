<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\MailEngine\MailEngine;
use Doctrine\ORM\EntityManagerInterface;

class CampaignSender
{

    private $em;
    private $mailEngine;

    public function __construct(EntityManagerInterface $em, MailEngine $mailEngine)
    {
        $this->mailEngine = $mailEngine;
        $this->em            = $em;
    }

    public function send($campaignId)
    {
        // Get the campaign with the Email already loaded
        $campaign = $this->em->getRepository('AdenaMailBundle:Campaign')->getWithEmail($campaignId);

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
            $this->mailEngine->run($message, $queues, $campaign->getId()."_".$campaign->getSentAt()->format('Ymd'));

            // Done with this campaign, change the status.
            $campaign->setStatus(Campaign::STATUS_ENDED);
        }catch(\Swift_TransportException $e) {
            // The send was interrupted, let's pause the campaign
            $campaign->setStatus(Campaign::STATUS_PAUSED);
        }finally{
            $this->em->flush();
        }
    }
}