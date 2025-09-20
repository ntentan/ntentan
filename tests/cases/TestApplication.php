<?php

use PHPUnit\Framework\TestCase;
use ntentan\middleware\MiddlewareQueue;

class TestApplication extends TestCase
{
    public function testSingleItemQueue()
    {
        $middlewareQueue = new MiddlewareQueue();

    }
}