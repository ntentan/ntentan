<?php

use ntentan\Ntentan;
use ntentan\caching\Cache;

class FileCacheTest extends \ntentan\test_cases\CachingTestCase
{
    public function setUp()
    {
        Cache::reInstantiate();
        Ntentan::$cacheMethod = 'file';
        Cache::setCachePath(TEST_HOME . '/tmp/');
    }
    
    
    public function testWrongPath()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_Error', 
            'The file cache directory *i_dont_think_this_dir_would_exist* was not found or is not writable!'
        );
        
        
        Ntentan::$debug = false; // prevent the error message from showing
        Cache::setCachePath('i_dont_think_this_dir_would_exist');
        Cache::add('test', 'should fail');
        Ntentan::$debug = true;
    }
}