<?php

namespace Adena\MailBundle\Tests\Controller;

use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Repository\CampaignRepository;
use Adena\PaginatorBundle\Paginator\Paginator;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\DateTime;

class CampaignControllerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $manager;

    public function setUp()
    {
        $this->manager  = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }


//    public function testList()
//    {
//        $campaign1 = $this->createMock(Campaign::class);
//        $campaign1->expects($this->once())
//            ->method('getStatus')
//            ->will($this->returnValue(Campaign::STATUS_ENDED));
//        $campaign1->expects($this->once())
//            ->method('getName')
//            ->will($this->returnValue('caca'));
//        $campaign1->expects($this->once())
//            ->method('getCreatedAt')
//            ->will($this->returnValue(new DateTime()));
//
//        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $campaignRepository->expects($this->once())
//            ->method('createQueryBuilder')
//            ->with('n')
//            ->will($this->returnValue($queryBuilder));
//
//        // We use QueryBuilder as Fluent Interface
//        // several times, so we need to make it sequence
//        $queryBuilder->expects($this->at(0))
//            ->method('setFirstResult')
//            ->with(0)
//            ->will($this->returnValue($queryBuilder));
//        $queryBuilder->expects($this->at(1))
//            ->method('setMaxResults')
//            ->with(2)
//            ->will($this->returnValue($queryBuilder));
//        $queryBuilder->expects($this->at(2))
//            ->method('orderBy')
//            ->with('n.id', 'DESC')
//            ->will($this->returnValue($queryBuilder));
//
//        $query = $this->getMockBuilder(AbstractQuery::class)
//            ->setMethods(array('getResult'))
//            ->disableOriginalConstructor()
//            ->getMockForAbstractClass();
//        $query->expects($this->once())
//            ->method('getResult')
//            ->will($this->returnValue([
//                $campaign1
//            ]));
//
//        $campaignRepository = $this
//            ->getMockBuilder(CampaignRepository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $campaignRepository->expects($this->once())
//            ->method('getActiveCampaignsQuery')
//            ->will($this->returnValue($query));
//
//        // create entity manager mock
//        $entityManager = $this
//            ->getMockBuilder(EntityManagerInterface::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//        $entityManager->expects($this->once())
//            ->method('getRepository')
//            ->will($this->returnValue($campaignRepository));
//
//        // next you need inject your mocked em into client's service container
//        $client = static::createClient();
//        $client->getContainer()->set('doctrine.orm.default_entity_manager', $entityManager);
//
//        $crawler = $client->request('GET', '/campaign');
//        $this->assertContains('adena_mail_datasource_list', $client->getResponse()->getContent());
//    }


    public function testList()
    {
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getStatus')
            ->willReturn(Campaign::STATUS_ENDED);
        

        $client = static::createClient();
        $this->assertContains('adena_mail_datasource_list', $client->getResponse()->getContent());
    }



    private function createCampaign(){
        // mock Une campagne

    }
}
