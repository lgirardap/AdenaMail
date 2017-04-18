<?php

namespace Adena\MailBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MailingListEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MailingListType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('type')
            ->remove('datasource');
    }
}
