<?php

namespace ntentan;

use ntentan\utils\Input;
use ntentan\panie\InjectionContainer;

/**
 * Provides default routing logic that loads controllers based on URL requests 
 * passed to the framework.
 */
class Router
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
    
    private $register = [
        'controller' => loaders\ControllerLoader::class
    ];

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
        $response = $this->loadResource($parameters, $routeName);
        if($response['success']) return;
        
        // Throw an exception if we're still alive
        throw new exceptions\RouteNotAvailableException(
            $response['message'],
            $this->route == '' ? 'Default route' : $this->route
        );
    }
    
    private function getRouteParameters($route, &$routeName)
    {
        // Go through predefined routes till a match is found
        foreach($this->routes as $routeName => $routeDescription) {
            $parameters = $this->match($route, $routeDescription);
            if($parameters !== false) return $parameters;           
        }
        $routeName = 'default';
        return [];
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
        $this->routerVariables = $parameters;
        foreach($this->register as $key => $class) {
            if(isset($parameters[$key])) {
                return InjectionContainer::resolve($class)->load($parameters);
            }
        }
        /*if(isset($parameters['controller'])) {
            return $this->loadController($parameters);
        }*/ 
        return ['success' => false, 'message' => 'Failed to find a suitable loader for this route'];
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
        if(!isset($parts[1])) return $value;
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
    
    public function registerLoader($tag, $class, $direction = 'append')
    {
        $this->register[$tag] = $class;
    }
}
