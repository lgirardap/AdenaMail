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

class MailingListDataFetcher
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

    public function getFirstRow(MailingList $mailingList)
    {
        return array_pop($this->fetch($mailingList));
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
            'password'   => $datasource->getPlainPassword(),
            'database'   => $datasource->getDatabaseName(),
            'port'       => $datasource->getPort()
        ]);

        $this->mysqlExternal->close();

        return $results;
//        return array_column($results, 'email');
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