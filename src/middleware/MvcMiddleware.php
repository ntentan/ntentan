<?php

namespace ntentan\middleware;

use ntentan\interfaces\ControllerFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\Middleware;

/**
 * 
 */
class MvcMiddleware implements Middleware
{

    private ControllerFactoryInterface $controllerFactory;

    public function __construct(ControllerFactoryInterface $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        return $this->controllerFactory->create($request)->run();      
    }    
    
    public function setup(array $config): MvcMiddleware
    {
        $this->controllerFactory->setup($config['controllers']);
        return $this;
    }
}
