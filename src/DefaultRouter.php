<?php

namespace ntentan;

use ntentan\utils\Input;
use ntentan\panie\InjectionContainer;

/**
 * Provides default routing logic that loads controllers based on URL requests 
 * passed to the framework.
 */
class DefaultRouter implements interfaces\RouterInterface
{
    /**
     * The routing table. 
     * An array of regular expressions and associated operations. If a particular 
     * request sent in through the URL matches a regular expression in the table, 
     * the associated operations are executed.
     *
     * @var array
     */
    private $routes = [];
    
    private $tempVariables = [];

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * 
     * @var string
     */
    private $route;

    /**
     * Variables exposed through getVar()
     * @var type 
     */
    private $routerVariables = [];
    
    /**
     * 
     */
    private $attemptedControllers = [];

    /**
     * Invoke the router to load a given resource.
     * @param string $route
     * @throws exceptions\RouteNotAvailableException
     */
    public function execute($route)
    {
        $this->route = explode('?', $route)[0];   
        $routeName = '';
        $parameters = $this->getRouteParameters($route, $routeName);
        if($this->loadResource($parameters, $routeName)) {
            return;
        }
        
        // Throw an exception if we're still alive
        throw new exceptions\RouteNotAvailableException(
            $this->route == '' ? 'Default route' : $this->route,
            $this->attemptedControllers
        );
    }
    
    private function getRouteParameters($route, &$routeName)
    {
        $parameters = [];
        
        // Go through predefined routes till a match is found
        foreach($this->routes as $routeName => $routeDescription) {
            $parameters = $this->match($route, $routeDescription);
            if($parameters !== false) break;
        }
        
        return $parameters;
    }
    
    private function loadResource($parameters, $routeName)
    {
        if($routeName == null) return false;
        
        foreach($this->routes[$routeName]['parameters']['default'] as $parameter => $value)
        {
            // Only set the controller on default route, if no route is presented to the router.
            if($routeName == 'default' && $this->route != '' && $parameter == 'controller') continue;
            
            if(!isset($parameters[$parameter]))
                $parameters[$parameter] = $value;
            else if($parameters[$parameter] === '')
                $parameters[$parameter] = $value;
        }
        $parameters += Input::get() + Input::post();
        if(isset($parameters['controller'])) {
            return $this->loadController($parameters);
        } 
        return false;
    }
    
    private function loadController($params = [])
    {
        $controller = $params['controller'];
        $action = isset($params['action']) ? $params['action'] : null;
        
        // Try to get the classname based on router parameters
        $controllerClassName = InjectionContainer::singleton(interfaces\ControllerClassResolverInterface::class)
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
        $this->routerVariables += $params;
        $controllerInstance->executeControllerAction($action, $params);            
        return true;
    }
    
    private function match($route, $description)
    {
        $parameters = [];
        if(preg_match("|{$description['regexp']}|i", urldecode($route), $matches)) {      
            foreach($matches as $key => $value) {
                if(!is_numeric($key)) {
                    $parameters[$key] = $this->expandParameter($key, $value);
                }
            }
            return $parameters;
        }
        return false;
    }
    
    private function expandParameter(&$key, $value)
    {
        $parts = explode('____', $key);
        if($parts[1] == 'array'){
            $key = $parts[0];
            return explode('/', $value);
        } 
        return $value;
    }

    public function mapRoute($name, $pattern, $parameters = [])
    {
        // Generate a PCRE regular expression from pattern
        $this->tempVariables = [];

        $regexp = preg_replace_callback(
            "/{(?<prefix>\*|\#)?(?<name>[a-z_][a-zA-Z0-9\_]*)}/", 
            function($matches) {
                $this->tempVariables[] = $matches['name'];
                return sprintf(
                    "(?<{$matches['name']}%s>[a-z0-9_.~:#[\]@!$&'()*+,;=%s\s]+)?", 
                    $matches['prefix'] == '#' ? '____array' : null,
                    $matches['prefix'] != '' ? "\-/_" : null
                );
            },
            str_replace('/', '(/)?', $pattern)
        );
            
        $routeDetails = [
            'pattern' => $pattern,
            'regexp' => $regexp,
            'parameters' => $parameters,
            'variables' => $this->tempVariables
        ];  
            
        $this->routes[$name] = $routeDetails;
    }

    public function getVar($var)
    {
        if (isset($this->routerVariables[$var])) {
            return $this->routerVariables[$var];
        }
        return null;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getRouteDefinition($routeName)
    {
        return $this->routes[$routeName];
    }
}
