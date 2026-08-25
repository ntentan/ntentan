<?php

namespace ntentan\tests\cases;

use InvalidArgumentException;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testParseUri()
    {
        $uri = new Uri('https://user:pass@example.com:8080/path/to/resource?foo=bar#section1');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('/path/to/resource', $uri->getPath());
        $this->assertSame('foo=bar', $uri->getQuery());
        $this->assertSame('section1', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/to/resource?foo=bar#section1', (string)$uri);
    }

    public function testStandardPortOmittedFromAuthority()
    {
        $httpUri = new Uri('http://example.com:80/path');
        $this->assertNull($httpUri->getPort());
        $this->assertSame('example.com', $httpUri->getAuthority());
        $this->assertSame('http://example.com/path', (string)$httpUri);

        $httpsUri = new Uri('https://example.com:443/path');
        $this->assertNull($httpsUri->getPort());
        $this->assertSame('example.com', $httpsUri->getAuthority());
        $this->assertSame('https://example.com/path', (string)$httpsUri);
    }

    public function testImmutabilityAndWithers()
    {
        $uri = new Uri('http://example.com');

        $newUri = $uri
            ->withScheme('https')
            ->withUserInfo('admin', 'secret')
            ->withHost('sub.example.org')
            ->withPort(8443)
            ->withPath('/api/v1')
            ->withQuery('sort=desc')
            ->withFragment('top');

        $this->assertNotSame($uri, $newUri);
        $this->assertSame('http://example.com', (string)$uri);
        $this->assertSame('https://admin:secret@sub.example.org:8443/api/v1?sort=desc#top', (string)$newUri);
    }

    public function testInvalidPortThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com');
        $uri->withPort(70000);
    }

    public function testInvalidSchemeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $uri = new Uri('http://example.com');
        $uri->withScheme('123invalid');
    }

    public function testPrefix()
    {
        $uri = new Uri('http://example.com/blog/post');
        $prefixed = $uri->withPrefix('/blog');
        $this->assertNotSame($uri, $prefixed);
        $this->assertSame('/blog', $prefixed->getPrefix());
    }
}
