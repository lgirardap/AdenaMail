<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\Email;
use Adena\MailBundle\Entity\MailingList;
use Adena\MailBundle\Entity\SendersList;
use Adena\MailBundle\Repository\EmailRepository;
use Adena\MailBundle\Repository\MailingListRepository;
use Adena\MailBundle\Repository\SendersListRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

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
//            ->add('email', EntityType::class, [
//                'class' => Email::class,
//                'choice_label' => 'name',
//                'query_builder' =>  function(EmailRepository $repository){
//                    return $repository->getEmailsQueryBuilder();
//                },
//            ])
            ->add('email', Select2EntityType::class, [
                'multiple' => false,
                'remote_route' => 'adena_mail_ajax_campaign_get_email',
                'class' => Email::class,
                'text_property' => 'name',
                'allow_clear' => true,
                'cache' => true,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => 'en',
                'placeholder' => 'Select an email'
            ])
            ->add('fromName', TextType::class)
            ->add('fromEmail', TextType::class)
            ->add('sendersList', EntityType::class, [
                'class' => SendersList::class,
                'choice_label' => 'name',
                'query_builder' =>  function(SendersListRepository $repository){
                    return $repository->getSendersListsQueryBuilder();
                },
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
