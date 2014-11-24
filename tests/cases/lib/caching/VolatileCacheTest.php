<?php
use ntentan\Ntentan;
use ntentan\caching\Cache;

class VolatileCacheTest extends \ntentan\test_cases\CachingTestCase
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