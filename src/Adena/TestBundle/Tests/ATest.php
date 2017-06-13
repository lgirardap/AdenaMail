<?php

namespace Adena\TestBundle\Tests;

use Adena\TestBundle\FixturesLoader\FixturesLoader;
use Adena\TestBundle\Tests\Entity\FixturesLoaderTest;
use Adena\TestBundle\Tests\ORMTestCase;
use Adena\TestBundle\Tests\Repository\FixturesLoaderTestRepository;

class Atest extends ORMTestCase
{
    /**
     * @var FixturesLoaderTestRepository $fixtureTestRepository
     */
    private $fixtureTestRepository;

    /**
     * @var FixturesLoader $fixtureLoader
     */
    private $fixtureLoader;

    /**
     * {@inheritDoc}
     */

    protected function setUp()
    {
        $this->getMockSqliteEntityManager();

        $data = new FixturesLoaderTest();
        $data->setData1('data1');
        $data->setData2('data2');
        $this->em->persist($data);

        $data = new FixturesLoaderTest();
        $data->setData1('data1');
        $data->setData2('data2');
        $this->em->persist($data);

        $data = new FixturesLoaderTest();
        $data->setData1('data1');
        $data->setData2('data2');
        $this->em->persist($data);

        $this->em->persist($data);
        $this->em->flush();
    }

    public function testBla(){
//        $qb = $this->em->createQueryBuilder();
//        $qb
//            ->select('a')
//            ->from('Adena\TestBundle\Tests\Entity\FixturesLoaderTest', 'a');
        
        $rep = $this->em->getRepository('Adena\TestBundle\Tests\Entity\FixturesLoaderTest');

        $this->assertCount(1, $rep->coolMethode()->getQuery()->getResult());
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {

    }

    /**
     * Get a list of used fixture classes
     *
     * @return array
     */
    protected function getUsedEntityFixtures()
    {
        return [
            'Adena\TestBundle\Tests\Entity\FixturesLoaderTest'
        ];
    }
}