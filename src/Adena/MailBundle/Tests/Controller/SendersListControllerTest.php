<?php

namespace Adena\MailBundle\Tests\Controller;

use Adena\TestBundle\FixturesLoader\FixturesLoader;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SendersListControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;
    /** @var FixturesLoader */
    private $fixturesLoader;

    public function setUp(){
        $this->client = static::createClient();
        $this->fixturesLoader = $this->client->getContainer()->get('adena_test.fixtures_loader');
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/senders-list');

        // Make sure we have a table
        $this->assertEquals(1, $crawler->filter('table')->count());

        // Since we have no data, the table should be empty (no tr in the tbody)
        $this->assertEquals(0, $crawler->filter('table tbody tr')->count());

        // We should not be seeing the pagination
        $this->assertEquals(0, $crawler->filter('.pagination')->count());

        // Insert data
        $this->fixturesLoader->loadFixturesFromPaths([
            '/src/Adena/MailBundle/Tests/DataFixtures/ORM/LoadSendersList.php'
        ]);

        $crawler = $this->client->request('GET', '/senders-list');

        // We show the first page, and we have 10 per page, so we should have 10 rows in the table
        $this->assertEquals(10, $crawler->filter('table tbody tr')->count());

        // The pagination should be here
        $this->assertGreaterThan(0, $crawler->filter('.pagination')->count());

        // Page 1 selected
        $this->assertEquals('1', $crawler->filter('.pagination li.active span')->text());

        // Clicking on page 2 should take us to page 2
        $link = $crawler->filter('.pagination')->selectLink('2')->link();
        $crawler = $this->client->click($link);

        // We should have 3 elements
        $this->assertEquals(3, $crawler->filter('table tbody tr')->count());

        // Page 2 selected
        $this->assertEquals('2', $crawler->filter('.pagination li.active span')->text());
    }

    public function tearDown(){
        $this->fixturesLoader->deleteAllFixtures();
    }
}