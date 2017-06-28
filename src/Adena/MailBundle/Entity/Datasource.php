<?php

namespace Adena\MailBundle\Entity;

use Adena\MailBundle\Validator\Constraints as AdenaAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Datasource
 *
 * @ORM\Table(name="datasource")
 * @ORM\Entity(repositoryClass="Adena\MailBundle\Repository\DatasourceRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * The following is a custom validator to check that the provided parameters are actually correct by pinging the
 * associated MySQL server.
 * @AdenaAssert\DatasourceCanConnect()
 */
class Datasource
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
     * @ORM\Column(name="host", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $host;

    /**
     * @var integer
     *
     * @ORM\Column(name="port", type="integer", length=5)
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    private $port;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * A non persisted field that's used to create the encoded password.
     *
     * @var string
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="database_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $databaseName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $name;

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
     * Set host
     *
     * @param string $host
     *
     * @return Datasource
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param string $port
     *
     * @return Datasource
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Datasource
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Datasource
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set databaseName
     *
     * @param string $databaseName
     *
     * @return Datasource
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * Get databaseName
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Datasource
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
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return Datasource
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        // The plainPassword changed so the password is not valid anymore.
        // Let's set it to null so it is regenerated when doctrine saves the entity
        $this->password = null;

        return $this;
    }

    /**
     * Use this instead of the setPlainPassword() when you want to change the $plainPassword but NOT reset the $password
     * Can be used when you want to change the $plainPassword without triggering a doctrine update.
     *
     * @param string $plainPassword
     *
     * @return Datasource
     */
    public function initPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
