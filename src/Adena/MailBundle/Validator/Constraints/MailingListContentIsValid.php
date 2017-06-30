<?php

namespace Adena\MailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class MailingListContentIsValid extends Constraint
{
    public $message = '';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    // Makes this a "class level" constraint. Not designed to be used on properties, it would make no sense.
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}