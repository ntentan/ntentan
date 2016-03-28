<?php

namespace ntentan;

use ntentan\utils\Input;

class Router
{
    /**
     * The routing table. An array of regular expressions and associated
     * operations. If a particular request sent in through the URL matches a
     * regular expression in the table, the associated operations are executed.
     *
     * @var array
     */
    private static $routes = [];
    
    /**
     *
     * @var type 
     */
    private static $routeOrder = [];
    
    private static $tempVariables = [];

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * @var string
     */
    private static $route;

    /**
     *
     * @var type 
     */
    private static $routerVariables = [];

    public static function loadResource($route)
    {
        self::$route = $route;
        if($route == '') {
            self::loadController([
                'controller' => self::$routes['default']['parameters']['default']['controller'], 
                'action' => self::$routes['default']['parameters']['default']['action']
            ]);
            return;
        }
        foreach(self::$routeOrder as $routeName) {
            $routeDesription = self::$routes[$routeName];
            if(self::match($route, $routeDesription)) {
                return;
            }
        }
        
        throw new exceptions\RouteNotAvailableException(
           $route == '' ? 'Default route' : $route
        );
    }
    
    private static function loadController($params = [])
    {
        $controller = $params['controller'];
        $action = $params['action'];
        $controllerClass = sprintf(
            '\%s\modules\%s\%sController', Ntentan::getNamespace(), 
            strtolower($controller), utils\Text::ucamelize("{$controller}")
        );
        if(class_exists($controllerClass)) {
            self::$routerVariables += $params;
            $controllerInstance = new $controllerClass();
            $controllerInstance->executeControllerAction($action, $params);
            return true;
        } else {
            return false;
        }
    }
    
    private static function match($route, $description)
    {
        if(preg_match("|{$description['regexp']}|i", $route, $matches)) {           
            $parameters = $description['parameters']['default'] + 
                Input::get() + Input::post();
            
            foreach($matches as $key => $value) {
                if(!is_numeric($key)) {
                    $parameters[$key] = $value;
                }
            }
            
            if(isset($parameters['controller'])) {
                return self::loadController($parameters);
            } elseif(isset($parameters['route'])) {
                self::$routerVariables += $parameters;
                self::loadResource($parameters['route']);
                return true;
            }
        }
        return false;
    }

    public static function mapRoute($name, $pattern, $parameters = [])
    {
        self::$routeOrder[] = $name;
        self::$tempVariables = [];
        
        // Get a regular expression from the pattern
        $regexp = preg_replace_callback(
            "/{(?<prefix>\*|#)?(?<name>[a-z_][a-zA_Z0-9\_]*)}/", 
            function($matches) {
                self::$tempVariables[] = $matches['name'];
                    return sprintf(
                        "(?<{$matches['name']}>[a-z0-9_.~:#[\]@!$&'()*+,;=%s]+)?", 
                        $matches['prefix'] ? "\-/_" : null
                    );
            },
            str_replace('/', '(/)?', $pattern)
        );
        foreach($parameters['default'] as $parameter => $value) {
            if(!in_array($parameter, self::$tempVariables)) self::$tempVariables[] = $parameter;
        }
        
        self::$routes[$name] = [
            'pattern' => $pattern,
            'regexp' => $regexp,
            'parameters' => $parameters,
            'variables' => self::$tempVariables
        ];
    }

    public static function getVar($var)
    {
        if (isset(self::$routerVariables[$var])) {
            return self::$routerVariables[$var];
        }
        return null;
    }
    
    public static function getRoute()
    {
        return self::$route;
    }
}
