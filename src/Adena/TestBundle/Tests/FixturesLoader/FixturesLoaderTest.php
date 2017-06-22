<?php

namespace Adena\TestBundle\Tests\ActionControl;

use Adena\TestBundle\FixturesLoader\FixturesLoader;
use Adena\TestBundle\Tests\ORMTestHelper;
use Adena\TestBundle\Tests\Repository\FixturesLoaderTestRepository;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class FixturesLoaderTest extends TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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

        $this->em = (new ORMTestHelper())->getMockMysqlEntityManager($this->getEntities());

        // We have to mock the project root dir and the Bundles Meta data since we can't access the kernel
        $projectRootDir = dirname(__FILE__, 5);

        $bundlesMetaData = array(
            'AdenaTestBundle' => array(
                'parent' => null,
                'path' => $projectRootDir,
                'namespace' => "Adena\TestBundle"
            )
        );

        $this->fixtureTestRepository = $this->em->getRepository('Adena\TestBundle\Tests\Entity\FixturesLoaderTest');
        $this->fixtureLoader = new FixturesLoader($this->em, $projectRootDir, $bundlesMetaData);
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

    public function testDeleteAllFixtures(){

        // ==== Test import one fixture
        $fixtureToLoad = array('src/Adena/TestBundle/Tests/DataFixtures/');
        $this->fixtureLoader->loadFixtures( $fixtureToLoad );

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

        $this->em->close();
        $this->em = null;

        $this->fixtureTestRepository = null;
        $this->fixtureLoader = null;
    }

    /**
     * Get a list of used entity fixture classes
     *
     * @return array
     */
    protected function getEntities()
    {
        return [
            'Adena\TestBundle\Tests\Entity\FixturesLoaderTest'
        ];
    }
}