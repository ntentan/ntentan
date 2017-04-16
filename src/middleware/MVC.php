<?php

namespace ntentan\middleware;

class MVC extends \ntentan\Middleware {
    
    public function run($route, $response) {
        
    }
    
    private function loadResource($parameters, $routeName) {
        if ($routeName == null)
            return false;

        foreach ($this->routes[$routeName]['parameters']['default'] as $parameter => $value) {
            // Only set the controller on default route, if no route is presented to the router.
            if ($routeName == 'default' && $this->route != '' && $parameter == 'controller')
                continue;

            if (!isset($parameters[$parameter]))
                $parameters[$parameter] = $value;
            else if ($parameters[$parameter] === '')
                $parameters[$parameter] = $value;
        }
        $parameters += Input::get() + Input::post();
        $this->routerVariables = $parameters;
        foreach ($this->register as $key => $class) {
            if (isset($parameters[$key])) {
                return InjectionContainer::resolve($class)->load($parameters);
            }
        }
        /* if(isset($parameters['controller'])) {
          return $this->loadController($parameters);
          } */
        /*return ['success' => false, 'message' => 'Failed to find a suitable loader for this route'];
    }    

}