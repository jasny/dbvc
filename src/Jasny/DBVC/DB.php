<?php

namespace Jasny\DBVC;

/**
 * Definition of database interfaces used by DBVC
 */
interface DB
{
    /**
     * Class constructor
     * 
     * @param object $config
     */
    public function __construct($config);

    /**
     * Select the database
     */
    public function selectDB();
    
    
    /**
     * Check if the database exists
     * 
     * @return boolean
     */
    public function exists();
    
    /**
     * Check if DBVC is initialised on the database
     * 
     * @return boolean
     */
    public function isInitialised();

    
    /**
     * Create the database from schema
     * 
     * @param string $schema
     */
    public function create($schema);
    
    /**
     * Create DBVS table
     */
    public function init();
    
    /**
     * Run an update file
     * 
     * @param string $file
     */
    public function run($file);
    
    /**
     * Mark update(s) as run
     * 
     * @param string|array $update
     */
    public function markUpdate($update);
    
    /**
     * Get a list with updates already performed on the DB
     * 
     * @return array
     */
    public function getUpdates();
}
