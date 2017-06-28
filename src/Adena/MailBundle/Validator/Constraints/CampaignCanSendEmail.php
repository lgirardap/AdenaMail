<?php

namespace Adena\MailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class CampaignCanSendEmail extends Constraint
{
    public $message = 'The {{ error_message }} Please update the MailingList to add it, use a different MailingList or update the Email template.';

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