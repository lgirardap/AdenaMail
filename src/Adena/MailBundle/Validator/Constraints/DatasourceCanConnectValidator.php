<?php

namespace Adena\MailBundle\Validator\Constraints;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
* @Annotation
*/
class DatasourceCanConnectValidator extends ConstraintValidator
{
    private $mysqlExternal;

    public function __construct(MysqlExternalConnection $mysqlExternal)
    {
        $this->mysqlExternal = $mysqlExternal;
    }

    // This takes a full $datasource object as parameter because the associated Constraint is a Class constraint.
    public function validate($datasource, Constraint $constraint)
    {
        $pingResult = $this->mysqlExternal->ping([
            'servername' => $datasource->getHost(),
            'username' => $datasource->getUsername(),
            'password' => $datasource->getPlainPassword(),
            'database' => $datasource->getDatabaseName(),
            'port' => $datasource->getPort()
        ]);

        // Looks like we could not connect, let's find out why and display it to the user by adding a new error
        if(!$pingResult) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ error_message }}', $this->mysqlExternal->getConnectErrors())
                ->addViolation();
        }
    }
}