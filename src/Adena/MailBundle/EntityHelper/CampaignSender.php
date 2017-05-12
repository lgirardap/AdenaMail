<?php

namespace Adena\MailBundle\EntityHelper;

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
        /** @var \Adena\MailBundle\Entity\Campaign $campaign */
        $campaign = $this->em->getRepository('AdenaMailBundle:Campaign')->getWithEmail($campaignId);

        // Get the queue for the specified campaign AS ARRAYS, not objects
        $queues = $this->em->getRepository('AdenaMailBundle:Queue')->getAsArrayForCampaign($campaign);

        // The parameters common to each message (email) sent
        $message = \Swift_Message::newInstance();
        $message
            ->setSubject($campaign->getEmail()->getSubject())
            ->setFrom('account@land-fx.com', 'Land-FX')
            ->setBody(
                $campaign->getEmail()->getTemplate(),
                'text/html'
            );

        $this->mailEngine->run($message, $queues);
    }
}