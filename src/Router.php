<?php

namespace ntentan;

use ntentan\exceptions\RouteExistsException;

/**
 * Provides default routing logic that loads controllers based on URLs passed to the framework.
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

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     *
     * @var string
     */
    private $route;

    /**
     * Names of all routes added to the routing table.
     * @var array
     */
    private $routeOrder = [];

    /**
     * Invoke the router to load a route.
     *
     * @param string $route
     * @param string $prefix
     * @return array
     */
    public function route($route, $prefix = null)
    {
        $this->route = substr($route, strlen($prefix));

        // Go through predefined routes till a match is found
        foreach ($this->routeOrder as $routeName) {
            $routeDescription = $this->routes[$routeName];
            $parameters = $this->match($this->route, $routeDescription);
            if ($parameters !== false) {
                return [
                    'route' => $this->route,
                    'parameters' => $this->fillInDefaultParameters($routeDescription, $parameters),
                    'description' => $routeDescription
                ];
            }
        }
        return [
            'route' => $this->route,
            'parameters' => [],
            'description' => $this->routes['main']
        ];
    }

    private function fillInDefaultParameters($routeDescription, $parameters)
    {
        foreach ($routeDescription['parameters']['default'] ?? [] as $parameter => $value) {
            if (!isset($parameters[$parameter])) {
                $parameters[$parameter] = $value;
            }
        }
        return $parameters;
    }

    private function match($route, $description)
    {
        $parameters = [];
        if (preg_match("|^{$description['regexp']}$|i", urldecode($route), $matches)) {
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
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
        if (!isset($parts[1])) {
            return $value;
        }
        if ($parts[1] == 'array') {
            $key = $parts[0];
            return explode('/', $value);
        }
        return $value;
    }

    private function createRoute($name, $pattern, $parameters)
    {
        $variables = null;
        if(isset($this->routes[$name])) {
            throw new RouteExistsException("A route named '$name' already exists");
        }
        // Generate a PCRE regular expression from pattern
        $regexp = preg_replace_callback(
            "/{(?<prefix>\*|\#)?(?<name>[a-z_][a-zA-Z0-9\_]*)}/", function ($matches) use (&$variables) {
            $variables[] = $matches['name'];
            return sprintf(
                "(?<{$matches['name']}%s>[a-z0-9_.~:#[\]@!$&'()*+,;=%s\s]+)?",
                $matches['prefix'] == '#' ? '____array' : null, $matches['prefix'] != '' ? "\-/_" : null
            );
        }, str_replace('/', '(/)?', $pattern)
        );

        $this->routes[$name] = [
            'name' => $name,
            'pattern' => $pattern,
            'regexp' => $regexp,
            'parameters' => $parameters,
            'variables' => $variables
        ];
    }

    public function appendRoute($name, $pattern, $parameters = [])
    {
        $this->createRoute($name, $pattern, $parameters);
        $this->routeOrder[] = $name;
    }

    public function prependRoute($name, $pattern, $parameters = [])
    {
        $this->createRoute($name, $pattern, $parameters);
        array_unshift($this->routeOrder, $name);
    }

    /**
     * Get the details for a particular route.
     * Details can be returned by reference so modifications can be made before the router runs.
     *
     * @param $name
     * @return mixed
     */
    public function &getRoute($name)
    {
        return $this->routes[$name];
    }

    public function setRoutes($routes)
    {
        foreach($routes as $route) {
            $this->createRoute($route['name'], $route['pattern'], $route['parameters']);
            $this->routeOrder[] = $route['name'];
        }

    }
}
