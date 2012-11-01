<?php
namespace ntentan\test_cases;

require "lib/Ntentan.php";

use ntentan\caching\Cache;

class CachingBackendTestCase extends \PHPUnit_Framework_TestCase
{
    public function testErrors()
    {
        $this->assertEquals(false, Cache::exists('non_existent'));
        $this->assertEquals(false, Cache::get('non_existent'));
    }
    
    public function testAdditions()
    {
        Cache::add('existent', 1);
        $data = Cache::get('existent');
        $this->assertEquals(true, is_integer($data));
        $this->assertEquals(1, $data);
        
        Cache::add('existent_string', 'Hello World! I am Ntentan');
        $data = Cache::get('existent_string');
        $this->assertEquals(true, is_string($data));
        $this->assertEquals('Hello World! I am Ntentan', $data);
        
        $this->assertEquals(true, Cache::exists('existent'));
    }
    
    /**
     * @depends testAdditions
     */
    public function testExpiry()
    {
        Cache::add('should_expire', 'something', 5);
        $this->assertEquals(true, Cache::exists('should_expire'));
        sleep(6);
        $this->assertEquals(false, Cache::exists('should_expire'));
        $this->assertEquals(false, Cache::get('should_expire'));
    }
    
    /**
     * @depends testAdditions
     */
    public function testRemovals()
    {
        Cache::remove('existent');
        $this->assertEquals(false, Cache::get('existent'));
        $this->assertEquals(false, Cache::exists('existent'));
    }
}
