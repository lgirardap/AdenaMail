<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\MailingList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailingListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var MailingList $mailingList */
            $mailingList = $event->getData();

            if(null === $mailingList){
                return;
            }

            if("query" === $mailingList->getType()){
                $event->getForm()->add('datasource', EntityType::class, [
                    'class' => 'Adena\MailBundle\Entity\Datasource',
                    'choice_label' => 'name'
                ]);
                $help_block = 'Valid SQL query.';
            }else{
                $help_block = 'List of email addresses separated by a coma (,).';
            }

            $event->getForm()->add('content', TextareaType::class, [
                'help_block' => $help_block
            ]);
        });
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
