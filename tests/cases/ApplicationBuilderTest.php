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

        $reflection = new \ReflectionClass(ApplicationBuilder::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
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

        if ($called) {
            $middlewareMock = $this->createMock(Middleware::class);
            $middlewareMock->expects($this->once())->method('configure');
            $factoryMock->expects($this->once())->method('__invoke')->willReturn($middlewareMock);
        } else {
            $factoryMock->expects($this->never())->method('__invoke');
        }
        return $factoryMock;
    }

    public function testDuplicatePipelineException()
    {
        $this->expectException(NtentanException::class);
        Application::builder()
            ->addMiddlewarePipeline('default', [['first', ['args']]])
            ->addMiddlewarePipeline('default', [['second', ['args']]])
            ->build();
    }

    public function testContainer()
    {
        $builder = Application::builder();
        $this->assertInstanceOf(Container::class, $builder->getContainer());
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

    public function testMiddlewareFilters()
    {
        $firstFactoryMock = $this->createMockBuilder();
        $secondFactoryMock = $this->createMockBuilder(true);
        $container = new Container();
        $container->setup([
            'first' => $firstFactoryMock,
            'second' => $secondFactoryMock,
        ]);
        $builder = new ApplicationBuilder($container);
        $application = $builder
            ->addMiddlewarePipeline('default', [['first', ['args']]])
            ->addMiddlewarePipeline('another', [['second', ['args']]], fn() => true)
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

    public function testSingletonException()
    {
        $this->expectException(NtentanException::class);
        $this->expectExceptionMessage("ApplicationBuilder can only be instantiated once.");
        new ApplicationBuilder(new Container());
        new ApplicationBuilder(new Container());
    }
}