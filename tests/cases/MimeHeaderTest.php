<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\MimeHeader;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class MimeHeaderTest extends TestCase
{
    public function testGetValues()
    {
        $filter = new MimeHeader('Content-Type', 'application/json');
        $this->assertSame(['Content-Type' => 'application/json'], $filter->getValues());
    }

    public function testMatchExact()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('Content-Type', 'application/json');
        $filter = new MimeHeader('Content-Type', 'application/json');

        $this->assertTrue($filter->match($request));
    }

    public function testMatchWithParameters()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('Content-Type', 'application/json; charset=utf-8');
        $filter = new MimeHeader('Content-Type', 'application/json');

        $this->assertTrue($filter->match($request));
    }

    public function testMismatch()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request($uri))->withHeader('Content-Type', 'application/json; charset=utf-8');
        $filter = new MimeHeader('Content-Type', 'text/html');

        $this->assertFalse($filter->match($request));
    }

    public function testMissingHeader()
    {
        $uri = new Uri('http://example.com');
        $request = new Request($uri);
        $filter = new MimeHeader('Content-Type', 'application/json');

        $this->assertFalse($filter->match($request));
    }
}
