<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\Header;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function testGetters()
    {
        $filter = new Header('X-Custom', 'val1');
        $this->assertSame('X-Custom', $filter->getHeader());
        $this->assertSame('val1', $filter->getValue());
    }

    public function testGetValues()
    {
        $filter = new Header('X-Custom', 'val1');
        $this->assertSame(['X-Custom' => 'val1'], $filter->getValues());
    }

    public function testMatch()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('X-Custom', 'val1');
        $filter = new Header('X-Custom', 'val1');

        $this->assertTrue($filter->match($request));
    }

    public function testMismatch()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('X-Custom', 'val1');
        $filter = new Header('X-Custom', 'val2');

        $this->assertFalse($filter->match($request));
    }

    public function testMissingHeader()
    {
        $uri = new Uri('http://example.com');
        $request = new Request($uri);
        $filter = new Header('X-Custom', 'val1');

        $this->assertFalse($filter->match($request));
    }
}
