<?php

namespace Jasny\DBVC\Console;

use ConsoleKit\Colors;

/**
 * Create the database from schema.
 * 
 * @usage $0 create [options]
 * 
 * @opt --quiet           -q  Don't output any message
 * @opt --verbose         -v  Increase verbosity
 * @opt --working-dir=DIR     If specified, use the given directory as working directory
 * @opt --config=FILE         Use an alternative config file
 */
class CreateCommand extends Command
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

        $db = $this->dbvc()->db();
        $dbname = $this->dbvc->config->db->dbname;
        
        if ($db->exists()) {
            $this->writeerr("Database '$dbname' already exist.");
            exit(1);
        }
        
        if ($this->verbosity) {
            $this->write("Creating database '$dbname' ");
        }
        
        $schema = $this->dbvc()->getDBSchema();
        $db->create($schema);
        
        $this->writeln("OK", Colors::GREEN);
    }
}
