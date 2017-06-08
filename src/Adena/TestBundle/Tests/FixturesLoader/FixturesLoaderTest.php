<?php

namespace Adena\TestBundle\Tests\ActionControl;

use Adena\TestBundle\FixturesLoader\FixturesLoader;
use Adena\TestBundle\Tests\Repository\FixturesLoaderTestRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\PHPDriver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FixturesLoaderTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */

    protected function setUp()
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

//        $this->em
    }

    public function testLoadFixtures()
    {
        $fixtureLoader = static::$kernel->getContainer()->get('adena_test.fixtures_loader');
        $this->assertInstanceOf(FixturesLoader::class, $fixtureLoader);


        // ==== Test import global
        // When we get fixture, we want to load the data in database and check if the data are accurate
        // Load fixture should load data and data2
        $fixtureLoader->loadFixtures();

        // To check the fixture, lets get the repository of FixtureLoaderTestData
        $fixturesTest = $this->em->getRepository('AdenaTestBundle:FixturesLoaderTest')->findAll();
        $testCaca = $this->em->getRepository('AdenaMailBundle:Campaign')->findAll();

        $this->assertCount(2, $fixturesTest);

        // ==== Test import list of one fixture
        $fixturesToLoad = array(
            '/Tests/DataFixtures/ORM/FixturesLoaderTestData.php'
        );
        $fixtureLoader->loadFixtures($fixturesToLoad);

        // To check the fixture, lets get the repository of FixtureLoaderTestData

        /** @var array $fixturesTest */
        $fixturesTest = $this->em->getRepository('AdenaTestBundle:FixturesLoaderTest')->findAll();
        $this->assertCount(1, $fixturesTest);
        $this->assertEquals('data1', $fixturesTest[0]->getData1());
        $this->assertEquals('data2', $fixturesTest[0]->getData2());


        // ==== Test import list of two fixture
        $fixturesToLoad = array(
            '/Tests/DataFixtures/ORM/FixturesLoaderTestData.php',
            '/Tests/DataFixtures/ORM/FixturesLoaderTestData2.php',
        );
        $fixtureLoader->loadFixtures($fixturesToLoad);

        // To check the fixture, lets get the repository of FixtureLoaderTestData

        /** @var array $fixturesTest */
        $fixturesTest = $this->em->getRepository('AdenaTestBundle:FixturesLoaderTest')->findAll();
        $this->assertCount(1, $fixturesTest);
        $this->assertEquals('data1', $fixturesTest[0]->getData1());
        $this->assertEquals('data2', $fixturesTest[0]->getData2());
        $this->assertEquals('data2-1', $fixturesTest[1]->getData1());
        $this->assertEquals('data2-2', $fixturesTest[1]->getData2());

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

}