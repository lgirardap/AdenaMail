<?php

namespace Adena\TestBundle\FixturesLoader;



use Doctrine\ORM\EntityManagerInterface;

class FixturesLoader
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct( EntityManagerInterface $em )
    {
        $this->em = $em;
    }


    public function loadFixtures( $fixtureFiles = array() )
    {
        /** @var $doctrine \Doctrine\Common\Persistence\ManagerRegistry */
//        $doctrine = $this->getContainer()->get('doctrine');
//        $em = $doctrine->getManager($input->getOption('em'));
//
//        $dirOrFile = $input->getOption('fixtures');
//        if ($dirOrFile) {
//            $paths = is_array($dirOrFile) ? $dirOrFile : array($dirOrFile);
//        } else {
//            $paths = array();
//            foreach ($this->getApplication()->getKernel()->getBundles() as $bundle) {
//                $paths[] = $bundle->getPath().'/DataFixtures/ORM';
//            }
//        }
//
//        $loader = new DataFixturesLoader($this->getContainer());
//        foreach ($paths as $path) {
//            if (is_dir($path)) {
//                $loader->loadFromDirectory($path);
//            } elseif (is_file($path)) {
//                $loader->loadFromFile($path);
//            }
//        }
//        $fixtures = $loader->getFixtures();
//        if (!$fixtures) {
//            throw new InvalidArgumentException(
//                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
//            );
//        }
//        $purger = new ORMPurger($em);
//        $purger->setPurgeMode($input->getOption('purge-with-truncate') ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE);
//        $executor = new ORMExecutor($em, $purger);
//        $executor->setLogger(function ($message) use ($output) {
//            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
//        });
//        $executor->execute($fixtures, $input->getOption('append'),$input->getOption('multiple-transactions'));
    }
}