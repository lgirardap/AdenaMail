<?php

namespace Adena\MailBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SendersListControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/senders-list');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html')->count()
        );
    }
}