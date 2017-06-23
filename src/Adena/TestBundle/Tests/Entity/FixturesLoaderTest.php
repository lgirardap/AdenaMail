<?php

namespace Adena\TestBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FixturesLoaderTest
 *
 * @ORM\Table(name="fixtures_loader_test")
 * @ORM\Entity(repositoryClass="Adena\TestBundle\Tests\Repository\FixturesLoaderTestRepository")
 */
class FixturesLoaderTest
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
     * @ORM\Column(name="data1", type="string", length=255)
     */
    private $data1;

    /**
     * @var string
     *
     * @ORM\Column(name="data2", type="string", length=255)
     */
    private $data2;


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
     * Set data1
     *
     * @param string $data1
     *
     * @return FixturesLoaderTest
     */
    public function setData1($data1)
    {
        $this->data1 = $data1;

        return $this;
    }

    /**
     * Get data1
     *
     * @return string
     */
    public function getData1()
    {
        return $this->data1;
    }

    /**
     * Set data2
     *
     * @param string $data2
     *
     * @return FixturesLoaderTest
     */
    public function setData2($data2)
    {
        $this->data2 = $data2;

        return $this;
    }

    /**
     * Get data2
     *
     * @return string
     */
    public function getData2()
    {
        return $this->data2;
    }
}

