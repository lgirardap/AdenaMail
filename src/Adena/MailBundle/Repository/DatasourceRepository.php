<?php

namespace Adena\MailBundle\Repository;

/**
 * DatasourceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DatasourceRepository extends \Doctrine\ORM\EntityRepository
{
    public function getDatasourcesQuery()
    {
        $query = $this
            ->getDatasourcesQueryBuilder()
            ->getQuery();

        return $query;
    }

    public function getDatasourcesQueryBuilder()
    {
        $queryBuilder = $this
            ->createQueryBuilder('d')
            ->orderBy('d.name', 'asc');

        return $queryBuilder;
    }
}
