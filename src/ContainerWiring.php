<?php
namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\http\Request;
use ntentan\http\Response;
use ntentan\http\Uri;
use ntentan\panie\Container;
use Psr\Http\Message\UriInterface;

/**
 * 
 */
class ContainerWiring
{
    private static ?Request $request = null;
    
    public static function requestFactory(Container $container): ServerRequestInterface
    {
        if(self::$request===null) {
            self::$request = new Request($container->get(UriInterface::class));
        }
        return self::$request;
    }
    
    public static function get(string $namespace): array 
    {
        return [
            ServerRequestInterface::class => [self::requestFactory(...), 'singleton' => true],
            ServerRequestInterface::class => [self::requestFactory(...), 'singleton' => true],
            UriInterface::class => [
                fn() => new Uri(
                    ($_SERVER['HTTPS'] ?? '' != 'on' ? 'https' : 'http')
                    . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"
                ),
                'singleton' => true
            ],
            ResponseInterface::class => [
                fn() => new Response(),
                'singleton' => true
            ],
            Context::class => [
                fn() => new Context($namespace, []),
                'singleton' => true
            ]
        ];
   }
}