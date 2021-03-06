<?php

namespace Adena\TestBundle\FixturesLoader;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class FixturesLoader
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;
    private $projectRootDir;
    private $bundlesMetaData;

    public function __construct( EntityManagerInterface $em, $projectRootDir, $bundlesMetaData )
    {
        $this->em = $em;
        $this->projectRootDir = $projectRootDir;
        $this->bundlesMetaData = $bundlesMetaData;
    }


    /**
     * Load fixture from a specified array of paths
     *
     * @param null $paths
     *
     * @return array
     */
    public function loadFixturesFromPaths($paths = null )
    {
        if($paths){

            $paths = is_array($paths) ? $paths : array($paths);
            $fixtures = $this->loadFixtures( $paths );

        } else {

            $fixtures = $this->loadAllFixtures();
        }

        return $fixtures;
    }

    /**
     * Load all the bundles fixtures
     *
     * @return array
     */
    public function loadAllFixtures()
    {
        $paths = array();
        foreach ($this->bundlesMetaData as $bundle) {
            $paths[] = $bundle['path'].'/DataFixtures/ORM';
        }

        $fixtures = $this->loadFixtures( $paths );
        return $fixtures;
    }

    public function loadFixtures( Array $paths )
    {
        $loader = new Loader();
        foreach ($paths as $path) {
            $path = $this->projectRootDir."/../".$path;
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);

            } elseif (is_file($path)) {
                $loader->loadFromFile($path);
            }
        }

        // If no fixtures are found in the specified folder we throw an exception
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $this->deleteAllFixtures();
        $this->em->getConnection()->executeUpdate("SET foreign_key_checks = 0;");

        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($fixtures);

        $this->em->getConnection()->executeUpdate("SET foreign_key_checks = 1;");

        return $fixtures;
    }


    /**
     * Delete all the fixture inserted in the Schema
     */
    public function deleteAllFixtures(){

        $this->em->getConnection()->executeUpdate("SET foreign_key_checks = 0;");
        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($this->em, $purger);
        $executor->purge();
        $this->em->getConnection()->executeUpdate("SET foreign_key_checks = 1;");

    }
}