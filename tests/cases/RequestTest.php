<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\Request;
use ntentan\http\StringStream;
use ntentan\http\UploadedFile;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class RequestTest extends TestCase
{
    public function testMethod()
    {
        $uri = new Uri('http://example.com/test');
        $request = new Request($uri, null, 'POST');
        $this->assertSame('POST', $request->getMethod());

        $newRequest = $request->withMethod('PUT');
        $this->assertNotSame($request, $newRequest);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('PUT', $newRequest->getMethod());
    }

    public function testInvalidMethodThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com/test');
        $request = new Request($uri);
        $request->withMethod('INVALID METHOD WITH SPACES');
    }

    public function testEmptyMethodThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com/test');
        $request = new Request($uri);
        $request->withMethod('');
    }

    public function testRequestTargetDefault()
    {
        $uri = new Uri('http://example.com/foo/bar?baz=qux');
        $request = new Request($uri);
        $this->assertSame('/foo/bar?baz=qux', $request->getRequestTarget());

        $uriRoot = new Uri('http://example.com');
        $requestRoot = new Request($uriRoot);
        $this->assertSame('/', $requestRoot->getRequestTarget());
    }

    public function testWithRequestTarget()
    {
        $uri = new Uri('http://example.com/foo/bar');
        $request = new Request($uri);
        $newRequest = $request->withRequestTarget('*');

        $this->assertNotSame($request, $newRequest);
        $this->assertSame('/foo/bar', $request->getRequestTarget());
        $this->assertSame('*', $newRequest->getRequestTarget());
    }

    public function testInvalidRequestTargetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com/foo/bar');
        $request = new Request($uri);
        $request->withRequestTarget('/foo bar');
    }

    public function testUriAndHostHeader()
    {
        $uri = new Uri('http://example.com/foo');
        $request = new Request($uri);
        $this->assertSame($uri, $request->getUri());
        $this->assertSame('example.com', $request->getHeaderLine('Host'));

        $newUri = new Uri('http://another.com/bar');
        $newRequest = $request->withUri($newUri);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame('another.com', $newRequest->getHeaderLine('Host'));

        // Test preserveHost = true when host header is present
        $preserveRequest = $request->withUri($newUri, true);
        $this->assertSame('example.com', $preserveRequest->getHeaderLine('Host'));

        // Test withUri with same URI instance
        $sameRequest = $request->withUri($uri);
        $this->assertSame($request, $sameRequest);
    }

    public function testServerParams()
    {
        $uri = new Uri('http://example.com');
        $serverParams = ['REMOTE_ADDR' => '127.0.0.1', 'REQUEST_METHOD' => 'GET'];
        $request = new Request($uri, null, null, $serverParams);

        $this->assertSame($serverParams, $request->getServerParams());
    }

    public function testCookieParams()
    {
        $uri = new Uri('http://example.com');
        $cookies = ['theme' => 'dark'];
        $request = new Request($uri, null, null, [], $cookies);

        $this->assertSame($cookies, $request->getCookieParams());

        $newCookies = ['theme' => 'light', 'session' => '123'];
        $newRequest = $request->withCookieParams($newCookies);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame($cookies, $request->getCookieParams());
        $this->assertSame($newCookies, $newRequest->getCookieParams());
    }

    public function testQueryParams()
    {
        $uri = new Uri('http://example.com/test?foo=bar&baz=1');
        $request = new Request($uri);

        $this->assertSame(['foo' => 'bar', 'baz' => '1'], $request->getQueryParams());

        $newQueryParams = ['page' => '2'];
        $newRequest = $request->withQueryParams($newQueryParams);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame(['foo' => 'bar', 'baz' => '1'], $request->getQueryParams());
        $this->assertSame($newQueryParams, $newRequest->getQueryParams());
    }

    public function testAttributes()
    {
        $uri = new Uri('http://example.com');
        $attributes = ['route' => 'home'];
        $request = new Request($uri, null, null, [], [], null, $attributes);

        $this->assertSame($attributes, $request->getAttributes());
        $this->assertSame('home', $request->getAttribute('route'));
        $this->assertSame('default', $request->getAttribute('non_existent', 'default'));
        $this->assertNull($request->getAttribute('non_existent'));

        $withAttr = $request->withAttribute('user_id', 42);
        $this->assertNotSame($request, $withAttr);
        $this->assertSame(42, $withAttr->getAttribute('user_id'));
        $this->assertNull($request->getAttribute('user_id'));

        $withoutAttr = $withAttr->withoutAttribute('route');
        $this->assertNotSame($withAttr, $withoutAttr);
        $this->assertNull($withoutAttr->getAttribute('route'));
        $this->assertSame('home', $withAttr->getAttribute('route'));

        // Removing non-existent attribute returns same instance
        $same = $withoutAttr->withoutAttribute('non_existent');
        $this->assertSame($withoutAttr, $same);
    }

    public function testUploadedFiles()
    {
        $uri = new Uri('http://example.com');
        $file = new UploadedFile([
            'tmp_name' => '/tmp/php123',
            'size' => 1024,
            'error' => UPLOAD_ERR_OK,
            'name' => 'test.txt',
            'type' => 'text/plain'
        ]);

        $request = new Request($uri, null, null, [], [], null, [], ['file' => $file]);
        $this->assertSame(['file' => $file], $request->getUploadedFiles());

        $file2 = new UploadedFile([
            'tmp_name' => '/tmp/php456',
            'size' => 2048,
            'error' => UPLOAD_ERR_OK,
            'name' => 'image.png',
            'type' => 'image/png'
        ]);

        $newRequest = $request->withUploadedFiles(['avatar' => $file2]);
        $this->assertNotSame($request, $newRequest);
        $this->assertSame(['avatar' => $file2], $newRequest->getUploadedFiles());
        $this->assertSame(['file' => $file], $request->getUploadedFiles());
    }

    public function testInvalidUploadedFilesThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com');
        $request = new Request($uri);
        $request->withUploadedFiles(['invalid' => 'not an uploaded file object']);
    }

    public function testParsedBody()
    {
        $uri = new Uri('http://example.com');
        $request = new Request($uri);

        $parsed = ['name' => 'John', 'email' => 'john@example.com'];
        $newRequest = $request->withParsedBody($parsed);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame($parsed, $newRequest->getParsedBody());

        $nullRequest = $newRequest->withParsedBody(null);
        $this->assertNull($nullRequest->getParsedBody());

        $obj = (object)['a' => 1];
        $objRequest = $request->withParsedBody($obj);
        $this->assertSame($obj, $objRequest->getParsedBody());
    }

    public function testInvalidParsedBodyThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com');
        $request = new Request($uri);
        $request->withParsedBody('invalid string parsed body');
    }

    public function testJsonParsedBodyAutoParsing()
    {
        $uri = new Uri('http://example.com');
        $json = json_encode(['foo' => 'bar']);
        $stream = new StringStream($json);
        $request = (new Request($uri, $stream))
            ->withHeader('Content-Type', 'application/json');

        $this->assertSame(['foo' => 'bar'], $request->getParsedBody());
    }
}
