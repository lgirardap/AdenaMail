<?php

namespace Adena\MailBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Campaign
 *
 * @ORM\Table(name="campaign")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\CampaignRepository")
 */
class Campaign
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetimetz")
     * @Assert\NotBlank()
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Adena\MailBundle\Entity\MailingList", cascade={"persist"})
     * @Assert\Valid()
     * @Assert\Count(min = 1)
     */
    private $mailingLists;

    public function __construct()
    {
        $this->mailingLists = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
     * @return Campaign
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Campaign
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Add mailingList
     *
     * @param \Adena\MailBundle\Entity\MailingList $mailingList
     *
     * @return Campaign
     */
    public function addMailingList(\Adena\MailBundle\Entity\MailingList $mailingList)
    {
        $this->mailingLists[] = $mailingList;

        return $this;
    }

    /**
     * Remove mailingList
     *
     * @param \Adena\MailBundle\Entity\MailingList $mailingList
     */
    public function removeMailingList(\Adena\MailBundle\Entity\MailingList $mailingList)
    {
        $this->mailingLists->removeElement($mailingList);
    }

    /**
     * Get mailingLists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMailingLists()
    {
        return $this->mailingLists;
    }
}
