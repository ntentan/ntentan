<?php

require_once TEST_HOME . '/lib/SqlDatabaseTestCase.php';
require_once CODE_HOME . '/lib/models/datastores/DataStore.php';
require_once CODE_HOME . '/lib/models/datastores/SqlDatabase.php';
require_once CODE_HOME . '/lib/models/datastores/Postgresql.php';

class PostgreSqlTest extends \ntentan\test_cases\SqlDatabaseTestCase
{
    
    protected function setUp()
    {
        $this->setupDatabase('postgresql');
        parent::setUp();
    }

    protected function getConnection()
    {
        $config = $this->getDbConfig();
        $pdo = new PDO(
            "pgsql:host={$config['host']};dbname={$config['name']}",
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
        return new \ntentan\models\datastores\Postgresql($parameters);
    }
}
