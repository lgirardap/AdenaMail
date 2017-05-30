<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\Email;
use Adena\MailBundle\Entity\MailingList;
use Adena\MailBundle\Repository\MailingListRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('mailingLists', EntityType::class, [
                'class'            => MailingList::class,
                'choice_label'     => 'name',
                'multiple' => true,
                'query_builder' =>  function(MailingListRepository $repository){
                    return $repository->getRegularMailingListQueryBuilder();
                },
            ])
            ->add('email', EntityType::class, [
                'class' => Email::class,
                'choice_label' => 'name'
            ])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Adena\MailBundle\Entity\Campaign'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adena_mailbundle_campaign';
    }


}
