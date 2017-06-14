<?php

namespace Adena\PaginatorBundle\Tests\DataFixtures\ORM;

use Adena\PaginatorBundle\Tests\Entity\ArticleTest;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleTestData implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {

        $data = new ArticleTest();
        $data->setName('1');
        $manager->persist($data);

        $data = new ArticleTest();
        $data->setName('2');
        $manager->persist($data);

        $data = new ArticleTest();
        $data->setName('3');
        $manager->persist($data);

        $data = new ArticleTest();
        $data->setName('4');
        $manager->persist($data);

        $manager->flush();
    }
}