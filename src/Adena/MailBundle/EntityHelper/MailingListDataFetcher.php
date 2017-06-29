<?php
/**
 * Created by PhpStorm.
 * User: Girard Lionel
 * Date: 5/8/2017
 * Time: 12:48 PM
 */

namespace Adena\MailBundle\EntityHelper;

use Adena\CoreBundle\ExternalConnection\MysqlExternalConnection;
use Adena\CoreBundle\Tools\CSVParser;
use Adena\MailBundle\Entity\MailingList;

class MailingListDataFetcher
{

    private $mysqlExternal;
    private $csvParser;

    public function __construct( MysqlExternalConnection $mysqlExternal, CSVParser $csvParser)
    {
        $this->mysqlExternal = $mysqlExternal;
        $this->csvParser = $csvParser;
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
        $rows = $this->fetch($mailingList);
        return reset($rows);
    }

    /**
     * @param $mailingList

     * @return array
     */
    private function addFromDatasource(MailingList $mailingList)
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
    }

    /**
     * @param $mailingList

     * @return array
     */
    private function addFromList(MailingList $mailingList)
    {
        return $this->csvParser->parse($mailingList->getContent());
    }
}