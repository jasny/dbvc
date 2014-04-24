<?php

namespace Jasny\DBVC\Console;

use Jasny\DBVC;
use Jasny\ConsoleKit\HelpCommand as BaseHelpCommand;

/**
 * Displays list of commands and help for command.
 * 
 * @usage $0 help [command_name]
 * @arg command_name The command name
 * 
 * @example `$0 help`         Show a list of all commands
 * @example `$0 help status`  Displays help for the status command
 */
class HelpCommand extends BaseHelpCommand
{
    /**
     * Execute command
     * 
     * @param array $args
     * @param array $options
     */
    public function execute(array $args, array $options = array())
    {
        if (!empty($options['version'])) return $this->showVersion();
        
        parent::execute($args, $options);
    }
    
    /**
     * Show available options and commands
     */
    protected function showCommands()
    {
        $this->writeln("DBVC - Database version control\n");
        parent::showCommands();
    }
    
    /**
     * Show the version number
     */
    protected function showVersion()
    {
        $this->writeln("dbvc version " . DBVC::VERSION);
    }
}
