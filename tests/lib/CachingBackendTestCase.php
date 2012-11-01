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
}
