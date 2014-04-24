<?php

namespace Jasny\DBVC\DB;

use Jasny\DBVC\DB;

/**
 * Base class for database interfaces using PDO
 */
abstract class PDO implements DB
{
    /**
     * Database connection
     * @var \PDO
     */
    protected $dbh;
    
    /**
     * Connect to the database;
     */
    protected function connect($dsn, $username = null, $passwd = null, $options = null)
    {
        $this->dbh = new \PDO($dsn, $username, $passwd, $options);
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 1);
    }
    
    /**
     * Run an update file
     * 
     * @param string $file
     */
    public function run($file)
    {
        $queries = file_get_contents($file);
        
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        
        $stmt = $this->dbh->prepare($queries);
        $stmt->execute();
        
        while ($stmt->nextRowset());
        
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $err = $stmt->errorInfo();
        if ($err[1]) throw new \RuntimeException($err[2]);
    }
}
