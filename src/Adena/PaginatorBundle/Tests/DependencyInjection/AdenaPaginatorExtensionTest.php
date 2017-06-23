<?php

namespace Adena\PaginatorBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdenaPaginatorExtensionTest extends KernelTestCase
{
    /** @var  \Symfony\Component\DependencyInjection\Container $container */
    private $container;

    protected function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    public function testNbPerPageConfig()
    {
        $this->assertNotEmpty($this->container->getParameter('adena_paginator.nbPerPage'));
        $this->assertTrue(is_int($this->container->getParameter('adena_paginator.nbPerPage')));
    }
}