<?php

namespace ntentan\tests\cases;

use ntentan\Application;
use ntentan\ApplicationBuilder;
use ntentan\exceptions\NtentanException;
use ntentan\middleware\Middleware;
use ntentan\tests\lib\MockCallback;
use PHPUnit\Framework\TestCase;
use ntentan\panie\Container;

class ApplicationBuilderTest extends TestCase
{

    public function testBuild()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['HTTPS'] = false;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->expectException(NtentanException::class);

        $builder = new ApplicationBuilder();
        $application = $builder->build();
        $this->assertInstanceOf(Application::class, $application);
        $application->execute();
    }

    public function testBuildWithMiddleware()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['HTTPS'] = false;
        $_SERVER['HTTP_HOST'] = 'example.com';

        $factoryMock = $this->getMockBuilder(MockCallback::class)
            ->onlyMethods(['__invoke'])
            ->getMock();
        $middlewareMock = $this->createMock(Middleware::class);
        $middlewareMock->expects($this->once())->method('configure');
        $factoryMock->expects($this->once())->method('__invoke')->willReturn($middlewareMock);

        $container = new Container();
        $container->setup([
            'middleware' => $factoryMock
        ]);

        $builder = new ApplicationBuilder($container);

        $application = $builder
            ->addMiddlewarePipeline('default',[
                ['middleware', ['args']]
            ])
            ->build();
        $application->execute();
    }
}
