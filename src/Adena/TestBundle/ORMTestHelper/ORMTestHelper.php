<?php

namespace Adena\TestBundle\ORMTestHelper;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ORMTestHelper extends KernelTestCase
{
    /**
     * EntityManager mock object together with
     * annotation mapping driver and custom
     * connection
     *
     * @param array        $conn
     * @param EventManager $evm
     *
     * @return \Doctrine\ORM\EntityManager
     * @internal param array $paths
     */
    private function getMockCustomEntityManager(array $conn, EventManager $evm = null)
    {
        $config = $this->getMockAnnotatedConfig();
        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());



        return $em;
    }

    /**
     * @param EntityManager      $em
     * @param array $entities
     */
    public function createSchema($em, $entities = array() ){
        $schema = [];
        $paths = [];

        foreach($entities as $entity){
            $reflection = new \ReflectionClass($entity);
            $paths[] = pathinfo($reflection->getFileName(), PATHINFO_DIRNAME);
            $schema[] = $em->getClassMetadata($entity);
        }
        $em->getConfiguration()->getMetadataDriverImpl()->addPaths(array_unique($paths));

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);
    }


    /**
     * Paths is the paths where your entity folders should be found
     *
     * @param array                              $entities
     * @param \Doctrine\Common\EventManager|null $evm
     *
     * @return \Doctrine\ORM\EntityManager
     * @internal param array $paths
     */
    public function getMockMysqlEntityManager($entities = array(), EventManager $evm = null){
        self::bootKernel();
        $params = static::$kernel->getContainer()->getParameter('adena_test.mysql_connection');
        $conn = array(
            'driver'  => 'pdo_mysql',
            'charset' => 'UTF8',
            'host' => $params['host'],
            'port' => $params['port'],
            'dbname' => $params['dbname'],
            'user' => $params['user'],
            'password' => $params['password']
        );

        $em = $this->getMockCustomEntityManager( $conn, $evm );

        if(!empty($entities)){
            $this->createSchema($em, $entities);
        }

        return $em;
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param array        $entities
     * @param EventManager $evm
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getMockSqliteEntityManager($entities = array(), EventManager $evm = null)
    {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );


        $em = $this->getMockCustomEntityManager( $conn, $evm );

        if(!empty($entities)){
            $this->createSchema($em, $entities);
        }

        return $em;
    }


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
            null
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