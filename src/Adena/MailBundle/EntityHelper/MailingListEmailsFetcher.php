<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/8/2017
 * Time: 12:48 PM
 */

namespace Adena\MailBundle\EntityHelper;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Adena\MailBundle\Entity\MailingList;

class MailingListEmailsFetcher
{

    private $mysqlExternal;

    public function __construct( MysqlExternalConnection $mysqlExternal)
    {
        $this->mysqlExternal = $mysqlExternal;
    }

    public function fetch(MailingList $mailingList){
        if ($mailingList->getType() == MailingList::TYPE_QUERY) {
            return $this->addFromDatasource($mailingList);
        }

        if ($mailingList->getType() == MailingList::TYPE_LIST) {
            return $this->addFromList($mailingList);
        }

        return [];
    }

    /**
     * @param $mailingList

     * @return array
     */
    private function addFromDatasource(MailingList $mailingList): array
    {
        $datasource = $mailingList->getDatasource();
        $results    = $this->mysqlExternal->executeQuery($mailingList->getContent(), [
            'servername' => $datasource->getHost(),
            'username'   => $datasource->getUsername(),
            'password'   => $datasource->getPassword(),
            'database'   => $datasource->getDatabaseName(),
            'port'       => $datasource->getPort()
        ]);

        $this->mysqlExternal->close();

        return array_column($results, 'email');
    }

    /**
     * @param $mailingList

     * @return array
     */
    private function addFromList(MailingList $mailingList): array
    {
        $list = explode(',', $mailingList->getContent());
        return array_map('trim', $list);
    }
}