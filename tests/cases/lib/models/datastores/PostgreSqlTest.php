<?php

require_once 'tests/lib/SqlDatabaseTestCase.php';
require_once 'lib/models/datastores/DataStore.php';
require_once 'lib/models/datastores/SqlDatabase.php';
require_once 'lib/models/datastores/Postgresql.php';

class PostgreSqlTest extends \ntentan\test_cases\SqlDatabaseTestCase
{
    protected function setUp()
    {
        require "tests/config/config.php";
        $config['application']['context'] = 'postgresql_test';
        \ntentan\Ntentan::setup($config);
        $this->datastoreName = 'postgresql';
        parent::setUp();
    }

    protected function getConnection()
    {
        require "tests/config/config.php";
        $pdo = new PDO(
            "pgsql:host={$config['postgresql_test']['database_host']};dbname={$config['postgresql_test']['database_name']}",
            $config['postgresql_test']['database_user'],
            $config['postgresql_test']['database_password']
        );
        return $this->createDefaultDBConnection($pdo);
    }
    
    protected function getInstance()
    {
        require "tests/config/config.php";
        $parameters['hostname'] = $config['postgresql_test']['database_host'];
        $parameters['username'] = $config['postgresql_test']['database_user'];
        $parameters['password'] = $config['postgresql_test']['database_password'];
        $parameters['database'] = $config['postgresql_test']['database_name'];
        return new \ntentan\models\datastores\Postgresql($parameters);
    }
}
