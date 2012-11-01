<?php
require_once "tests/lib/CachingBackendTestCase.php";

use ntentan\Ntentan;
use ntentan\caching\Cache;

class MemecacheCacheTest extends \ntentan\test_cases\CachingBackendTestCase
{
    public function setUp()
    {
        Cache::reInstantiate();
        Ntentan::$cacheMethod = 'memcache';
    }
}