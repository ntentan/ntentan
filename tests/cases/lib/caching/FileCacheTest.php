<?php
require_once "tests/lib/CachingBackendTestCase.php";

use ntentan\Ntentan;
use ntentan\caching\Cache;

class FileCacheTest extends \ntentan\test_cases\CachingBackendTestCase
{
    public function setUp()
    {
        Cache::reInstantiate();
        Ntentan::$cacheMethod = 'file';
        Cache::setCachePath('tests/tmp/');
    }
    
    
    public function testWrongPath()
    {
        $this->setExpectedException(
            'PHPUnit_Framework_Error', 
            'The file cache directory *i_dont_think_this_dir_would_exist* was not found or is not writable!'
        );
        Cache::setCachePath('i_dont_think_this_dir_would_exist');
        Cache::add('test', 'should fail');
    }
}