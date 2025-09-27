<?php

use ntentan\middleware\Middleware;
use ntentan\middleware\MiddlewareQueue;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareQueueTest extends TestCase
{
    private function createMockMiddleware(bool $isRun = false): array
    {
        $mockMiddleware = $this->createMock(Middleware::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        if ($isRun) {
            $mockMiddleware->expects($this->once())->method('run')->willReturn($mockResponse);
        }
        $mockFactory = function () use ($mockMiddleware) {
            return $mockMiddleware;
        };
        return [$mockFactory, $mockMiddleware, $mockResponse];
    }
    public function testSingleMiddleware(): void
    {
        list($mockFactory, $mockMiddleware, $mockResponse) = $this->createMockMiddleware(true);
        $pipeline = ['first'];
        $factories = ['first' => $mockFactory];
        $middlewareQueue = new MiddlewareQueue($pipeline, $factories);
        $output = $middlewareQueue->iterate($this->createMock(ServerRequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertSame($mockResponse, $output);

    }

    public function testDoubleMiddleware(): void
    {
        list($mockFactory1, $mockMiddleware1, $mockResponse1) = $this->createMockMiddleware(true);
        list($mockFactory2, $mockMiddleware2, $mockResponse2) = $this->createMockMiddleware();

        $pipeline = ['first', 'second'];
        $factories = ['first' => $mockFactory1, 'second' => $mockFactory2];
        $middlewareQueue = new MiddlewareQueue($pipeline, $factories);
        $output = $middlewareQueue->iterate($this->createMock(ServerRequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertSame($mockResponse1, $output);
        $this->assertNotSame($mockResponse2, $output);
    }

    public function testMiddlewareHandoff(): void
    {
        list($mockFactory1, $mockMiddleware1, $mockResponse1) = $this->createMockMiddleware();
        list($mockFactory2, $mockMiddleware2, $mockResponse2) = $this->createMockMiddleware(true);
        $mockMiddleware1->method('run')->willReturnCallback(
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                return $next($request, $response);
            }
        );

        $pipeline = ['first', 'second'];
        $factories = ['first' => $mockFactory1, 'second' => $mockFactory2];
        $middlewareQueue = new MiddlewareQueue($pipeline, $factories);
        $output = $middlewareQueue->iterate($this->createMock(ServerRequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertSame($mockResponse2, $output);
    }

    public function testResultRunThrough(): void
    {
        list($mockFactory1, $mockMiddleware1, $mockResponse1) = $this->createMockMiddleware(true);
        list($mockFactory2, $mockMiddleware2, $mockResponse2) = $this->createMockMiddleware();
        $mockMiddleware2->method('run')->willReturnCallback(
            function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
                return $response;
            }
        );

        $pipeline = ['first', 'second'];
        $factories = ['first' => $mockFactory1, 'second' => $mockFactory2];
        $middlewareQueue = new MiddlewareQueue($pipeline, $factories);
        $output = $middlewareQueue->iterate($this->createMock(ServerRequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertSame($mockResponse1, $output);
    }

    public function testNextResultsRunthrough(): void
    {
        list($mockFactory1, $mockMiddleware1, $mockResponse1) = $this->createMockMiddleware();
        list($mockFactory2, $mockMiddleware2, $mockResponse2) = $this->createMockMiddleware();
        $mockMiddleware1->method('run')->willReturnCallback(
            fn (ServerRequestInterface $request, ResponseInterface $response, callable $next) => $next($request, $mockResponse1)
        );
        $mockMiddleware2->method('run')->willReturnCallback(
            fn (ServerRequestInterface $request, ResponseInterface $response, callable $next) => $next($request, $mockResponse2)
        );
        $pipeline = ['first', 'second'];
        $factories = ['first' => $mockFactory1, 'second' => $mockFactory2];
        $middlewareQueue = new MiddlewareQueue($pipeline, $factories);
        $output = $middlewareQueue->iterate($this->createMock(ServerRequestInterface::class), $this->createMock(ResponseInterface::class));
        $this->assertSame($mockResponse2, $output);
    }
}
