<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\MailBundle\EntityHelper\CampaignTester;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class CampaignCanSendEmailValidator extends ConstraintValidator
{
    private $campaignTester;

    public function __construct(CampaignTester $campaignTester)
    {
        $this->campaignTester = $campaignTester;
    }

    // This takes a full Campaign object as parameter because the associated Constraint is a Class constraint.
    public function validate($campaign, Constraint $constraint)
    {
        try {
            $this->campaignTester->test($campaign);
        }catch(\Exception $e){
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ error_message }}', $e->getMessage())
                ->addViolation();
        }
    }
}