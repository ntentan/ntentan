<?php
namespace ntentan\middleware;

use ntentan\Session;
use ntentan\Middleware;
use ntentan\middleware\auth\AuthMethodFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The Authentication middleware ensures that all requests are from properly authenticated sessions before sending
 * them down the pipeline. In cases where the request is not properly authenticated, it's passed on to an authentication
 * module for authentication.
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AuthMiddleware implements Middleware
{
    private $authenticated;
    private $authMethodFactory;
    private $config;

    public function __construct(AuthMethodFactory $authMethodFactory)
    {
        $this->authenticated = Session::get('logged_in');
        $this->authMethodFactory = $authMethodFactory;
    }

    public static function registerAuthMethod($authMethod, $class)
    {
        self::$authMethods[$authMethod] = $class;
    }
    
    public function setup(array $config): AuthMiddleware
    {
        $this->config = $config;
        return $this;
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (Session::get('logged_in')) {
            return $next($request, $response);
        }
        if (in_array($request->getUri()->getPath(), $this->config['excluded'] ?? [])) {
            return $next($request, $response);
        }

         $authResponse = $this->authMethodFactory->createAuthMethod($this->config)->login($request, $response);
        if ($response === true) {
            return $next($request, $response);
        } else {
            return $authResponse;
        }
    }
}

