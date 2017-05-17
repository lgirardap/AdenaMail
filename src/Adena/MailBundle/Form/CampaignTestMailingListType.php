<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\MailingList;
use Adena\MailBundle\Repository\MailingListRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignTestMailingListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('testMailingLists', EntityType::class, [
                'class'            => MailingList::class,
                'choice_label'     => 'name',
                'label'            => "Let's test the mailing list",
                'multiple' => true,
                'query_builder' =>  function(MailingListRepository $repository){
                    return $repository->getTestMailingListQueryBuilder();
                },
            ]);
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
        return 'adena_mailbundle_testmailinglist';
    }
}
