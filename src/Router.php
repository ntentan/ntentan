<?php

namespace ntentan;

use ntentan\utils\Input;

class Router
{

    /**
     * The route which was requested through the URL. In cases where the route
     * is altered by the routing engine, this route still remains the same as
     * what was requested through the URL. The altered route can always be found
     * in the Ntentan::$route property.
     * @var string
     */
    private static $requestedRoute;

    /**
     * The routing table. An array of regular expressions and associated
     * operations. If a particular request sent in through the URL matches a
     * regular expression in the table, the associated operations are executed.
     *
     * @var array
     */
    private static $routes = array();

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * @var string
     */
    private static $route;
    private static $defaultRoute = 'home';
    private static $destinationType;
    private static $vars = [];

    public static function route()
    {
        self::$requestedRoute = Input::get('q');
        self::$route = self::$requestedRoute;
        self::$destinationType = 'ROUTE';

        foreach (self::$routes as $route) {
            if (self::reRoute($route)) {
                break;
            }
        }

        self::loadController();
    }

    private static function rewriteRoute($route, $matches, &$parts)
    {
        foreach ($matches as $key => $value) {
            $route = str_replace("::$key", $value, $route);
            $parts["::$key"] = $value;
        }
        return $route;
    }

    private static function setGlobals($route, $parts)
    {
        foreach ($route["globals"] as $key => $value) {
            self::$vars[$key] = str_replace(array_keys($parts), $parts, $value);
        }
    }

    private static function reRoute($route)
    {
        if (preg_match($route["pattern"], self::$requestedRoute, $matches)) {
            $parts = array();
            if (isset($route["route"])) {
                self::$route = self::rewriteRoute($route['route'], $matches, $parts);
            }
            if(self::$route == '' && isset($route['default'])) {
                self::$route = $route['default'];
            }
            if (isset($route["globals"])) {
                self::setGlobals($route, $parts);
            }
            return true;
        }
        return false;
    }

    private static function loadController()
    {
        if(self::$route == '')
        {
            self::$route = self::$defaultRoute;
        }
        Controller::load(self::$route);
    }

    public static function setDefaultRoute($defaultRoute)
    {
        self::$defaultRoute = $defaultRoute;
    }

    public static function getRoute()
    {
        return self::$route;
    }

    public static function getRequestedRoute()
    {
        return self::$requestedRoute;
    }

    public static function setRoutes($routes)
    {
        self::$routes = $routes;
    }

    public static function getVar($var)
    {
        if (isset(self::$vars[$var])) {
            return self::$vars[$var];
        }
        return null;
    }

}
