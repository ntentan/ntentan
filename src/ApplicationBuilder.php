<?php

namespace ntentan;

use ntentan\http\Request;
use ntentan\http\Response;
use ntentan\http\Uri;
use ntentan\middleware\MiddlewareQueue;
use ntentan\panie\Container;
use ntentan\sessions\PhpSessionStore;
use ntentan\sessions\SessionStore;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ApplicationBuilder
{
    private Container $container;
    private string $namespace = 'app';

    private Request $request;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    private function requestFactory(Container $container): ServerRequestInterface
    {
        if(!isset($this->request)) {
            $this->request = new Request($container->get(UriInterface::class), new http\Stream("php://input", 'r'));
        }
        return $this->request;
    }

    public function setMiddlewareQueue(array $middlewareQueue): self
    {
        $this->container->setup([
            self::class => [
                function($container) use ($middlewareQueue) {
                    $selectedQueue = [];
                    if (count($middlewareQueue) == 1) {
                        $selectedQueue = reset($middlewareQueue)['pipeline'];
                    } else {
                        $request = $container->get(ServerRequestInterface::class);
                        foreach($middlewareQueue as $name => $pipeline) {
                            if ($name == 'default') {
                                $selectedQueue = $pipeline['pipeline'];
                                continue;
                            }
                            if (isset($pipeline['filter']) && $pipeline['filter']($request)) {
                                $selectedQueue = $pipeline['pipeline'];
                                break;
                            }
                        }
                    }
                    $registry = [];
                    $finalQueue = [];

                    foreach($selectedQueue as $middlewareEntry) {
                        list($middlewareClass, $config) = $middlewareEntry;
                        $finalQueue[]=$middlewareClass;
                        $registry[$middlewareClass] = function() use ($container, $config, $middlewareClass) {
                            $middleware = $container->get($middlewareClass);
                            $middleware->configure($config);
                            return $middleware;
                        };
                    }

                    return new MiddlewareQueue($finalQueue, $registry);
                },
                'singleton' => true
            ]
        ]);
        return $this;
    }

    public function build(): Application
    {
        $this->container->provide("string", "namespace")->with(fn () => $this->namespace);
        $this->container->provide("string", "home")->with(fn () => __DIR__ . "/../../");
        $this->container->bind(ContainerInterface::class)->to(fn() => $this->container);
        $this->container->setup([
            ServerRequestInterface::class => [$this->requestFactory(...), 'singleton' => true],
            RequestInterface::class => [$this->requestFactory(...), 'singleton' => true],
            Request::class => [$this->requestFactory(...), 'singleton' => true],
            UriInterface::class => [
                fn() => new Uri(
                    ($_SERVER['HTTPS'] ?? '' != 'on' ? 'https' : 'http')
                    . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"
                ),
                'singleton' => true
            ],
            Uri::class => [ fn($container) => $container->get(UriInterface::class), 'singleton' => true],
            ResponseInterface::class => [
                fn() => new Response(),
                'singleton' => true
            ],
            Context::class => ['singleton' => true],
            SessionStore::class => [
                function (Container $container) {
                    $sessionHandler = null;
                    if($container->has(\SessionHandlerInterface::class)) {
                        $sessionHandler = $container->get(\SessionHandlerInterface::class);
                    }
                    return new PhpSessionStore($sessionHandler);
                },
                'singleton' => true
            ]
        ]);

        return $this->container->get(Application::class);
    }
}