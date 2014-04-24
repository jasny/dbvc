<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors, ConsoleKit\Widgets\Checklist;

/**
 * Mark an update as run.
 * 
 * @usage $0 mark update ... [options]
 * 
 * @opt --quiet           -q  Don't output any message
 * @opt --verbose         -v  Increase verbosity
 * @opt --working-dir=DIR     If specified, use the given directory as working directory
 * @opt --force           -f  Continue after an error
 */
class MarkCommand extends Command
{
    /**
     * Execute command
     * 
     * @param array $args
     * @param array $options
     */
    public function execute(array $args, array $options = array())
    {
        $this->prepare($options);
        $this->force = !empty($options['force']) || !empty($options['f']); 
        
        if (!$this->databaseIsReady()) {
            exit(1);
        }
        
        if (empty($args)) {
            $this->writeerr("Specify which updates should be marked as done.\n");
            return;
        }
        
        $updates = array_intersect($this->dbvc()->getUpdates(), $args);
                
        if (empty($updates)) {
            $this->writeln("Nothing to do");
            return;
        }
        
        $this->dbvc()->db()->markUpdate($updates);
        
        if ($this->verbosity) {
            $this->writeln("Marked as run:", Colors::YELLOW);
            $this->writeln("  " . join("\n  ", $updates));
        }
    }
}
