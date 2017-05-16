<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\MailingList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TestMailingListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mailingListTest', EntityType::class, [
                'class'            => MailingList::class,
                'choice_label'     => 'name',
                'label'            => "Let's test the mailing list"
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adena_mailbundle_testmailinglist';
    }


}
