<?php
namespace ntentan;

use ntentan\sessions\PhpSessionStore;
use ntentan\sessions\SessionStore;
use Psr\Http\Message\RequestInterface;
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