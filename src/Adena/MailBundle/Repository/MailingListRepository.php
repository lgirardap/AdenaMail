<?php

namespace Adena\MailBundle\Repository;

use Adena\MailBundle\Entity\Campaign;

/**
 * MailingListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MailingListRepository extends \Doctrine\ORM\EntityRepository
{

    public function getTestMailingListQueryBuilder()
    {
        return $this
            ->createQueryBuilder('m')
            ->where('m.isTest = 1')
            ;
    }

    public function getRegularMailingListQueryBuilder()
    {
        return $this
            ->createQueryBuilder('m')
            ->where('m.isTest = 0')
            ;
    }

    public function getMailingListsQuery()
    {
        $query = $this
            ->createQueryBuilder('m')
            ->orderBy('m.name', 'desc')
            ->getQuery();

        return $query;
    }
}
