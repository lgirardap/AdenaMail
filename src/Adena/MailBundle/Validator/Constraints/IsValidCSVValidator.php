<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\CoreBundle\Tools\CSVParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class IsValidCSVValidator extends ConstraintValidator
{
    /** @var CSVParser */
    private $csvParser;

    function __construct(CSVParser $csvParser)
    {
        $this->csvParser = $csvParser;
    }

    // This takes a full $datasource object as parameter because the associated Constraint is a Class constraint.
    public function validate($value, Constraint $constraint)
    {
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