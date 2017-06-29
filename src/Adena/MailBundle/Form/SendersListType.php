<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\Sender;
use Adena\MailBundle\Repository\SenderRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendersListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',               TextType::class)
            ->add('fromEmail',          EmailType::class)
            ->add('fromName',           TextType::class)
            ->add('senders',            EntityType::class, [
                'class' => Sender::class,
                'multiple' => true,
                'choice_label' => 'name',
                'query_builder' =>  function(SenderRepository $repository){
                    return $repository->getSendersQueryBuilder();
                },
            ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Adena\MailBundle\Entity\SendersList'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adena_mailbundle_senders_list';
    }

}
