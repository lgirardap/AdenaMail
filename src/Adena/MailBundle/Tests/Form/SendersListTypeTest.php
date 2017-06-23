<?php

namespace Adena\MailBundle\Tests\Form;

use Adena\MailBundle\Entity\SendersList;
use Adena\MailBundle\Form\SendersListType;

use Adena\TestBundle\Tests\ORMTestHelper;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class TestedTypeTest extends TypeTestCase
{
    private $entityManager;

    protected function setUp()
    {
        // mock any dependencies
        $this->entityManager = (new ORMTestHelper())->getMockMysqlEntityManager();
        parent::setUp();
    }

    protected function getExtensions()
    {
        $name = 'default';

        $registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($this->entityManager));

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->entityManager));


        return array_merge(parent::getExtensions(), array(
            new DoctrineOrmExtension($registry),
        ));
    }

    public function testSubmitValidData()
    {
        $formData = array(
            'name'       => 'testname',
            'fromEmail'  => 'email',
            'fromName'   => 'name',
            'senders'    => 'foch',
        );

        $object = new SendersList();
        $form = $this->factory->create(SendersListType::class, $object);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        $this->assertCount(count($formData), $children);

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}