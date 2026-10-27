<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\Put;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class PutTest extends TestCase
{
    public function testMatch()
    {
        $uri = new Uri('http://example.com/items/99');
        $request = new Request($uri, null, 'PUT');

        $filter = new Put('/items/{item_id}');
        $this->assertTrue($filter->match($request));
        $this->assertSame('99', $filter->getValues()['item_id']);
    }

    public function testMethodMismatch()
    {
        $uri = new Uri('http://example.com/items/99');
        $getRequest = new Request($uri, null, 'GET');

        $filter = new Put('/items/{item_id}');
        $this->assertFalse($filter->match($getRequest));
    }

    public function testPathMismatch()
    {
        $uri = new Uri('http://example.com/other/99');
        $request = new Request($uri, null, 'PUT');

        $filter = new Put('/items/{item_id}');
        $this->assertFalse($filter->match($request));
    }

    public function testMultipleParameters()
    {
        $uri = new Uri('http://example.com/users/42/posts/101');
        $request = new Request($uri, null, 'PUT');

        $filter = new Put('/users/{user_id}/posts/{post_id}');
        $this->assertTrue($filter->match($request));
        $values = $filter->getValues();
        $this->assertSame('42', $values['user_id']);
        $this->assertSame('101', $values['post_id']);
    }

    public function testWildcardParameter()
    {
        $uri = new Uri('http://example.com/files/docs/api/v1/readme.txt');
        $request = new Request($uri, null, 'PUT');

        $filter = new Put('/files/{*path}');
        $this->assertTrue($filter->match($request));
        $this->assertSame('docs/api/v1/readme.txt', $filter->getValues()['path']);
    }
}

