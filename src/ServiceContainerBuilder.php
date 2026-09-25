<?php
namespace ntentan;

use ntentan\panie\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\http\Request;
use ntentan\http\Response;
use ntentan\http\Uri;
use Psr\Http\Message\UriInterface;


class ServiceContainerBuilder
{
    private Container $container;
    private Context $context;

    private array $bindings = [];
    
    public function __construct(string $home, Context $context)
    {
        $this->container = new Container();
        $this->container->provide("string", "home")->with(fn() => $home);
        $this->context = $context;
    }

    public function addBindings(array $bindings): void
    {
        $this->bindings = [...$this->bindings, ...$bindings];
    }

    private function getBindings(UriInterface $uri, RequestInterface $request, ResponseInterface $response): array
    {
        return array_merge([
            Context::class => [fn() => $this->context, 'singleton' => true],
            Request::class => fn() => $request instanceof Request ? $request : null,
            Response::class => fn() => $response instanceof Response ? $response : null,
            Uri::class => fn() => $uri instanceof Uri ? $uri : null,
            UriInterface::class => fn() => $uri,
            ServerRequestInterface::class => fn() => $request,
            RequestInterface::class => fn() => $request,
            ResponseInterface::class => fn() => $response
        ], $this->bindings);
    }

    public static function initialize(array ...$bindings): array
    {
        return [ServiceContainerBuilder::class => [
            function($container) use ($bindings) {
                $home = $container->get("\$home:string");
                $context = $container->get(Context::class);
                $containerBuilder = new self($home, $context);
                foreach($bindings as $binding) {
                    $containerBuilder->addBindings($binding);
                }
                return $containerBuilder;
            },
            'singleton' => true
        ]];
    }

    public function getContainer(ServerRequestInterface $request, ResponseInterface $response): Container
    {
        $this->container->setup($this->getBindings($request->getUri(), $request, $response));
        return $this->container;
    }
}
