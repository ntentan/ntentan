<?php

namespace ntentan\middleware;

use ntentan\utils\Input;
use ntentan\honam\TemplateEngine;
use ntentan\honam\Helper;
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
        TemplateEngine::prependPath('views/shared');
        TemplateEngine::prependPath('views/layouts');
        Helper::setBaseUrl(Context::getInstance()->getUrl(''));
        $parameters = $route['parameters'] + Input::get() + Input::post();
        $controller = $this->controllerFactory->createController($parameters);
        return $this->controllerFactory->executeController($controller, $parameters);        
    }    
}
