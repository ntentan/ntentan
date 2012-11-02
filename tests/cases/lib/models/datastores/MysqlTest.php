<?php

use ntentan\Ntentan;

require_once 'tests/lib/SqlDatabaseTestCase.php';
require_once 'lib/models/datastores/DataStore.php';
require_once 'lib/models/datastores/SqlDatabase.php';
require_once 'lib/models/datastores/Mysql.php';
require_once 'lib/sessions/Manager.php';
/**
 * 
 */
class MysqlTest extends \ntentan\test_cases\SqlDatabaseTestCase
{
    protected function setUp()
    {
        require "tests/config/config.php";
        $config['application']['context'] = 'mysql_test';
        \ntentan\Ntentan::setup($config);
        $this->datastoreName = 'mysql';
        parent::setUp();
    }

    protected function getConnection()
    {
        require "tests/config/config.php";
        $pdo = new PDO(
            "mysql:host={$config['mysql_test']['database_host']};dbname={$config['mysql_test']['database_name']}", 
            $config['mysql_test']['database_user'], 
            $config['mysql_test']['database_password']
        );
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getInstance()
    {
        require "tests/config/config.php";
        $parameters['hostname'] = $config['mysql_test']['database_host'];
        $parameters['username'] = $config['mysql_test']['database_user'];
        $parameters['password'] = $config['mysql_test']['database_password'];
        $parameters['database'] = $config['mysql_test']['database_name'];
        return new \ntentan\models\datastores\Mysql($parameters);
    }
}
