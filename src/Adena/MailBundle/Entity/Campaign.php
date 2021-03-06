<?php

namespace Adena\MailBundle\Entity;

use Adena\MailBundle\Validator\Constraints as AdenaAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\InvalidArgumentException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Campaign
 *
 * @ORM\Table(name="campaign")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\CampaignRepository")
 *
 * // Makes sure that the associated MailingLists all have the required parameter to send the attached Email
 * @AdenaAssert\CampaignCanSendEmail()
 *
 * @UniqueEntity("name")
 */
class Campaign
{
    const STATUS_NEW = 'new';
    const STATUS_TESTING = 'testing';
    const STATUS_TESTED = 'tested';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PAUSED = 'paused';
    const STATUS_ENDED = 'ended';
    const STATUSES = [
        self::STATUS_NEW => self::STATUS_NEW,
        self::STATUS_TESTING => self::STATUS_TESTING,
        self::STATUS_TESTED => self::STATUS_TESTED,
        self::STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
        self::STATUS_PAUSED => self::STATUS_PAUSED,
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
     * @var string
     *
     * @ORM\Column(name="from_email", type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private $fromEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255, nullable=true)
     */
    private $fromName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\NotBlank()
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="emails_count", type="integer")
     */
    private $emailsCount;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Adena\MailBundle\Entity\MailingList", cascade={"persist"}, inversedBy="campaigns")
     * @Assert\Valid()
     * @Assert\Count(min = 1)
     */
    private $mailingLists;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Adena\MailBundle\Entity\MailingList", cascade={"persist"})
     * @ORM\JoinTable(name="campaign_test_mailing_list")
     * @Assert\Valid()
     */
    private $testMailingLists;

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
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Adena\MailBundle\Entity\Queue", mappedBy="campaign", cascade={"remove"})
     *
     */
    private $queues;

    /**
     * @var SendersList
     * @ORM\ManyToOne(targetEntity="Adena\MailBundle\Entity\SendersList")
     * @Assert\Valid()
     * @Assert\NotBlank
     */
    private $sendersList;

    public function __construct()
    {
        $this->mailingLists = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_NEW;
        $this->emailsCount = 0;
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
        if($mailingList->getIsTest()){
            throw new InvalidArgumentException('You can only add regular mailinglists.');
        }
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

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     *
     * @return Campaign
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set emailsCount
     *
     * @param integer $emailsCount
     *
     * @return Campaign
     */
    public function setEmailsCount($emailsCount)
    {
        $this->emailsCount = $emailsCount;

        return $this;
    }

    /**
     * Get emailsCount
     *
     * @return integer
     */
    public function getEmailsCount()
    {
        return $this->emailsCount;
    }

    /**
     * Add queue
     *
     * @param \Adena\MailBundle\Entity\Queue $queue
     *
     * @return Campaign
     */
    public function addQueue(\Adena\MailBundle\Entity\Queue $queue)
    {
        $this->queues[] = $queue;

        return $this;
    }

    /**
     * Remove queue
     *
     * @param \Adena\MailBundle\Entity\Queue $queue
     */
    public function removeQueue(\Adena\MailBundle\Entity\Queue $queue)
    {
        $this->queues->removeElement($queue);
    }

    /**
     * Get queues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * Add testMailingList
     *
     * @param \Adena\MailBundle\Entity\MailingList $testMailingList
     *
     * @return Campaign
     */
    public function addTestMailingList(\Adena\MailBundle\Entity\MailingList $testMailingList)
    {
        if(!$testMailingList->getIsTest()){
            throw new InvalidArgumentException('You can only add test mailinglists.');
        }
        $this->testMailingLists[] = $testMailingList;

        return $this;
    }

    /**
     * Remove testMailingList
     *
     * @param \Adena\MailBundle\Entity\MailingList $testMailingList
     */
    public function removeTestMailingList(\Adena\MailBundle\Entity\MailingList $testMailingList)
    {
        $this->testMailingLists->removeElement($testMailingList);
    }

    /**
     * Get testMailingLists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTestMailingLists()
    {
        return $this->testMailingLists;
    }

    /**
     * Set fromEmail
     *
     * @param string $fromEmail
     *
     * @return Campaign
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Get fromEmail
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Set fromName
     *
     * @param string $fromName
     *
     * @return Campaign
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set sendersList
     *
     * @param \Adena\MailBundle\Entity\SendersList $sendersList
     *
     * @return Campaign
     */
    public function setSendersList(\Adena\MailBundle\Entity\SendersList $sendersList = null)
    {
        $this->sendersList = $sendersList;

        return $this;
    }

    /**
     * Get sendersList
     *
     * @return \Adena\MailBundle\Entity\SendersList
     */
    public function getSendersList()
    {
        return $this->sendersList;
    }
}
