<?php

namespace Adena\MailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class IsValidCSV extends Constraint
{
    public $message = 'This must be a valid CSV, with at least an "email" column.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}