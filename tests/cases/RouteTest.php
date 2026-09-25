<?php

namespace ntentan\tests\cases;

use ntentan\http\filters\Route;
use ntentan\http\Request;
use ntentan\http\Uri;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testCompileStaticRoute()
    {
        $regexp = Route::compile('/about/us');
        $this->assertIsString($regexp);
        $this->assertTrue((bool)preg_match("|^$regexp$|i", '/about/us'));
        $this->assertFalse((bool)preg_match("|^$regexp$|i", '/contact'));
    }

    public function testCompileParameterRoute()
    {
        $regexp = Route::compile('/users/{id}');
        $this->assertIsString($regexp);
        $this->assertTrue((bool)preg_match("|^$regexp$|i", '/users/42', $matches));
        $this->assertSame('42', $matches['id']);
        $this->assertFalse((bool)preg_match("|^$regexp$|i", '/posts/42'));
    }

    public function testMatchWithSubclass()
    {
        $route = new class('/items/{id}') extends Route {
            protected string $method = 'DELETE';
        };

        $deleteRequest = new Request(new Uri('http://example.com/items/55'), null, 'DELETE');
        $this->assertTrue($route->match($deleteRequest));
        $this->assertSame('55', $route->getValues()['id']);

        $getRequest = new Request(new Uri('http://example.com/items/55'), null, 'GET');
        $this->assertFalse($route->match($getRequest));

        $mismatchRequest = new Request(new Uri('http://example.com/other/55'), null, 'DELETE');
        $this->assertFalse($route->match($mismatchRequest));
    }

    public function testMultipleParameters()
    {
        $route = new class('/orgs/{org_id}/repos/{repo_id}') extends Route {
            protected string $method = 'GET';
        };

        $request = new Request(new Uri('http://example.com/orgs/my-org/repos/sample-repo'), null, 'GET');
        $this->assertTrue($route->match($request));
        $values = $route->getValues();
        $this->assertSame('my-org', $values['org_id']);
        $this->assertSame('sample-repo', $values['repo_id']);
    }

    public function testWildcardParameter()
    {
        $route = new class('/static/{*filepath}') extends Route {
            protected string $method = 'GET';
        };

        $request = new Request(new Uri('http://example.com/static/css/themes/dark.css'), null, 'GET');
        $this->assertTrue($route->match($request));
        $this->assertSame('css/themes/dark.css', $route->getValues()['filepath']);
    }
}
