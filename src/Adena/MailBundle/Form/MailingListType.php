<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\MailingList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('content', TextareaType::class)
            ->add('type', ChoiceType::class, [
                'choices' => MailingList::TYPES,
            ])
            ->add('datasource', EntityType::class, [
                'class' => 'Adena\MailBundle\Entity\Datasource',
                'choice_label' => 'name'
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Adena\MailBundle\Entity\MailingList'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adena_mailbundle_mailinglist';
    }


}
