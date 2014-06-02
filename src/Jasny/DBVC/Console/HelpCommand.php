<?php

namespace Jasny\DBVC\Console;

use Jasny\DBVC;
use Jasny\ConsoleKit\HelpCommand as BaseHelpCommand;
use Jasny\ConsoleKit\Widgets\Table, ConsoleKit\TextFormater, ConsoleKit\Colors, ConsoleKit\Utils;

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

        if (empty($args)) {
            $this->writeln("DBVC - Database version control\n");
            $this->showUsage();
            $this->showOptions();
            $this->showCommands();
        } else {
            $this->showHelp($args[0], Utils::get($args, 1));
        }
    }
    
    /**
     * Show a list of available options
     */
    protected function showOptions()
    {
        $this->writeln("Options:", Colors::YELLOW);
        
        $rows = array(
            array(Colors::colorize('--version', Colors::GREEN), "Show version number"),
            array(
                Colors::colorize('--working-dir=DIR', Colors::GREEN),
                "If specified, use the given directory as working directory"
            ),
            array(
                Colors::colorize('--config=FILE', Colors::GREEN),
                "Use an alternative config file"
            )
        );
        
        $formater = new TextFormater(array('indent' => 2));
        $table = new Table(null, $rows, array('border'=>false, 'frame'=>false));
        $this->writeln($formater->format($table->render()));
    }


    /**
     * Show the version number
     */
    protected function showVersion()
    {
        $this->writeln("dbvc version " . DBVC::VERSION);
    }
}
