<?php

namespace Adena\TestBundle\Tests\DataFixtures\ORM;

use Adena\MailBundle\Entity\SendersList;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSendersList implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < 13; $i++){
            $sendersList = new SendersList();
            $sendersList->setName($i);
            $sendersList->setFromEmail($i);
            $sendersList->setFromName($i);
            $manager->persist($sendersList);
        }

        $manager->flush();
    }
}