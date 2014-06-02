<?php

namespace Jasny\DBVC\Console;

use Jasny\DBVC;

/**
 * Base class for commands
 */
abstract class Command extends \ConsoleKit\Command
{
    /**
     * Verbosity level
     * @var int
     */
    protected $verbosity = 1;
    
    /**
     * Database version control interface
     * @var DBVC 
     */
    protected $dbvc;
    
    
    /**
     * Get database version control interface
     * 
     * @return DBVC
     */
    protected function dbvc()
    {
       return $this->dbvc;
    }

    /**
     * Check if database exists and if initialised.
     * 
     * @return boolean
     */
    protected function databaseIsReady()
    {
        $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        $dbname = $this->dbvc->config->db->dbname;
        
        if (!$this->dbvc()->db()->exists()) {
            $message = "Database '$dbname' doesn't exist.";
            if ($this->dbvc()->getDBSchema()) $message .= " Run `$scriptName create` to create it.";
            
            $this->writeerr("$message\n");
            return false;
        }

        if (!$this->dbvc()->db()->isInitialised()) {
            $this->writeerr("Database '$dbname' isn't initialised yet. Run `$scriptName init`.\n");
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Process common options and prepare DBVC
     * 
     * @param array $options
     */
    protected function prepare($options)
    {
        $config = isset($options['config']) ? $options['config'] : null;
        
        if (isset($options['d'])) $workingDir = $options['d'];
        if (isset($options['working-dir'])) $workingDir = $options['working-dir'];
        if (isset($workingDir) && $workingDir !== true) {
            if (!is_dir($workingDir)) throw new \Exception("Working directory '$workingDir' doesn't exist");
            chdir($workingDir);
        }
        
        if (!empty($options['q']) || !empty($options['quiet'])) {
            $this->verbosity = 0;
        } elseif (!empty($options['v']) && !empty($options)) {
            $this->verbosity = 2;
        }
        
        $this->dbvc = new DBVC($config);
    }
}
