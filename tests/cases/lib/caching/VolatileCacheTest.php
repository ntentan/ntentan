<?php
require_once "tests/lib/CachingBackendTestCase.php";

use ntentan\Ntentan;
use ntentan\caching\Cache;

class VolatileCacheTest extends \ntentan\test_cases\CachingBackendTestCase
{
    public function setUp()
    {
        Cache::reInstantiate();
        Ntentan::$cacheMethod = 'volatile';
    }
    
    
    public function testMethodNotFound()
    {
        $this->setExpectedException(
            'ntentan\exceptions\MethodNotFoundException'
        );
        Cache::someMethodBi();
    }
}