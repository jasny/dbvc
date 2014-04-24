<?php

namespace Jasny\DBVC\DB;

/**
 * MySQL interface for DBVC
 */
class Mysql extends PDO
{
    /** @var string */
    protected $dbname;
    
    /**
     * Class constructor
     * 
     * @param object $config
     */
    public function __construct($config)
    {
        if (!isset($config->dbname)) throw new \RuntimeException("Database name not configured");
        
        $this->dbname = $config->dbname;
        
        $dsn = array();
        if (isset($config->host)) $dsn[] = "host=" . $config->host;
        if (isset($config->port)) $dsn[] = "port=" . $config->port;
        if (isset($config->unix_socket)) $dsn[] = "port=" . $config->unix_socket;
        if (isset($config->charset)) $dsn[] = "port=" . $config->charset;
        
        $this->connect("mysql:" . join(';', $dsn), $config->username, $config->password);
        if ($this->exists()) $this->selectDB();
    }
    
    /**
     * Select the database
     */
    public function selectDB()
    {
        $this->dbh->exec("USE `{$this->dbname}`");
    }
    

    /**
     * Check if the database exists
     * 
     * @return boolean
     */
    public function exists()
    {
       return $this->dbh->query("SHOW DATABASES LIKE " . $this->dbh->quote($this->dbname))->fetch() !== false;
    }

    /**
     * Check if DBVC table exists
     * 
     * @return boolean
     */
    public function isInitialised()
    {
       return $this->dbh->query("SHOW TABLES LIKE '_dbvc'")->fetch() !== false;
    }

    
    /**
     * Create the database from schema
     * 
     * @param string $schema
     */
    public function create($schema)
    {
        $this->dbh->query("CREATE DATABASE `" . $this->dbname . "`");
        $this->selectDB();
        
        $this->run($schema);
    }
    
    /**
     * Create DBVS table
     */
    public function init()
    {
        $this->dbh->exec("CREATE TABLE `_dbvc` (`update` VARCHAR(128) NOT NULL,"
            . "`update_time` TIMESTAMP NULL DEFAULT NULL)");
    }
    
    /**
     * Get a list with updates already performed on the DB
     * 
     * @return array
     */
    public function getUpdates()
    {
        $updates = array();
        
        $result = $this->dbh->query("SELECT `update` FROM `_dbvc`");
        while ($update = $result->fetchColumn()) {
            $updates[] = $update;
        }
        
        return $updates;
    }
    
    /**
     * Mark update(s) as run
     * 
     * @param string|array $update
     */
    public function markUpdate($update)
    {
        $updates = (array)$update;
        
        $values = [];
        foreach ($updates as $update) {
            $values[] = "(" . $this->dbh->quote($update) . ", NOW())";
        }
        
        $this->dbh->exec("INSERT IGNORE `_dbvc` VALUES " . join(',', $values));
    }
}
