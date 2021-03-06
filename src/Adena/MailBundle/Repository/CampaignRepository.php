<?php

namespace Adena\MailBundle\Repository;

use Adena\MailBundle\Entity\Campaign;

/**
 * CampaignRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CampaignRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $id
     *
     * @return \Adena\MailBundle\Entity\Campaign
     */
    public function getWithEmail($id){
        return $this->createQueryBuilder('c')
            ->join('c.email', 'e')
            ->addSelect('e')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }
    
    public function getActiveCampaignsQuery()
    {
        $query = $this
            ->createQueryBuilder('c')
            ->where('c.status != :status_ended')
            ->setParameter('status_ended', Campaign::STATUS_ENDED)
            ->orderBy('c.createdAt', 'desc')
            ->getQuery();

        return $query;
    }

    public function getCompletedCampaignsQuery()
    {
        $query = $this
            ->createQueryBuilder('c')
            ->where('c.status = :status_ended')
            ->setParameter('status_ended', Campaign::STATUS_ENDED)
            ->orderBy('c.createdAt', 'desc')
            ->getQuery();

        return $query;
    }
}
