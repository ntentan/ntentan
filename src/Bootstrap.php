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
class Bootstrap
{
    private static ?Request $request = null;
    
    private static function requestFactory(Container $container): ServerRequestInterface
    {
        if(self::$request===null) {
            self::$request = new Request($container->get(UriInterface::class), new http\Stream("php://input", 'r'));
        }
        return self::$request;
    }
    
    public static function getWiring(string $namespace): array 
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
            ]
        ];
    }
    
    public static function getConfiguration(): callable
    {
        return function($container) {
            $home = $container->get('$home:string');
            $configFile = "{$home}config/main.ini";
            $config = [];
            if (is_file($configFile)) {
                $config = parse_ini_file($configFile, true);
            }
            return $config;
        };
    }
}