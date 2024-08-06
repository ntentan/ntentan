<?php
namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\http\Request;
use ntentan\http\Response;
use ntentan\http\Uri;
use ntentan\panie\Container;
use Psr\Http\Message\UriInterface;
use ntentan\middleware\auth\AuthMethodFactory;
use ntentan\middleware\auth\HttpRequestAuthMethod;
use ntentan\middleware\auth\HttpBasicAuthMethod;
use ntentan\kaikai\CacheBackendInterface;
use ntentan\kaikai\backends\VolatileCache;

/**
 * Holds the default DI configuration for the ntentan core.
 */
class Configuration
{
    private static ?Request $request = null;
    
    private static function requestFactory(Container $container): ServerRequestInterface
    {
        if(self::$request===null) {
            self::$request = new Request($container->get(UriInterface::class), new http\Stream("php://input", 'r'));
        }
        return self::$request;
    }
    
    public static function for(string $namespace): array 
    {
        return [
            ServerRequestInterface::class => [self::requestFactory(...), 'singleton' => true],
            Request::class => [self::requestFactory(...), 'singleton' => true],
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
            AuthMethodFactory::class => [
                function($container) {
                    $instance = new AuthMethodFactory();
                    $instance->registerAuthMethod('http_request', fn() => $container->get(HttpRequestAuthMethod::class));
                    $instance->registerAuthMethod('basin_auth', fn() => $container->get(HttpBasicAuthMethod::class));
                    return $instance;
                },
                'singlton' => true
            ],
            CacheBackendInterface::class => [
                function($container) {
                    $config = $container->get('$ntentanConfig:array');
                    if (isset($config['cache'])) {
                        $backend = sprintf('\ntentan\kaikai\backends\%sCache', ucfirst($config['cache']['backend']));
                        return $container->get($backend);
                    } else {
                        return new VolatileCache();
                    }
                }
            ]
        ];
    }
    
    public static function get(): callable
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