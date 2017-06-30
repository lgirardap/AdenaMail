<?php

namespace Adena\MailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AceeditorType extends AbstractType
{
    public function setDefaultOptions(OptionsResolver $resolver)
    {

    }

    public function getParent() // On utilise l'héritage de formulaire
    {
        return TextareaType::class;
    }

    public function getName()
    {
        return 'aceeditor';
    }
}