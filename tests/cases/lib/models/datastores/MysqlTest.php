<?php
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
