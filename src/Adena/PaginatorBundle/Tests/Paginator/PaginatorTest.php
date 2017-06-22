<?php

namespace Adena\PaginatorBundle\Tests\Paginator;

use Adena\PaginatorBundle\Paginator\Paginator;
use Adena\PaginatorBundle\Tests\Entity\ArticleTest;
use Adena\TestBundle\Tests\ORMTestHelper;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class PaginatorTest extends TestCase
{
    private $query;
    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;


    public function setUp()
    {
        parent::setUp();


        $this->em = (new ORMTestHelper())->getMockMysqlEntityManager($this->getEntities());
        $this->query = $this->em->createQueryBuilder()
            ->select('a')
            ->from(ArticleTest::class, 'a')
            ->getQuery();
    }

    public function testPageNumberType()
    {
        $paginator = new Paginator($this->em, 'not_an_integer');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Number per page must be an integer');
        $paginator->paginate($this->query, 1);
    }

    public function testValidPageNumber()
    {
        $paginator = new Paginator($this->em, 3);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be one or more.');
        $paginator->paginate($this->query, -1);
    }

    /**
     * @depends testPageNumberType
     * @depends testValidPageNumber
     */
    public function testPaginate()
    {
        $paginator = new Paginator($this->em, 3);

        // No data, we should not crash, but we should also have no result
        $this->assertCount(0, $paginator->paginate($this->query, 1));

        // Insert 10 elements
        for($i = 0; $i < 10; $i++) {
            $data = new ArticleTest();
            $data->setName($i);
            $this->em->persist($data);
        }
        $this->em->flush();

        $page1 = $paginator->paginate($this->query, 1);
        // We should have 10 elements, 4 pages, be on page 1 and 3 per page.
        $this->assertCount(10, $page1);
        $this->assertAttributeEquals(1, 'page', $page1);
        $this->assertAttributeEquals(4, 'nbPages', $page1);
        $this->assertAttributeEquals(3, 'nbPerPage', $page1);

        // Check that we have elements 0 to 2 (3 per page)
        $this->assertCount(3, $page1->getIterator());
        $this->assertAttributeEquals(0, 'name', $page1->getIterator()->offsetGet(0));
        $this->assertAttributeEquals(1, 'name', $page1->getIterator()->offsetGet(1));
        $this->assertAttributeEquals(2, 'name', $page1->getIterator()->offsetGet(2));

        // Switch to last page and check that we have only 1 element, and that this element is 9
        $page4 = $paginator->paginate($this->query, 4);
        $this->assertCount(1, $page4->getIterator());
        $this->assertAttributeEquals(4, 'page', $page4);
        $this->assertAttributeEquals(9, 'name', $page4->getIterator()->offsetGet(0));

        // Let's request a page higher than the number of page
        // and check that we have the correct exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Page 5 does not exist.");
        $paginator->paginate($this->query, 5);
    }

    /**
     * Get a list of used fixture classes
     *
     * @return array
     */
    protected function getEntities()
    {
        return [ArticleTest::class];
    }
}