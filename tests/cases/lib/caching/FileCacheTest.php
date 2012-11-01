<?php
require "tests/lib/CachingBackendTestCase.php";

use ntentan\Ntentan;
use ntentan\caching\Cache;

class FileCacheTest extends \ntentan\test_cases\CachingBackendTestCase
{
    public function setUp()
    {
        Ntentan::$cacheMethod = 'file';
        Cache::setCachePath('tests/tmp/');
    }
}