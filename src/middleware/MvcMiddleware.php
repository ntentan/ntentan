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
    private $templates;

    public function __construct(ControllerFactoryInterface $controllerFactory, Templates $templates)
    {
        $this->controllerFactory = $controllerFactory;
        $this->templates = $templates;
    }

    public function run($route, $response)
    {
        $this->templates->prependPath('views/shared');
        $this->templates->prependPath('views/layouts');
        $parameters = $route['parameters'] + Input::get() + Input::post();
        $controller = $this->controllerFactory->createController($parameters);
        return $this->controllerFactory->executeController($controller, $parameters);        
    }    
}
