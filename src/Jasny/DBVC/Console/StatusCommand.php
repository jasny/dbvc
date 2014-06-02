<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors, ConsoleKit\TextFormater, Jasny\ConsoleKit\Widgets\Table;

/**
 * Output a list of updates.
 * 
 * @usage $0 status [options]
 * 
 * @opt --quiet           -q  Don't output any message
 * @opt --verbose         -v  Increase verbosity
 * @opt --working-dir=DIR     If specified, use the given directory as working directory
 * @opt --config=FILE         Use an alternative config file
 * @opt --notification    -n  Output as notification
 * @opt --all             -a  Show a lists with all updates and their status
 * @opt --short           -s  Use abbreviations and exclude annotations
 * 
 * @example `$0 status`       Show updates that need to be run
 * @example `$0 status -asq`  Show machine processable list with all updated
 */
class StatusCommand extends Command
{
    /**
     * Use abbreviations
     * @var boolean
     */
    protected $short = false;
    
    /**
     * Execute command
     * 
     * @param array $args
     * @param array $options
     */
    public function execute(array $args, array $options = array())
    {
        $this->prepare($options);
        $this->short = !empty($options['s']) || !empty($options['short']);
        
        if (!$this->databaseIsReady()) return;
        
        if (!empty($options['n']) || !empty($options['notification'])) {
            $this->showNotification();
        } elseif (empty($options['a']) && empty($options['all'])) {
            $this->showSimple();
        } else {
            $this->showComplete();
        }
    }
    
    /**
     * Show a simple lists with only the updates that need to be run
     */
    public function showNotification()
    {
        $updates = $this->dbvc()->getUpdates();
        $dbname = $this->dbvc()->config->db->dbname;

        if (empty($updates)) {
            if ($this->verbosity >= 2) $this->writeln("Nothing to run");
        } else {
            $count = count($updates);
            $todo = $count === 1 ? "is 1 update" : "are $count updates";
            $this->writeln("There $todo ready to be run for database '$dbname'", Colors::YELLOW);
        }
    }
    
    /**
     * Show a simple lists with only the updates that need to be run
     */
    public function showSimple()
    {
        $updates = $this->dbvc()->getUpdates();

        if (empty($updates)) {
            if ($this->verbosity) $this->writeln("Nothing to run");
            return;
        }
        
        $prefix = "";
        if ($this->verbosity) {
            $this->writeln("Updates to be run:", Colors::YELLOW);
            $prefix = "  ";
        }
        
        foreach ($updates as $update) {
            $this->writeln($prefix . $update);
        }
    }
    
    /**
     * Show a lists with all updates and their status
     */
    public function showComplete()
    {
        $updates = $this->dbvc()->getUpdatesAvailable();
        
        if (empty($updates)) {
            if ($this->verbosity) $this->writeln("No updates available");
            return;
        }
        
        $done = $this->dbvc()->getUpdatesDone();
        
        $prefix = "";
        if ($this->verbosity) $this->writeln("Available updates:", Colors::YELLOW);

        if ($this->short) {
            $prefix = $this->verbosity ? "  " : '';
            
            foreach ($updates as $update) {
                $status = !in_array($update, $done) ? 'o' : 'x';
                $this->writeln($prefix . $status . ' ' . $update);
            }
        } else {
            $rows = array();
            foreach ($updates as $update) {
                $status = !in_array($update, $done) ?
                    Colors::colorize('open', Colors::YELLOW) :
                    Colors::colorize('done', Colors::GREEN);
                $rows[] = array($update, $status);
            }
            
            $formater = new \ConsoleKit\TextFormater(array('indent' => $this->verbosity ? 2 : 0));
            
            $table = new Table(null, $rows, array('border' => false, 'frame' => false));
            $this->writeln($formater->format($table->render()));
        }
    }
}
