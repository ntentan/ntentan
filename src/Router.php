<?php

namespace ntentan;

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

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * 
     * @var string
     */
    private $route;

    /**
     * Invoke the router to load a given resource.
     * @param string $route
     * @throws exceptions\RouteNotAvailableException
     */
    public function route($route)
    {
        $this->route = explode('?', $route)[0];
        $routeName = '';
        $parameters = [];

        // Go through predefined routes till a match is found
        foreach ($this->routes as $routeName => $routeDescription) {
            $parameters = $this->match($route, $routeDescription);
            if ($parameters !== false) {
                return [
                    'route' => $this->route,
                    'parameters' => $parameters,
                    'description' => $this->routes[$routeName]
                ];
            }
        }
        return [
            'route' => $this->route,
            'parameters' => $parameters,
            'description' => $this->routes['default']
        ];
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
        if (!isset($parts[1]))
            return $value;
        if ($parts[1] == 'array') {
            $key = $parts[0];
            return explode('/', $value);
        }
        return $value;
    }

    public function mapRoute($name, $pattern, $parameters = [])
    {
        // Generate a PCRE regular expression from pattern
        $variables;
        $regexp = preg_replace_callback(
                "/{(?<prefix>\*|\#)?(?<name>[a-z_][a-zA-Z0-9\_]*)}/", function($matches) use (&$variables) {
            $variables[] = $matches['name'];
            return sprintf(
                    "(?<{$matches['name']}%s>[a-z0-9_.~:#[\]@!$&'()*+,;=%s\s]+)?", $matches['prefix'] == '#' ? '____array' : null, $matches['prefix'] != '' ? "\-/_" : null
            );
        }, str_replace('/', '(/)?', $pattern)
        );

        $routeDetails = [
            'name' => $name,
            'pattern' => $pattern,
            'regexp' => $regexp,
            'parameters' => $parameters,
            'variables' => $variables
        ];

        $this->routes[$name] = $routeDetails;
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
