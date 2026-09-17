<?php

namespace ntentan;

use ntentan\panie\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait ServiceContainer
{
    private Container $serviceContainer;
    private ServiceContainerBuilder $containerBuilder;

    protected function getServiceContainer(ServerRequestInterface $request, ResponseInterface $response): Container
    {
        if(!isset($this->serviceContainer)) {
            $this->serviceContainer = $this->containerBuilder->getContainer($request, $response);
        }
        return $this->serviceContainer;
    }
}
