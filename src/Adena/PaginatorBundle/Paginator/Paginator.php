<?php

namespace Adena\PaginatorBundle\Paginator;

use Doctrine\ORM\EntityManagerInterface;
use \Adena\PaginatorBundle\Tools\Pagination\AdenaPaginator;

class Paginator
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Doctrine\ORM\Query $query
     * @param int                 $page
     * @param int                 $nbPerPage
     *
     * @return \Adena\PaginatorBundle\Tools\Pagination\AdenaPaginator
     */
    public function paginate($query, $page, $nbPerPage){
        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be one or more.');
        }

        $query
            ->setFirstResult(($page-1) * $nbPerPage)
            ->setMaxResults($nbPerPage)
        ;

        $paginator = new AdenaPaginator($query);

        $nbPages = ceil(count($paginator) / $nbPerPage);

        if ($page > 1 && $page > $nbPages) {
            throw new \InvalidArgumentException("Page ".$page." does not exist.");
        }

        // Add our own parameters
        $paginator->setPage($page);
        $paginator->setNbPerPage($nbPerPage);
        $paginator->setNbPages($nbPages);

        return $paginator;
    }
}