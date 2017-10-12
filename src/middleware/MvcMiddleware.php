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
    
    private function extractRouteParameters($route)
    {
        $parameters = $route['parameters'];
        $routeDescription = $route['description'];
        foreach ($routeDescription['parameters']['default'] as $parameter => $value) {
            // Only set the controller on default route, if no route is presented to the router.
            if ($routeDescription['name'] == 'default' && $route['route'] != '' && $parameter == 'controller') {
                continue;
            }
            if (!isset($parameters[$parameter])) {
                $parameters[$parameter] = $value;
            } elseif ($parameters[$parameter] === '') {
                $parameters[$parameter] = $value;
            }
        }        
        $parameters += Input::get() + Input::post();
        return $parameters;
    }

    public function run($route, $response)
    {
        TemplateEngine::prependPath('views/shared');
        TemplateEngine::prependPath('views/layouts');
        Helper::setBaseUrl(Context::getInstance()->getUrl(''));
        $parameters = $this->extractRouteParameters($route);
        $controller = $this->controllerFactory->createController($parameters);
        return $this->controllerFactory->executeController($controller, $parameters);        
    }    
}
