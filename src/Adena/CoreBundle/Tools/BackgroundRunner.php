<?php

namespace Adena\CoreBundle\Tools;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Class BackgroundRunner
 * @package Adena\CoreBundle\Tools
 *
 * Allows a specify command to be launched in a separate PHP process.
 */
class BackgroundRunner
{
    private $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Runs a command in a new PHP Process so the main one does not have to wait.
     *
     * @param string $command The command to execute
     */
    public function run($command){
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen("start /B ". $command, "r"));
        }
        else {
            exec($command . " > /dev/null 2>/dev/null &");
        }
    }

    /**
     * Runs a symfony console command in a new PHP process.
     *
     * @param $command
     * @throws \Exception
     */
    public function runConsoleCommand($command){
        // Get the PHP executable path
        $phpFinder = new PhpExecutableFinder;
        if (!$phpPath = $phpFinder->find()) {
            throw new \Exception('The php executable could not be found, add it to your PATH environment variable and try again.');
        }

        // Get the console script location
        // TODO When we update to SF3.3, we can change to:
        // kernel.project_dir instead of kernel.root_dir/../
        $consolePath = $this->rootDir.'/../bin/console';

        $this->run($phpPath.' '.$consolePath.' '.$command);
    }
}