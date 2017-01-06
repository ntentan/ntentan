<?php

namespace ntentan\loaders;

use ntentan\panie\InjectionContainer;
use ntentan\interfaces\ControllerClassResolverInterface;
use ntentan\interfaces\ResourceLoaderInterface;

class ControllerLoader implements ResourceLoaderInterface
{
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
            Ntentan::getRouter()->setVar('controller_path', $controller);
            $controllerInstance = InjectionContainer::resolve($controllerClassName);
        } else if(class_exists($controller)) {
            // use controller class
            $controllerInstance = InjectionContainer::resolve($controller);
        } else {
            return ['success' => false, 'message' => "Failed to load class [$controllerClassName]"];
        }
        $controllerInstance->executeControllerAction($action, $params);            
        return ['success' => true];
    }
}
