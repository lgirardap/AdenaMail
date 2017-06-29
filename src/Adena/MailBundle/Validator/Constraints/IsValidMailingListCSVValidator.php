<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\CoreBundle\Tools\CSVParser;
use Adena\MailBundle\Entity\MailingList;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class IsValidMailingListCSVValidator extends ConstraintValidator
{
    /** @var CSVParser */
    private $csvParser;

    function __construct(CSVParser $csvParser)
    {
        $this->csvParser = $csvParser;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var MailingList $mailingList */
        $mailingList = $this->context->getRoot()->getData();

        // Disable this check if the mailing is being validated through another form (using @Assert\Valid on the
        // relationship)
        if(!($mailingList instanceof MailingList)){
            return;
        }
        
        // Only for "list" type MailingLists
        if(MailingList::TYPE_LIST !== $mailingList->getType()){
            return;
        }

        try {
            // First parse the string using our service.
            // This will throw an exception on malformed strings or invalid number of columns (mismatch header / content)
            $array_csv = $this->csvParser->parse($value);

            // Make sure we have at least an "email" column.
            if(!isset($array_csv[0]['email'])){
                throw new \Exception('Missing email column in CSV');
            }
        }catch(\Exception $e) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}