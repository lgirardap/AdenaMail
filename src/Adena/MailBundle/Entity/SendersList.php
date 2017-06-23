<?php

namespace Adena\MailBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SendersList
 *
 * @ORM\Table(name="senders_list")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\SendersListRepository")
 */
class SendersList
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
     * @var string
     *
     * @ORM\Column(name="from_email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $fromEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $fromName;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Adena\MailBundle\Entity\Sender")
     * @Assert\Valid()
     * @Assert\Count(min = 1)
     */
    private $senders;

    public function __construct()
    {
        $this->senders = new ArrayCollection();
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
     * @return SendersList
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
     * Set fromEmail
     *
     * @param string $fromEmail
     *
     * @return SendersList
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
     * @return SendersList
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
     * Add sender
     *
     * @param \Adena\MailBundle\Entity\Sender $sender
     *
     * @return SendersList
     */
    public function addSender(\Adena\MailBundle\Entity\Sender $sender)
    {
        $this->senders[] = $sender;

        return $this;
    }

    /**
     * Remove sender
     *
     * @param \Adena\MailBundle\Entity\Sender $sender
     */
    public function removeSender(\Adena\MailBundle\Entity\Sender $sender)
    {
        $this->senders->removeElement($sender);
    }

    /**
     * Get senders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSenders()
    {
        return $this->senders;
    }
}
