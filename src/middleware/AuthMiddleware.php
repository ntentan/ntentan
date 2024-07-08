<?php
namespace ntentan\middleware;

use ntentan\Session;
use ntentan\Middleware;
use ntentan\middleware\auth\AuthMethod;
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
    
    private AuthMethod $authMethod;

    private array $config;

    public function __construct(AuthMethod $authMethod, array $authConfig)
    {
        $this->authMethod = $authMethod;
        $this->authMethod->setup($authConfig);
        $this->config = $authConfig;
    }
    
    private function isExcluded(string $path, array $excludedPaths)
    {
        foreach ($excludedPaths as $excludedPath) {
            if (substr($path, 0, strlen($excludedPath)) == $excludedPath) {
                return true;
            }
        }
        return false;
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (Session::get('authenticated') || $this->isExcluded($request->getUri()->getPath(), $this->config['excluded'] ?? [])) { //in_array($request->getUri()->getPath(), $this->config['excluded'] ?? [])) {
            return $next($request, $response);
        }
        $authResponse = $this->authMethod->run($request, $response, $next);
        if ($authResponse === true) {
            return $next($request, $response);
        } else {
            return $authResponse;
        }
    }
}

