<?php

namespace Adena\MailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CampaignSendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('adenamail:campaign:send')

            // the short description shown while running "php bin/console list"
            ->setDescription('Sends the campaign provided')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Requires a Campaign ID')

            ->addArgument('campaign_id', InputArgument::REQUIRED, 'The Campaign ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // Get the campaign with the Email already loaded
        /** @var \Adena\MailBundle\Entity\Campaign $campaign */
        $campaign = $em->getRepository('AdenaMailBundle:Campaign')->getWithEmail($input->getArgument('campaign_id'));

        // Get the queue for the specified campaign AS ARRAYS, not objects
        $queues = $em->getRepository('AdenaMailBundle:Queue')->getAsArrayForCampaign($campaign);

        // The parameters common to each message (email) sent
        $message = \Swift_Message::newInstance();
        $message
            ->setSubject($campaign->getEmail()->getSubject())
            ->setFrom('account@land-fx.com', 'Land-FX')
            ->setBody(
                $campaign->getEmail()->getTemplate(),
                'text/html'
            );

        $this->getContainer()->get('adena_mail.mail_engine')->run($message, $queues);
    }
}