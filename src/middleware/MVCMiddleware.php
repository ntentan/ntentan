<?php

namespace ntentan\middleware;

use ntentan\utils\Input;
use ntentan\honam\TemplateEngine;
use ntentan\honam\Helper;
use ntentan\Context;

class MVCMiddleware extends \ntentan\Middleware
{
    private $container;
    private $context;
    
    private $loaders = [
        'controller' => mvc\ControllerLoader::class
    ];
    
    public function __construct(Context $context)
    {
        $this->container = $context->getContainer();
        $this->context = $context;
    }
    
    public function run($route, $response)
    {
        TemplateEngine::prependPath('views/shared');
        TemplateEngine::prependPath('views/layouts');
        Helper::setBaseUrl($this->context->getUrl(''));
        return $this->loadResource($route);
    }
    
    private function loadResource($route)
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
        foreach ($this->loaders as $key => $class) {
            if (isset($parameters[$key])) {
                return $this->container->resolve($class)->load($parameters);
            }
        }
        return ['success' => false, 'message' => 'Failed to find a suitable loader for this route'];
    }
    
    public function registerLoader($key, $class)
    {
        $this->loaders[$key] = $class;
    }
}
