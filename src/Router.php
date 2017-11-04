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
     *
     * @param string $route
     * @return array
     * @throws exceptions\RouteNotAvailableException
     */
    public function route($route, $prefix = null)
    {
        $this->route = substr(explode('?', $route)[0], strlen($prefix));
        $parameters = [];

        // Go through predefined routes till a match is found
        foreach ($this->routes as $index => $routeDescription) {
            $parameters = $this->match($this->route, $routeDescription);
            if ($parameters !== false) {
                return [
                    'route' => $this->route,
                    'parameters' => $parameters,
                    'description' => $routeDescription
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
        // Generate a PCRE regular expression from pattern
        $variables = null;
        $regexp = preg_replace_callback(
            "/{(?<prefix>\*|\#)?(?<name>[a-z_][a-zA-Z0-9\_]*)}/", function ($matches) use (&$variables) {
            $variables[] = $matches['name'];
            return sprintf(
                "(?<{$matches['name']}%s>[a-z0-9_.~:#[\]@!$&'()*+,;=%s\s]+)?", $matches['prefix'] == '#' ? '____array' : null, $matches['prefix'] != '' ? "\-/_" : null
            );
        }, str_replace('/', '(/)?', $pattern)
        );

        return [
            'name' => $name,
            'pattern' => $pattern,
            'regexp' => $regexp,
            'parameters' => $parameters,
            'variables' => $variables
        ];
    }

    public function appendRoute($name, $pattern, $parameters = [])
    {
        $this->routes[] = $this->createRoute($name, $pattern, $parameters);
    }

    public function prependRoute($name, $pattern, $parameters = [])
    {
        array_unshift($this->routes, $this->createRoute($name, $pattern, $parameters));
    }
}
