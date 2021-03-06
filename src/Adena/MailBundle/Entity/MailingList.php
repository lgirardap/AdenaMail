<?php

namespace Adena\MailBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Adena\MailBundle\Validator\Constraints as AdenaAssert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * MailingList
 *
 * @ORM\Table(name="mailing_list")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\MailingListRepository")
 *
 * // Makes sure that the content can be transformed to an array with at least an "email" column.
 * @AdenaAssert\MailingListContentIsValid()
 */
class MailingList
{
    const TYPE_QUERY = 'query';
    const TYPE_LIST = 'list';
    const TYPES = [
        self::TYPE_QUERY => self::TYPE_QUERY,
        self::TYPE_LIST => self::TYPE_LIST
    ];

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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getTypes")
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_test", type="boolean")
     */
    private $isTest;

    /**
     * @var Datasource
     * 
     * @ORM\ManyToOne(targetEntity="Adena\MailBundle\Entity\Datasource", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $datasource;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Adena\MailBundle\Entity\Campaign", mappedBy="mailingLists")
     */
    private $campaigns;

    public function __construct()
    {
        $this->isTest = false;
        $this->campaigns = new ArrayCollection();
    }

    /**
     * Datasource is only checked when the type is "query", so we need a custom validator to handle it.
     *
     * @Assert\Callback()
     */
    public function validateDatasource(ExecutionContextInterface $context, $payload)
    {
        if(self::TYPE_QUERY === $this->getType()){
            // Mandatory
            $context
                ->getValidator()
                ->inContext($context)
                ->atPath('datasource')
                ->validate($this->datasource, new  Assert\NotBlank(), [Constraint::DEFAULT_GROUP]);

            // And valid
            $context
                ->getValidator()
                ->inContext($context)
                ->atPath('datasource')
                ->validate($this->datasource, new  Assert\Valid(), [Constraint::DEFAULT_GROUP]);
        }
    }

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
        if (!in_array($type, $this->getTypes())) {
            throw new \InvalidArgumentException("Invalid type");
        }
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

    public function getTypes(){
        return self::TYPES;
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

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Set isTest
     *
     * @param boolean $isTest
     *
     * @return MailingList
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Get isTest
     *
     * @return boolean
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Add campaign
     *
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     *
     * @return MailingList
     */
    public function addCampaign(\Adena\MailBundle\Entity\Campaign $campaign)
    {
        $this->campaigns[] = $campaign;

        return $this;
    }

    /**
     * Remove campaign
     *
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     */
    public function removeCampaign(\Adena\MailBundle\Entity\Campaign $campaign)
    {
        $this->campaigns->removeElement($campaign);
    }

    /**
     * Get campaigns
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }
}
