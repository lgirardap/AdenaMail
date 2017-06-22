<?php

namespace Adena\MailBundle\Tests\Form;

use Adena\MailBundle\Entity\SendersList;
use Adena\MailBundle\Form\SendersListType;
use Symfony\Component\Form\Test\TypeTestCase;

class TestedTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'test'  => 'test',
            'test2' => 'test2',
        );

        $form = $this->factory->create(SendersListType::class);

        $object = SendersList::fromArray($formData);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}