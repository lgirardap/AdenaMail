<?php

namespace Adena\MailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatetimepickerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolver $resolver)
    {

    }

    public function getParent() // On utilise l'héritage de formulaire
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'datetimepicker';
    }
}