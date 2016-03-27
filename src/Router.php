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

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * @var string
     */
    private static $route;

    private static $vars = [];


    public static function loadResource($path)
    {
        foreach(self::$routeOrder as $routeName) {
            $route = self::$routes[$routeName];
            if(self::match($path, $route['pattern'], $matches)) {
                
            }
        }
    }
    
    
    
    private static function match($path, $pattern, &$matches)
    {
        $segments = explode('/', $path);
        $patterns = explode('/', $pattern);
        for($i = 0; $i < count($segments); $i++) {
            
        }
    }
    
    private static function getRegexp($pattern)
    {
        preg_replace_callback(
            "/\{.*\}/", 
            function($segment){
                var_dump($segment);
            }, 
            $pattern
        );
    }

    public static function setRoute($name, $pattern, $parameters = [])
    {
        self::$routeOrder[] = $name;
        self::$routes[$name] = [
            'pattern' => $pattern,
            'resource' => $resource,
            'parameters' => $parameters
        ];
    }

    public static function getVar($var)
    {
        if (isset(self::$vars[$var])) {
            return self::$vars[$var];
        }
        return null;
    }
}
