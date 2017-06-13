<?php

namespace Adena\TestBundle\Tests\Repository;

use Doctrine\ORM\Tools\SchemaTool;

/**
 * FixturesLoaderTestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FixturesLoaderTestRepository extends \Doctrine\ORM\EntityRepository
{
    public function coolMethode(){
        $qb = $this->createQueryBuilder('a');

        return $qb;
    }

    public function createTable()
    {
        $schemaTool = new SchemaTool($this->getEntityManager());
        $schemaTool->createSchema(
            [$this->getClassMetadata()]
        );
    }

    public function dropTable()
    {
        $schemaTool = new SchemaTool($this->getEntityManager());
        $schemaTool->dropSchema(
            [$this->getClassMetadata()]
        );
    }
}
