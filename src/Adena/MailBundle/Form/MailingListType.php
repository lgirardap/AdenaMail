<?php

namespace Adena\MailBundle\Form;

use Adena\MailBundle\Entity\MailingList;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataMapper\CheckboxListMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
            ->add('isTest', CheckboxType::class);

        // Our callback to decide whether or not to add the datasource field.
        $datasourceModifier = function(FormInterface $form, $type){
            if(MailingList::TYPE_QUERY === $type) {
                $form->add('datasource', EntityType::class, [
                    'class' => 'Adena\MailBundle\Entity\Datasource',
                    'choice_label' => 'name'
                ]);

//                $form->add('datasource', DatasourceType::class);
            }
        };

        $contentModifier = function(FormInterface $form, $type){
            // Generate the correct helpBlock message
            if(MailingList::TYPE_QUERY === $type) {
                $helpBlock = 'Valid SQL query.';
            }else{
                $helpBlock = 'List of email addresses separated by a coma (,).';
            }

            // Show a message to remind the user what type of content we are waiting for.
            $form->add('content', TextareaType::class, [
                'help_block' => $helpBlock
            ]);
        };

        // When the form is initialized, we have to modify it depending on $mailingList->getType();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($datasourceModifier, $contentModifier) {
            /** @var MailingList $mailingList */
            $mailingList = $event->getData();

            if(null === $mailingList){
                return;
            }

            // Add the datasource field if needed.
            $datasourceModifier($event->getForm(), $mailingList->getType());
            $contentModifier($event->getForm(), $mailingList->getType());
        });

//        // When the "type" sub-form is submitted, we have to check its value and if needed add the datasource field
//        // When using AJAX validation, the only way we can know the type is by checking the data in the Hidden field, so we
//        // can't rely on the PRE_SET_DATA event earlier.
//        $builder->get('type')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($datasourceModifier, $contentModifier) {
//            // We want to modify the PARENT form, so we have to use $event->getForm()->getParent();
//            // $event->getForm()->getData() is the value of the field, in this case a string.
//            $datasourceModifier($event->getForm()->getParent(), $event->getForm()->getData());
//            $contentModifier($event->getForm()->getParent(), $event->getForm()->getData());
//        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Adena\MailBundle\Entity\MailingList',
            'allow_extra_fields' => true
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
