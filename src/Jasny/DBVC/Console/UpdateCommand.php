<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors, ConsoleKit\Widgets\Checklist;

/**
 * Update the database.
 * 
 * @usage $0 update [update ...] [options]
 * 
 * @opt --quiet           -q  Don't output any message
 * @opt --verbose         -v  Increase verbosity
 * @opt --working-dir=DIR     If specified, use the given directory as working directory
 * @opt --config=FILE         Use an alternative config file
 * @opt --force           -f  Continue after an error
 */
class UpdateCommand extends Command
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
        
        $available = $this->dbvc()->getUpdateFiles();
        $files = $available;
        
        if (!empty($args)) $files = array_intersect_key($files, array_fill_keys($args, null));
        
        $done = $this->dbvc()->getUpdatesDone();
        $updates = array_diff_key($files, array_fill_keys($done, null));
        
        if (empty($updates)) {
            $this->writeln("Nothing to do");
            return;
        }

        if ($this->verbosity === 0) {
            $this->runUpdatesQuiet($updates);
        } else {
            $this->runUpdates($updates);
        }
        
        if ($this->verbosity >= 2) {
            // List missing
        }
    }
    
    /**
     * Run a list of updates
     * 
     * @param array $files
     */
    protected function runUpdates(array $files)
    {
        $steps = array();
        $config = $this->dbvc()->config;
        $checklist = new Checklist($this->getConsole()->getTextWriter());
        $db = $this->dbvc()->db();
        
        $this->writeln("Running updates for database '{$config->db->dbname}':", Colors::YELLOW);
        
        foreach ($files as $update => $file) {
            $file = $config->datadir . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $file;
            $message = "  $update ";
            
            if ($this->force) {
                $steps[$message] = function () use ($db, $update, $file) {
                    try {
                        $db->run($file);
                    } catch (\Exception $e) {
                        return false;
                    }
                    
                    $db->markUpdate($update);
                    return true;
                };
            } else {
                $steps[$message] = function () use ($db, $update, $file) {
                    $db->run($file);
                    $db->markUpdate($update);
                    return true;
                };
            }
        }
        
        $checklist->run($steps);
    }
    
    /**
     * Run a list of updates quietly
     * 
     * @param array $files
     */
    protected function runUpdatesQuiet(array $files)
    {
        $db = $this->dbvc()->db();
        
        foreach ($files as $update => $file) {
            $file = $this->dbvc()->config->datadir . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $file;
            
            try {
                $db->run($file);
            } catch (\Exception $e) {
                $this->writeerr("Update $update failed.");
                
                if (!$this->force) {
                    $this->getConsole()->writeException($e);
                    break;
                }
            }
            
            $db->markUpdate($update);
        }
    }
}
