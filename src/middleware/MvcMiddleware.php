<?php

namespace ntentan\middleware;

use ntentan\honam\TemplateFileResolver;
use ntentan\honam\Templates;
use ntentan\utils\Input;
use ntentan\Context;
use ntentan\AbstractMiddleware;
use ntentan\interfaces\ControllerFactoryInterface;

/**
 * 
 */
class MvcMiddleware extends AbstractMiddleware
{
    /**
     *
     * @var ControllerFactoryInterface
     */
    private $controllerFactory;

    public function __construct(ControllerFactoryInterface $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }

    public function run($route, $response)
    {
        $parameters = $route['parameters'] + Input::get() + Input::post();
        $controller = $this->controllerFactory->createController($parameters);
        return $this->controllerFactory->executeController($controller, $parameters);        
    }    
}
