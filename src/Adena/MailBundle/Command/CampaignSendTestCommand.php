<?php

namespace Adena\MailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CampaignSendTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('adenamail:campaign:sendtest')

            // the short description shown while running "php bin/console list"
            ->setDescription('Tests sending the campaign provided')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Requires a Campaign ID')

            ->addArgument('campaign_id', InputArgument::REQUIRED, 'The Campaign ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $campaign = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('AdenaMailBundle:Campaign')
            ->getWithEmail($input->getArgument('campaign_id'));

        if(!$campaign){
            $output->writeln('Invalid campaign!');
        }else {
            $this->getContainer()->get('adena_mail.entity_helper.campaign_sender')->test($campaign);
        }
    }
}