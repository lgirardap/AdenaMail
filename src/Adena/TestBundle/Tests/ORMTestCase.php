<?php

namespace Adena\TestBundle\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

abstract class ORMTestCase extends TestCase
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and custom
     * connection
     *
     * @param array        $conn
     * @param array        $paths
     * @param EventManager $evm
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getMockCustomEntityManager(array $conn, $paths = array(), EventManager $evm = null)
    {
        $config = $this->getMockAnnotatedConfig();
        $em = EntityManager::create($conn, $config);

        $schema = array_map(function($class) use ($em) {
            return $em->getClassMetadata($class);
        }, (array)$this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);
        return $this->em = $em;
    }

    /**
     * Paths is the paths where your entity folders should be found
     *
     * @param array                              $paths
     * @param \Doctrine\Common\EventManager|null $evm
     */
    protected function getMockMysqlEntityManager($paths = array(), EventManager $evm = null){
        // TODO -- Add config support
        $conn = array(
            'driver'  => 'pdo_mysql',
            'charset' => 'UTF8',
            'host' => '127.0.0.1',
            'port' => null,
            'dbname' => 'adena_mail_test',
            'user' => 'root',
            'password' => null
        );

        $this->getMockCustomEntityManager( $conn, $paths, $evm);
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param EventManager $evm
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager(EventManager $evm = null)
    {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $paths = array();
        $config = $this->getMockAnnotatedConfig();

        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schema = array_map(function($class) use ($em) {
            return $em->getClassMetadata($class);
        }, (array)$this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema($schema);
        return $this->em = $em;
    }

    /**
     * Get a list of used fixture classes
     *
     * @return array
     */
    abstract protected function getUsedEntityFixtures();

    private function getEventManager()
    {
        $evm = new EventManager;
        return $evm;
    }

    /**
     * Creates default mapping driver
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    protected function getMetadataDriverImplementation()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        // TODO -- Change __dir__ if issue
        return new AnnotationDriver(
            new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache()),
            array(__dir__)
        );
    }


    /**
     * Get annotation mapping configuration
     *
     * @return \Doctrine\ORM\Configuration
     */
    private function getMockAnnotatedConfig()
    {
        $config = $this->getMockBuilder('Doctrine\ORM\Configuration')->getMock();
        $config
            ->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(__DIR__.'/../../temp'))
        ;

        $config
            ->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'))
        ;

        $config
            ->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true))
        ;

        $config
            ->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'))
        ;

        $mappingDriver = $this->getMetadataDriverImplementation();

        $config
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultRepositoryClassName')
            ->will($this->returnValue('Doctrine\\ORM\\EntityRepository'))
        ;

        $config
            ->expects($this->any())
            ->method('getRepositoryFactory')
            ->will($this->returnValue(new DefaultRepositoryFactory()))
        ;

        $config
            ->expects($this->any())
            ->method('getQuoteStrategy')
            ->will($this->returnValue(new DefaultQuoteStrategy()))
        ;

        $config
            ->expects($this->any())
            ->method('getNamingStrategy')
            ->will($this->returnValue(new DefaultNamingStrategy()))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultQueryHints')
            ->will($this->returnValue(array()))
        ;

        return $config;
    }

}