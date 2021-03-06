<?php

namespace Adena\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Queue
 *
 * @ORM\Table(name="queue", uniqueConstraints={@ORM\UniqueConstraint(name="email_campaign_unique", columns={"email", "campaign_id"})})
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\QueueRepository")
 */
class Queue
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var \Adena\MailBundle\Entity\Campaign
     *
     * @ORM\ManyToOne(targetEntity="Adena\MailBundle\Entity\Campaign", inversedBy="queues")
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

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
     * Set email
     *
     * @param string $email
     *
     * @return Queue
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set campaign
     *
     * @param \Adena\MailBundle\Entity\Campaign $campaign
     *
     * @return Queue
     */
    public function setCampaign(\Adena\MailBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return \Adena\MailBundle\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Queue
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
