<?php
use ntentan\Ntentan;
use ntentan\caching\Cache;

class MemecacheCacheTest extends \ntentan\test_cases\CachingTestCase
{
    public function setUp()
    {
        Cache::reInstantiate();
        Ntentan::$cacheMethod = 'memcache';
    }
}