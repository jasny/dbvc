<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors;

/**
 * Initialise the database to maintain a list of processed updates.
 * 
 * @usage $0 init [options]
 * 
 * @opt --quiet       -q  Don't output any message
 * @opt --verbose     -v  Increase verbosity
 * @opt --working-dir     If specified, use the given directory as working directory
 */
class InitCommand extends Command
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
        
        if (!$this->databaseIsReady()) {
            exit(1);
        }
        
        $this->dbvc()->db()->init();
        
        if ($this->verbosity) {
            $this->writeln("Initialised {$this->dbvc->config->db->dbname}");

            $updates = $this->dbvc()->getUpdatesAvailable();
            if ($updates) {
                $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
                $this->writeln("\nWarning: There are " . count($count) . " updates that may already have been applied to "
                    . "the database. Use `$scriptName mark` to mark those updates as done.");
            }
        }
    }
    
    /**
     * Check if database exists and is *not* initialised.
     * 
     * @return boolean
     */
    protected function databaseIsReady()
    {
        $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        $dbname = $this->dbvc->config->db->dbname;
        
        if (!$this->dbvc()->db()->exists()) {
            $message = "Database '$dbname' doesn't exist.";
            if ($this->dbvc()->getDBSchema()) $message = " Run `$scriptName create` to create it.";
            
            $this->writeerr("$message\n");
            return false;
        }
        
        if ($this->dbvc()->db()->isInitialised()) {
            $this->writeerr("Database '$dbname' is already initialised.\n");
            return false;
        }
       
        return true;
    }    
}
