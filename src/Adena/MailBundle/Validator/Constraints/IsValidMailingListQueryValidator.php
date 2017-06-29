<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Adena\MailBundle\Entity\Datasource;
use Adena\MailBundle\Entity\MailingList;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class IsValidMailingListQueryValidator extends ConstraintValidator
{
    private $mysqlExternal;

    function __construct(MysqlExternalConnection $mysqlExternal)
    {

        $this->mysqlExternal = $mysqlExternal;
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

        /** @var Datasource $datasource */
        $datasource = $mailingList->getDatasource();

        // Only for "query" type MailingLists
        if(MailingList::TYPE_QUERY !== $mailingList->getType()){
            return;
        }

        if(!$datasource){
            return;
        }

        try {
            $results = $this->mysqlExternal->executeQuery($value, [
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
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }finally{
            $this->mysqlExternal->close();
        }
    }
}