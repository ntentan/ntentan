<?php

namespace cases;

use ntentan\Application;
use ntentan\ApplicationBuilder;
use ntentan\exceptions\NtentanException;
use PHPUnit\Framework\TestCase;

class ApplicationBuilderTest extends TestCase
{
    private $builder;
    public function setup(): void
    {
        $this->builder = Application::builder();
    }

    public function testBuild()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['HTTPS'] = false;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->expectException(NtentanException::class);
        $application = $this->builder->build();
        $this->assertInstanceOf(Application::class, $application);
        $application->execute();
    }
}
