DBVC
====

The `dbvc` commandline tool allows you to manage your database schema updates in your version control (git) repository.

## Configuration

DBVC looks for the `dbvc.json` configuration file.

    {
      "db": {
        "driver": "mysql",
        "host": "localhost",
        "username": "root",
        "password": "open",
        "dbname": "foobar"
      },
      "datadir": "dev",
      "vcs": "none"
    }

If `datadir` is omitted, it defaults to "dev". If `vcs` is omitted, the vcs is automatically determined.

### Supported database interfaces

  * mysql

You may [issue a feature request](https://github.com/jasny/dbvc/issues) to support other DBMSs.

### Supported version control systems

  * git
  * none

You may [issue a feature request](https://github.com/jasny/dbvc/issues) to support other VCSs.

When using git, the correct order of the updates is automatically found by examining the git log.

When selecting vcs 'none', updates are run in [natural order](http://www.php.net/manual/en/function.natsort.php). It's
up to you to prefix the update files with (for instance) a date, to make sure that run in the correct order


## Usage

Show a list of commands

    dbvc help

Show help on a specific command

    dbvc help init


Initialise DBVC for an existing database.

    dbvc init

Create a database dump. This is used to create the DB on a new environment.

    mysqldump foobar > dev/schema.php

Create the DB using the schema.

    dbvc create


Add an update file. These are used to update the DB on other environments.

    echo 'ALTER TABLE `foo` ADD COLUMN `status` BOOL DEFAULT 1;' > dev/updates/add-status-to-foo.sql

Mark an update as already run.

    dbvc mark add-status-to-foo


Show a list of updates that need to be run.

    dbvc status

Show all updates with their status.

    dbvc status --all

Update the database.

    dbvc update
