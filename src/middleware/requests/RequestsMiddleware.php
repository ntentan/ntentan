<?php

namespace ntentan\middleware\requests;

use ntentan\middleware\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestsMiddleware implements Middleware
{
    private $routes = [];

    function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {

    }

    function configure(array $configuration)
    {
        $this->routes = $configuration['routes'] ?? [];
    }
}