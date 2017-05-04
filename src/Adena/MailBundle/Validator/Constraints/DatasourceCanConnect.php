<?php

namespace Adena\MailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class DatasourceCanConnect extends Constraint
{
    public $message = 'Failed to connect with the error: {{ error_message }}';

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