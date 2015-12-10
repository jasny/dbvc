<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors;

/**
 * Mark an update as run.
 * 
 * @usage $0 mark update ... [options]
 * 
 * @opt --quiet           -q  Don't output any message
 * @opt --verbose         -v  Increase verbosity
 * @opt --working-dir=DIR     If specified, use the given directory as working directory
 * @opt --config=FILE         Use an alternative config file
 * @opt --all             -a  Mark all updates as run
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
        $all = !empty($options['all']) || !empty($options['a']);
        
        if (!$this->databaseIsReady()) {
            exit(1);
        }
        
        if (empty($args) && !$all) {
            $this->writeerr("Specify which updates should be marked as done.\n");
            return;
        }
        
        $updates = $this->dbvc()->getUpdates();
        if (!$all) $updates = array_intersect($updates, $args);
                
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
