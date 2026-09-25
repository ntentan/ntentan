<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\Get;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase
{
    public function testMatch()
    {
        $uri = new Uri('http://example.com/users/42');
        $request = new Request($uri, null, 'GET');

        $filter = new Get('/users/{id}');
        $this->assertTrue($filter->match($request));
        $this->assertSame('42', $filter->getValues()['id']);
    }

    public function testMethodMismatch()
    {
        $uri = new Uri('http://example.com/users/42');
        $postRequest = new Request($uri, null, 'POST');

        $filter = new Get('/users/{id}');
        $this->assertFalse($filter->match($postRequest));
    }

    public function testPathMismatch()
    {
        $uri = new Uri('http://example.com/posts/42');
        $request = new Request($uri, null, 'GET');

        $filter = new Get('/users/{id}');
        $this->assertFalse($filter->match($request));
    }

    public function testMultipleParameters()
    {
        $uri = new Uri('http://example.com/categories/books/items/123');
        $request = new Request($uri, null, 'GET');

        $filter = new Get('/categories/{category}/items/{item_id}');
        $this->assertTrue($filter->match($request));
        $values = $filter->getValues();
        $this->assertSame('books', $values['category']);
        $this->assertSame('123', $values['item_id']);
    }

    public function testWildcardParameter()
    {
        $uri = new Uri('http://example.com/files/docs/api/v1/readme.txt');
        $request = new Request($uri, null, 'GET');

        $filter = new Get('/files/{*path}');
        $this->assertTrue($filter->match($request));
        $this->assertSame('docs/api/v1/readme.txt', $filter->getValues()['path']);
    }
}
