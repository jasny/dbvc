<?php

namespace Jasny;

/**
 * Database version control
 */
class DBVC
{
    /**
     * Version number
     * @var string
     */
    const VERSION = "0.1.3";
    
    
    /**
     * Version control system interface
     * @var DBVC\VCS
     */
    protected $vcs;
    
    /**
     * Database interface
     * @var DBVC\DB
     */
    protected $db;
    
    /**
     * Configuration
     * @var object
     */
    public $config;
    
    
    /**
     * Class constructor
     * 
     * @param object|string  Alternative configuration or config file 
     */
    public function __construct($config=null)
    {
        $this->loadConfig($config);
        $this->determineVCS();
        $this->connectDB();
        
        if (!file_exists($this->config->datadir)) {
            $dir = $this->config->datadir;
            if ($dir[0] !== '/' && $dir[1] !== ':') $dir = getcwd() . DIRECTORY_SEPARATOR . $dir;
            
            throw new \RuntimeException("Directory for data files $dir doesn't exist");
        }
    }
    
    /**
     * Load the configuration.
     * 
     * @param object|string  Alternative configuration or config file 
     */
    protected function loadConfig($config=null)
    {
        if (!isset($config)) $config = 'dbvc.json';
        
        if (is_scalar($config)) {
            if (!file_exists($config))
                throw new \RuntimeException("DBVC could not find the $config config file in " . getcwd());
            
            $this->config = json_decode(file_get_contents($config));
            if (!$this->config) throw new \Exception("Invalid config file '$config': " . json_last_error_msg());
        } else {
            $this->config = self::objectify($config);
        }
        
        if (!isset($this->config->datadir)) $this->config->datadir = 'dev';
    }
    
    /**
     * Determine which vcs interface to use.
     */
    protected function determineVCS()
    {
        if (isset($this->config->vcs)) {
            $class = __CLASS__ . '\\VCS\\' . ucfirst($this->config->vcs);
            
            $this->vcs = new $class();
            return;
        }
        
        $vcsDir = __DIR__ . DIRECTORY_SEPARATOR . 'DBVC' . DIRECTORY_SEPARATOR . 'VCS';
        foreach (scandir($vcsDir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
            
            $class = __CLASS__ . '\\VCS\\' . pathinfo($file, PATHINFO_FILENAME);
            
            if ($class::isUsed()) {
                $this->vcs = new $class();
                return;
            }
        }
        
        throw new \RuntimeException("Could not determine which VCS is used. "
            . "Set vcs to 'none' in the configuration if you want to use DBVC without a VCS");
    }

    /**
     * Connect to the database
     */
    protected function connectDB()
    {
        if (!isset($this->config->db->driver))
            throw new \RuntimeException("DB driver not defined in dbvc.json");
        
        $class = __CLASS__ . '\\DB\\' . ucfirst($this->config->db->driver);
        $this->db = new $class($this->config->db);
    }
    
    /**
     * Get schema file to create the database.
     * 
     * @return string
     */
    public function getDBSchema()
    {
        $files = glob($this->config->datadir . DIRECTORY_SEPARATOR . 'schema.*');
        return !empty($files) ? $files[0] : null;
    }
    
    
    /**
     * Get a list with all available update files in the correct order.
     * 
     * @return array
     */
    public function getUpdateFiles()
    {
        return $this->vcs->getUpdateFiles($this->config->datadir . DIRECTORY_SEPARATOR . 'updates');
    }
    
    /**
     * Get a list with all available updates in the correct order.
     * 
     * @return array
     */
    public function getUpdatesAvailable()
    {
        return array_keys($this->vcs->getUpdateFiles($this->config->datadir . DIRECTORY_SEPARATOR . 'updates'));
    }
    
    /**
     * Get a list with updates that have already been run.
     * 
     * @return array
     */
    public function getUpdatesDone()
    {
        return $this->db->getUpdates();
    }
    
    /**
     * Get a list with updates that haven't been run (in the correct order).
     * 
     * @return array
     */
    public function getUpdates()
    {
        return array_diff($this->getUpdatesAvailable(), $this->getUpdatesDone());
    }
    
    
    /**
     * Get the database interface
     * 
     * @param array $update
     */
    public function db()
    {
        return $this->db;
    }
    
    
    /**
     * Turn associative array into object
     * 
     * @param mixed $var
     * @return mixed
     */
    protected static function objectify($var)
    {
        if (is_array($var) && (bool)count(array_filter(array_keys($var), 'is_string'))) {
            $var = (object)$var;
            foreach ($var as &$value) {
                $value = self::objectify($value);
            }
        }
        
        return $var;
    }
}
