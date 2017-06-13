<?php

namespace Adena\TestBundle\Tests\ActionControl;

use Adena\TestBundle\FixturesLoader\FixturesLoader;
use Adena\TestBundle\Tests\Repository\FixturesLoaderTestRepository;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FixturesLoaderTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

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
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // We create the table we will use to do the fixtures tests
        $this->fixtureTestRepository = $this->em->getRepository('AdenaTestBundle:FixturesLoaderTest');
        $this->fixtureTestRepository->createTable();

        $this->fixtureLoader = static::$kernel->getContainer()->get('adena_test.fixtures_loader');
    }


    public function testIsFixtureLoader()
    {
        $this->assertInstanceOf(FixturesLoader::class, $this->fixtureLoader);
    }


    public function testLoadFixturesFromPaths()
    {
        // ==== Test import list of two fixture
        $fixturesToLoad = array(
            'src/Adena/TestBundle/Tests/DataFixtures/FixturesLoaderTestData.php',
            'src/Adena/TestBundle/Tests/DataFixtures/FixturesLoaderTestData2.php',
        );
        $this->fixtureLoader->loadFixturesFromPaths($fixturesToLoad);

        /** @var array $fixturesTest */
        $fixturesTest = $this->fixtureTestRepository->findAll();
        $this->assertCount(2, $fixturesTest);
        $this->assertEquals('data1', $fixturesTest[0]->getData1());
        $this->assertEquals('data2', $fixturesTest[0]->getData2());
        $this->assertEquals('data2-1', $fixturesTest[1]->getData1());
        $this->assertEquals('data2-2', $fixturesTest[1]->getData2());

        // ==== Test import list of one fixture
        $fixturesToLoad = array(
            'src/Adena/TestBundle/Tests/DataFixtures/FixturesLoaderTestData2.php',
        );
        $this->fixtureLoader->loadFixturesFromPaths($fixturesToLoad);

        /** @var array $fixturesTest */
        $fixturesTest = $this->fixtureTestRepository->findAll();
        $this->assertCount(1, $fixturesTest);
        $this->assertEquals('data2-1', $fixturesTest[0]->getData1());
        $this->assertEquals('data2-2', $fixturesTest[0]->getData2());

    }

    public function testLoadFixtures()
    {
        // ==== Test import one fixture
        $fixtureToLoad = array('src/Adena/TestBundle/Tests/DataFixtures/');
        $this->fixtureLoader->loadFixtures( $fixtureToLoad );

        /** @var array $fixturesTest */
        $fixturesTest = $this->fixtureTestRepository->findAll();
        $this->assertCount(2, $fixturesTest);
        $this->assertEquals('data1', $fixturesTest[0]->getData1());
        $this->assertEquals('data2', $fixturesTest[0]->getData2());
    }

    public function testLoadAllFixtures()
    {
        // ==== Test import of all fixtures ( from bundle fixtures folders )
        // We should get a InvalidArgumentException here since at least one of the bundle default fixture folder will be empty,
        // enough to fire the exception
        $this->expectException(InvalidArgumentException::class);
        $this->fixtureLoader->loadAllFixtures();
    }

    public function testDeleteAllFixtures(){

        $this->fixtureLoader->deleteAllFixtures();
        $fixturesTest = $this->fixtureTestRepository->findAll();
        $this->assertCount(0, $fixturesTest);

    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        // We clean up the fixture test table
        $this->fixtureTestRepository->dropTable();

        $this->em->close();
        $this->em = null;

        $this->fixtureTestRepository = null;
        $this->fixtureLoader = null;
    }

}