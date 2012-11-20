<?php
$config = array(
    'application' => array(
        'name' => 'Ntentan Test Suite',
        'context' => 'test',
        'ntentan_home' => '/home/travis/builds/ekowabaka/ntentan/',
        'namespace' => 'tests',
        'modules_path' => 'tests/app'
    ),
    'mysql_test' => array(
        'caching' => 'volatile',
        'debug' => true,
        'datastore' => 'mysql',
        'database_user' => 'root',
        'database_password' => '',
        'database_name' => 'ntentan_tests',
        'database_host' => 'localhost'
    ),
    'postgresql_test' => array(
        'caching' => 'volatile',
        'debug' => true,
        'datastore' => 'postgresql',
        'database_user' => 'postgres',
        'database_password' => '',
        'database_name' => 'ntentan_tests',
        'database_host' => 'localhost'
    ),    
);
