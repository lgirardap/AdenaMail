<?php

namespace Adena\TestBundle\Tests\DataFixtures\ORM;

use Adena\TestBundle\Tests\Entity\FixturesLoaderTest;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FixturesLoaderTestData2 implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {

        $data = new FixturesLoaderTest();
        $data->setData1('data2-1');
        $data->setData2('data2-2');

        $manager->persist($data);
        $manager->flush();

    }
}