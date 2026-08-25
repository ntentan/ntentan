<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\Header;
use ntentan\http\filters\Method;
use ntentan\http\filters\MimeHeader;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testMethodFilter()
    {
        $uri = new Uri('http://example.com');
        $postRequest = new Request($uri, null, 'POST');
        $getRequest = new Request($uri, null, 'GET');

        $filter = new Method('POST');
        $this->assertSame('POST', $filter->getType());
        $this->assertTrue($filter->match($postRequest));
        $this->assertFalse($filter->match($getRequest));
    }

    public function testHeaderFilter()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('X-Custom', 'val1');

        $filter = new Header('X-Custom', 'val1');
        $this->assertSame('X-Custom', $filter->getHeader());
        $this->assertSame('val1', $filter->getValue());
        $this->assertTrue($filter->match($request));

        $filterMismatch = new Header('X-Custom', 'val2');
        $this->assertFalse($filterMismatch->match($request));
    }

    public function testMimeHeaderFilter()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('Content-Type', 'application/json; charset=utf-8');

        $filter = new MimeHeader('Content-Type', 'application/json');
        $this->assertTrue($filter->match($request));

        $filterMismatch = new MimeHeader('Content-Type', 'text/html');
        $this->assertFalse($filterMismatch->match($request));
    }
}
