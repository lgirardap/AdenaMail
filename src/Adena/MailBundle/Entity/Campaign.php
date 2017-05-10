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
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ENDED = 'ended';
    const STATUSES = [
        self::STATUS_NEW => self::STATUS_NEW,
        self::STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
        self::STATUS_ENDED => self::STATUS_ENDED,
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

    /**
     * @var string
     *
     * @ORM\Column(name="status", length=255, type="string")
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @var \Adena\MailBundle\Entity\Email
     *
     * @ORM\ManyToOne(targetEntity="Adena\MailBundle\Entity\Email")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     */
    private $email;

    public function __construct()
    {
        $this->mailingLists = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_NEW;
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

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Campaign
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->getStatuses())) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        return self::STATUSES;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * Set email
     *
     * @param \Adena\MailBundle\Entity\Email $email
     *
     * @return Campaign
     */
    public function setEmail(\Adena\MailBundle\Entity\Email $email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return \Adena\MailBundle\Entity\Email
     */
    public function getEmail()
    {
        return $this->email;
    }
}
