<?php

namespace Adena\PaginatorBundle\Tools\Pagination;


class AdenaPaginator extends \Doctrine\ORM\Tools\Pagination\Paginator
{
    private $page;
    private $nbPerPage;
    private $nbPages;

    /**
     * Get nbPerPage
     *
     * @return mixed
     */
    public function getNbPerPage()
    {
        return $this->nbPerPage;
    }

    /**
     * Set nbPerPage
     *
     * @param mixed $nbPerPage
     *
     * @return AdenaPaginator
     */
    public function setNbPerPage($nbPerPage)
    {
        $this->nbPerPage = $nbPerPage;

        return $this;
    }

    /**
     * Get page
     *
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set page
     *
     * @param mixed $page
     *
     * @return AdenaPaginator
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get nbPages
     *
     * @return mixed
     */
    public function getNbPages()
    {
        return $this->nbPages;
    }

    /**
     * Set nbPages
     *
     * @param mixed $nbPages
     *
     * @return AdenaPaginator
     */
    public function setNbPages($nbPages)
    {
        $this->nbPages = $nbPages;

        return $this;
    }
}