<?php

namespace Adena\MailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class IsValidMailingListQuery extends Constraint
{
    public $message = 'This must be a valid SQL query, with at least an "email" column.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}