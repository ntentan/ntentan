<?php

use ntentan\Ntentan;

require_once TEST_HOME . '/lib/SqlDatabaseTestCase.php';
require_once CODE_HOME . '/lib/models/datastores/DataStore.php';
require_once CODE_HOME . '/lib/models/datastores/SqlDatabase.php';
require_once CODE_HOME . '/lib/models/datastores/Mysql.php';
require_once CODE_HOME . '/lib/sessions/Manager.php';
/**
 * 
 */
class MysqlTest extends \ntentan\test_cases\SqlDatabaseTestCase
{
    protected function setUp()
    {
        $this->setupDatabase('mysql');
        parent::setUp();
    }

    protected function getConnection()
    {
        $config = $this->getDbConfig();
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['name']}", 
            $config['user'], 
            $config['password']
        );
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getInstance()
    {
        require $this->getDbConfig();
        $parameters['hostname'] = $config['host'];
        $parameters['username'] = $config['user'];
        $parameters['password'] = $config['password'];
        $parameters['database'] = $config['name'];
        return new \ntentan\models\datastores\Mysql($parameters);
    }
}
