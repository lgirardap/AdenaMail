<?php

namespace Adena\PaginatorBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(length=64)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }
}
