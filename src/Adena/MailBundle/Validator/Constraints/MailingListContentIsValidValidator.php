<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Adena\CoreBundle\Tools\CSVParser;
use Adena\MailBundle\Entity\MailingList;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class MailingListContentIsValidValidator extends ConstraintValidator
{
    private $csvParser;
    private $mysqlExternal;

    public function __construct(CSVParser $csvParser, MysqlExternalConnection $mysqlExternal)
    {
        $this->csvParser = $csvParser;
        $this->mysqlExternal = $mysqlExternal;
    }

    /**
     * @param MailingList $mailingList a full MailingList object as parameter because the associated Constraint is a Class constraint.
     * @param Constraint $constraint
     */
    public function validate($mailingList, Constraint $constraint)
    {
        if(MailingList::TYPE_QUERY === $mailingList->getType()){
            $datasource = $mailingList->getDatasource();
            try {
                $results = $this->mysqlExternal->executeQuery($mailingList->getContent(), [
                    'servername' => $datasource->getHost(),
                    'username'   => $datasource->getUsername(),
                    'password'   => $datasource->getPlainPassword(),
                    'database'   => $datasource->getDatabaseName(),
                    'port'       => $datasource->getPort()
                ]);

                // Make sure we have at least an "email" column.
                if(!isset($results[0]['email'])){
                    throw new \Exception('Missing email column.');
                }
            }catch(\Exception $e){
                $this->context->buildViolation('The content must be a valid SQL query, with at least an "email" column.')
                    ->atPath('content')
                    ->addViolation();
            }finally{
                $this->mysqlExternal->close();
            }
        }else{
            try {
                // First parse the string using our service.
                // This will throw an exception on malformed strings or invalid number of columns (mismatch header / content)
                $array_csv = $this->csvParser->parse($mailingList->getContent());

                // Make sure we have at least an "email" column.
                if(!isset($array_csv[0]['email'])){
                    throw new \Exception('Missing email column in CSV');
                }
            }catch(\Exception $e) {
                $this->context->buildViolation('The content must be a valid CSV, with at least an "email" column.')
                    ->atPath('content')
                    ->addViolation();
            }
        }
    }
}