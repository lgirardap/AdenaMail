<?php

namespace Adena\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MailingList
 *
 * @ORM\Table(name="mailing_list")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\MailingListRepository")
 */
class MailingList
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var Datasource
     * 
     * @ORM\ManyToOne(targetEntity="Adena\MailBundle\Entity\Datasource")
     * @ORM\JoinColumn(nullable=false)
     */
    private $datasource;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MailingList
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return MailingList
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return MailingList
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set datasource
     *
     * @param \Adena\MailBundle\Entity\Datasource $datasource
     *
     * @return MailingList
     */
    public function setDatasource(\Adena\MailBundle\Entity\Datasource $datasource)
    {
        $this->datasource = $datasource;

        return $this;
    }

    /**
     * Get datasource
     *
     * @return \Adena\MailBundle\Entity\Datasource
     */
    public function getDatasource()
    {
        return $this->datasource;
    }
}
