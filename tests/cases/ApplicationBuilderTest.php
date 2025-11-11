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
    public function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['HTTPS'] = false;
        $_SERVER['HTTP_HOST'] = 'example.com';
    }
    public function testBuild()
    {
        $this->expectException(NtentanException::class);
        $builder = Application::builder();
        $application = $builder->build();
        $application->execute();
    }

    private function createMockBuilder(bool $called = false)
    {
        $factoryMock = $this->getMockBuilder(MockCallback::class)
            ->onlyMethods(['__invoke'])
            ->getMock();
        $middlewareMock = $this->createMock(Middleware::class);

        if ($called) {
            $middlewareMock->expects($this->once())->method('configure');
            $factoryMock->expects($this->once())->method('__invoke')->willReturn($middlewareMock);
        } else {
            $factoryMock->expects($this->never())->method('__invoke');
        }
        return $factoryMock;
    }

    public function testBuildWithMultiMiddleware()
    {
        $firstFactoryMock = $this->createMockBuilder(true);
        $secondFactoryMock = $this->createMockBuilder();
        $container = new Container();
        $container->setup([
            'first' => $firstFactoryMock,
            'second' => $secondFactoryMock,
        ]);
        $builder = new ApplicationBuilder($container);
        $application = $builder
            ->addMiddlewarePipeline('default', [['first', ['args']]])
            ->addMiddlewarePipeline('another', [['second', ['args']]])
            ->build();

        $application->execute();
    }

    public function testBuildWithMiddleware()
    {
        $factoryMock = $this->createMockBuilder(true);
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
