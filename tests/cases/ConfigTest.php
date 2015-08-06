<?php
namespace ntentan\tests;

require 'vendor/autoload.php';

use ntentan\Config;

class ConfigTests extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Config::setContext('default');
    }
    
    public function testPlain() 
    {
        Config::init(__DIR__ . '/../fixtures/config/plain');
        $this->assertEquals(
            array (
                'default' => 
                array (
                  'app.debug' => true,
                  'app.caching' => 
                  array (
                    'driver' => 'redis',
                    'host' => 'redis.mytestserver.tld',
                  ),
                  'app' => 
                  array (
                    'debug' => true,
                    'caching' => 
                    array (
                      'driver' => 'redis',
                      'host' => 'redis.mytestserver.tld',
                    ),
                  ),
                  'db.datastore' => 'mysql',
                  'db.host' => 'localhost',
                  'db.user' => 'root',
                  'db.password' => 'root',
                  'db.name' => 'production',
                  'db' => 
                  array (
                    'datastore' => 'mysql',
                    'host' => 'localhost',
                    'user' => 'root',
                    'password' => 'root',
                    'name' => 'production',
                  ),
                ),
              ),
            Config::dump()
        );
        $this->assertEquals(true, Config::get('app.debug'));
        $this->assertEquals('root', Config::get('db.user'));
        $this->assertEquals(
            array (
                'datastore' => 'mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => 'root',
                'name' => 'production',
            ),
            Config::get('db')
        );
        $this->assertEquals(
            array (
                'driver' => 'redis',
                'host' => 'redis.mytestserver.tld',
            ),
            Config::get('app.caching')
        );
    }
    
    public function testContextualized()
    {
        Config::init(__DIR__ . '/../fixtures/config/contexts');
        $this->assertEquals(
            array (
                'default' => 
                array (
                  'app.debug' => false,
                  'app.caching' => 
                  array (
                    'driver' => 'redis',
                    'host' => 'redis.mytestserver.tld',
                  ),
                  'app' => 
                  array (
                    'debug' => false,
                    'caching' => 
                    array (
                      'driver' => 'redis',
                      'host' => 'redis.mytestserver.tld',
                    ),
                  ),
                  'db.datastore' => 'mysql',
                  'db.host' => 'localhost',
                  'db.user' => 'root',
                  'db.password' => 'root',
                  'db.name' => 'production',
                  'db' => 
                  array (
                    'datastore' => 'mysql',
                    'host' => 'localhost',
                    'user' => 'root',
                    'password' => 'root',
                    'name' => 'production',
                  ),
                ),
                'production' => 
                array (
                  'app.debug' => false,
                  'app.caching' => 
                  array (
                    'driver' => 'redis',
                    'host' => 'redis.mytestserver.tld',
                  ),
                  'app' => 
                  array (
                    'debug' => false,
                    'caching' => 
                    array (
                      'driver' => 'redis',
                      'host' => 'redis.mytestserver.tld',
                    ),
                  ),
                  'db.datastore' => 'mysql',
                  'db.host' => 'localhost',
                  'db.user' => 'root',
                  'db.password' => 'root',
                  'db.name' => 'production',
                  'db' => 
                  array (
                    'datastore' => 'mysql',
                    'host' => 'localhost',
                    'user' => 'root',
                    'password' => 'root',
                    'name' => 'production',
                  ),
                ),
                'test' => 
                array (
                  'app.debug' => true,
                  'app.caching' => 
                  array (
                    'driver' => 'file',
                    'host' => '/cache/dir',
                  ),
                  'app' => 
                  array (
                    'debug' => true,
                    'caching' => 
                    array (
                      'driver' => 'file',
                      'host' => '/cache/dir',
                    ),
                  ),
                  'db.datastore' => 'mysql',
                  'db.host' => 'localhost',
                  'db.user' => 'root',
                  'db.password' => null,
                  'db.name' => 'test',
                  'db' => 
                  array (
                    'datastore' => 'mysql',
                    'host' => 'localhost',
                    'user' => 'root',
                    'password' => null,
                    'name' => 'test',
                  ),
                ),
              ),
            Config::dump()
        );
        $this->assertEquals(
            array (
                'datastore' => 'mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => 'root',
                'name' => 'production',
            ),
            Config::get('db')
        );
        Config::setContext('test');
        $this->assertEquals(
            array (
                'datastore' => 'mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => NULL,
                'name' => 'test',
            ),
            Config::get('db')
        );
    }
    
    public function testSet()
    {
        Config::init(__DIR__ . '/../fixtures/config/contexts');
        $this->assertEquals(
            array (
                'datastore' => 'mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => 'root',
                'name' => 'production',
            ),
            Config::get('db')
        );
        Config::set('db.name', 'changed');
        $this->assertEquals(
            array (
                'datastore' => 'mysql',
                'host' => 'localhost',
                'user' => 'root',
                'password' => 'root',
                'name' => 'changed',
            ),
            Config::get('db')
        );
        $this->assertEquals('changed', Config::get('db.name'));
    }
}
