<?php

require_once 'tests/lib/SqlDatabaseTestCase.php';
require_once 'lib/models/datastores/DataStore.php';
require_once 'lib/models/datastores/SqlDatabase.php';
require_once 'lib/models/datastores/Mysql.php';

/**
 * 
 */
class MysqlTest extends \ntentan\test_cases\SqlDatabaseTestCase
{
    protected function setUp()
    {
        \ntentan\Ntentan::setup('tests/config/mysql_config.ini');
        parent::setUp();
    }

    protected function getConnection()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=ntentan_tests', 'root', 'root');
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getInstance()
    {
        $parameters['hostname'] = 'localhost';
        $parameters['username'] = 'root';
        $parameters['password'] = 'root';
        $parameters['database'] = 'ntentan_test';
        return new \ntentan\models\datastores\Mysql($parameters);
    }
}
