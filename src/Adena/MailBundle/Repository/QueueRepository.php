<?php

namespace Adena\MailBundle\Repository;

use Adena\MailBundle\Entity\Campaign;

/**
 * QueueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QueueRepository extends \Doctrine\ORM\EntityRepository
{
    public function countByCampaign($campaign){
        $qb = $this->createQueryBuilder('q');
        $qb->select('count(q.id)')
            ->where('q.campaign = :campaign')
            ->setParameter('campaign', $campaign);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getAsArrayByCampaign($campaign){
        return $this->createQueryBuilder('q')
            ->where('q.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getArrayResult();
    }

    public function removeAllForCampaign($campaign){
        return $this->createQueryBuilder('q')
            ->delete()
            ->where('q.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getResult();
    }

    public function removeById($id){
        return $this->createQueryBuilder('q')
            ->delete()
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * Handles batch inserting by doing a raw PDO query instead of using the Doctrine to prevent memory limit errors.
     * Note: This will work only for MySQL because we use "INSERT INTO table (columns) VALUES (),(),()"
     *
     * @param                                   $emails
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     *
     * @throws \Exception
     * @see http://stackoverflow.com/a/9088630
     */
    public function nativeBulkInsertForCampaign($emails, Campaign $campaign){
        $conn = $this->getEntityManager()->getConnection();

        // The SQL query
        $sql = 'INSERT INTO queue (email, campaign_id) VALUES ';

        // Create the placeholders that will be used to "prepare" the query and make it SQL-Injection safe
        $placeholders = [];
        // Create the values as a flat array of all the values: ['email1', 'campaign_id', 'email2', campaign_id]...
        $values = [];
        foreach($emails as $email){
            // We insert two colums so we need two placeholders per row inserted.
            $placeholders[] = "(?,?)";

            // Add the values to the flat array
            $values[] = $email;
            $values[] = $campaign->getId();
        }
        // Add the placeholders to the SQL query
        $sql .= implode(',', $placeholders);

        // Prepare the statement
        $statement = $conn->prepare($sql);

        // Execute it with our flat array of values.
        if(!$statement->execute($values)){
            throw new \Exception($statement->errorCode());
        }
    }
}
