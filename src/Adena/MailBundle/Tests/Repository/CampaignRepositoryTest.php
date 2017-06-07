<?php

namespace Adena\MailBundle\Tests\Repository;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CampaignRepositoryTest extends KernelTestCase
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
    }

    public function testGetDatasourcesQuery()
    {
        $query = $this->em
            ->getRepository('AdenaMailBundle:Datasource')
            ->getDatasourcesQuery()
        ;

        $this->assertInstanceOf(Query::class, $query);
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
