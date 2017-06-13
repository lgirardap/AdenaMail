<?php

namespace Adena\PaginatorBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

//use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PaginatorTest extends TestCase
{
    /** @var  \Symfony\Component\DependencyInjection\Container $container */
    private $container;

    protected function setUp()
    {
//        self::bootKernel();
//        $this->container = static::$kernel->getContainer();
    }

    public function testPaginate()
    {
//        $paginator = $this->container->get('adena_paginator.paginator.paginator');
//        $em = $this->container->get("doctrine.orm.entity_manager");


//        $qb = $em->createQueryBuilder();

        //$schemaTool = new SchemaTool($em);
//        $schemaTool->dropSchema(array());
        //$schemaTool->createSchema(
//            [$em->getClassMetadata('Adena\PaginatorBundle\Tests\Entity\Article')]
//        );
//
//        $qb
//            ->select('a')
//            ->from('Adena\PaginatorBundle\Tests\Entity\Article', 'a')
//        ;

//        $paginator->paginate();

//        $this->assertNotEmpty($this->container->getParameter('adena_paginator.nbPerPage'));
//        $this->assertTrue(is_int($this->container->getParameter('adena_paginator.nbPerPage')));
        $this->getMockAnnotatedConfig();
    }

    /**
     * Creates default mapping driver
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    protected function getMetadataDriverImplementation()
    {
        // TODO == Change with real value
        $reader = new AnnotationReader();
        $reader = new CachedReader($reader, new ArrayCache());

        return new AnnotationDriver($reader);
    }


    /**
     * Get annotation mapping configuration
     *
     * @return \Doctrine\ORM\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockAnnotatedConfig()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $config */
//        $config = $this->createMock('\Doctrine\ORM\Configuration');
        $config = $this->getMockBuilder(Configuration::class)->getMock();

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
            ->method('getCustomHydrationMode')
            ->will($this->returnValue('Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\ORM\Query\AsIsHydrator'))
        ;
        $config
            ->expects($this->any())
            ->method('getDefaultQueryHints')
            ->will($this->returnValue(array()))
        ;

        return $config;
    }
}