<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Adena\MailBundle\Entity\MailingList;

class MailingListTester
{

    private $mysqlExternal;
    private $errors = "";

    public function __construct( MysqlExternalConnection $mysqlExternal)
    {
        $this->mysqlExternal = $mysqlExternal;
    }

    public function test(MailingList $mailingList){
        $this->errors = "";

        // We only test Query type.
        if ($mailingList->getType() != MailingList::TYPE_QUERY) {
            return true;
        }

        try {
            $datasource = $mailingList->getDatasource();
            $this->mysqlExternal->executeQuery($mailingList->getContent(), [
                'servername' => $datasource->getHost(),
                'username'   => $datasource->getUsername(),
                'password'   => $datasource->getPlainPassword(),
                'database'   => $datasource->getDatabaseName(),
                'port'       => $datasource->getPort()
            ]);
            return true;
        }catch(\Exception $e){
            $this->errors = $e->getMessage();
            return false;
        }finally{
            $this->mysqlExternal->close();
        }
    }

    /**
     * @return string
     */
    public function getErrors(): string
    {
        return $this->errors;
    }
}