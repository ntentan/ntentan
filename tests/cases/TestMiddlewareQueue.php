<?php

use ntentan\middleware\MiddlewareQueue;
use PHPUnit\Framework\TestCase;

class TestMiddlewareQueue extends TestCase
{
    public function testSingleMiddleware(): void
    {
        $middleware = new MiddlewareQueue();
    }
}