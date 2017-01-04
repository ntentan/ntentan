<?php

namespace ntentan\loaders;

use ntentan\panie\InjectionContainer;
use ntentan\interfaces\ControllerClassResolverInterface;

class ControllerLoader {
    public function load($params) {
        $controller = $params['controller'];
        $action = isset($params['action']) ? $params['action'] : null;
        
        // Try to get the classname based on router parameters
        $controllerClassName = InjectionContainer::singleton(ControllerClassResolverInterface::class)
            ->getControllerClassName($controller);
        
        // Try to resolve the classname 
        $resolvedControllerClass = InjectionContainer::getResolvedClassName($controllerClassName);
        
        if($resolvedControllerClass) {
            // use resolved class name
            $params['controller_path'] = $controller;
            $controllerInstance = InjectionContainer::resolve($controllerClassName);
        } else if(class_exists($controller)) {
            // use controller class
            $controllerInstance = InjectionContainer::resolve($controller);
        } else {
            $this->attemptedControllers[] = $controllerClassName;
            return false;
        }
        $controllerInstance->executeControllerAction($action, $params);            
        return true;
        
    }
}
