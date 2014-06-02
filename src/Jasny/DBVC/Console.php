<?php

namespace Jasny\DBVC;

/**
 * Registry of available commands and command runner
 */
class Console extends \ConsoleKit\Console
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->addCommandsFromDir(__DIR__ . '/Console', 'Jasny\DBVC\Console');
        $this->setDefaultCommand('help');
    }
}
