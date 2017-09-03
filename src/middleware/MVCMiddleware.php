<?php

namespace ntentan\middleware;

use ntentan\middleware\mvc\ResourceLoaderFactory;
use ntentan\utils\Input;
use ntentan\honam\TemplateEngine;
use ntentan\honam\Helper;
use ntentan\Context;
use ntentan\AbstractMiddleware;

class MVCMiddleware extends AbstractMiddleware
{
    private $container;
    private $loaderFactory;

    public function __construct(ResourceLoaderFactory $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }

    public function run($route, $response)
    {
        TemplateEngine::prependPath('views/shared');
        TemplateEngine::prependPath('views/layouts');
        Helper::setBaseUrl(Context::getInstance()->getUrl(''));
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
        return $this->loaderFactory->createLoader($parameters);
    }
}
