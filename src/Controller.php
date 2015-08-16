<?php

/**
 * The Controller base class for the Ntentan framework
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan;

use ntentan\caching\Cache;
use ntentan\controllers\exceptions\ComponentNotFoundException;
use \ReflectionClass;
use ntentan\Ntentan;
use ntentan\View;
use ntentan\utils\Text;

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application logic. They are stored in modules and they contain methods
 * which are called from the url. Parameters to the methods are also passed
 * through the URL. If a method is not specified, the default method is called.
 * The methods called by the controllers are expected to set data into variables
 * which are later rendered as output to the end user through views.
 *
 * @author  James Ekow Abaka Ainooson
 * @todo    Controllers must output data that can be passed to some kind of
 *          template engine like smarty.
 */
class Controller
{

    private $defaultMethod = 'run';

    /**
     * The variables generated by any method called in this controller are
     * stored in this array. The values from this array would later be used
     * with the view classes to render the views.
     * @var Array
     */
    private $variables = array();

    /**
     * An array detailing all the loaded components.
     * @var array
     */
    private $componentInstances = array();

    /**
     * The instance of the view template which is going to be used to render
     * the output of this controller.
     * @var View
     */
    private $view;
    private $method;
    private $route;

    /**
     * Adds a component to the controller. Component loading is done with the
     * following order of priority.
     *  1. Application components
     *  2. Plugin components
     *  3. Core components
     *  
     * @param string $component Name of the component
     * @todo cache the location of a component once found to prevent unessearry
     * checking
     */
    public function addComponent()
    {
        $arguments = func_get_args();
        $component = array_shift($arguments);
        $namespace = Ntentan::getNamespace();

        // Attempt to load an application component
        $namespace = "\\$namespace\\components";
        $className = $this->loadComponent($component, $arguments, $namespace);
        if (is_string($className)) {
            return;
        }

        // Attempt to load a core component
        $className = $this->loadComponent(
                $component, $arguments, '\\ntentan\\controllers\\components'
        );
        if (is_string($className)) {
            return;
        }

        // Attempt to load plugin component
        $componentPaths = explode(".", $component);
        $namespace = "\\ntentan\\extensions\\{$componentPaths[0]}\\components";
        $className = $this->loadComponent(
                $componentPaths[1], $arguments, $namespace, $componentPaths[0]
        );

        if (is_string($className)) {
            return;
        }

        throw new exceptions\ComponentNotFoundException(
        "Component not found *$component*"
        );
    }

    public function __get($property)
    {
        if (substr($property, -9) == "Component") {
            $component = substr($property, 0, strlen($property) - 9);
            return $this->getComponentInstance($component);
        } else {
            throw new \Exception("Unknown property *{$property}* requested");
        }
    }

    private function loadComponent($component, $arguments, $path, $plugin = null)
    {
        $camelizedComponent = Text::ucamelize($component);
        $componentName = "$path\\$component\\{$camelizedComponent}Component";
        if (class_exists($componentName)) {
            $key = Text::camelize($plugin . ($plugin == null ? $camelizedComponent : $camelizedComponent));

            $componentClass = new ReflectionClass($componentName);
            $componentInstance = $componentClass->newInstanceArgs($arguments);

            $this->componentInstances[$key] = $componentInstance;
            $this->componentInstances[$key]->setController($this);
            $this->componentInstances[$key]->init();

            return $componentName;
        } else {
            return false;
        }
    }

    /**
     *
     * @param mixed $params1
     * @param string $params2
     */
    protected function set($params1, $params2 = null)
    {
        if (is_string($params1)) {
            $this->variables[$params1] = $params2;
        } else if (is_array($params1)) {
            $this->variables += $params1;
        }
    }

    protected function getData($variable = null)
    {
        return $variable != null ? $this->variables[$variable] : $this->variables;
    }

    /**
     * A utility method to load a controller. This method loads the controller
     * and fetches the contents of the controller into the Controller::$contents
     * variable if the get_contents parameter is set to true on call. If a
     * controller doesn't exist in the module path, a ModelController is loaded
     * to help manipulate the contents of the model. If no model exists in that
     * location, it is asumed to be a package and a package controller is
     * loaded.
     *
     * @param $path                 The path for the model to be loaded.
     * @param $returnInstanceOnly   Fources the method to return only the instance of the controller object.
     * @return Controller
     */
    public static function load($route)
    {
        $routeArray = explode('/', $route);
        $numParts = count($routeArray);
        $namespace = Ntentan::getNamespace();
        $className = "\\$namespace\\modules\\";
        $controller = null;
        $controllerRoute = null;

        for ($i = 0; $i < $numParts; $i++) {
            $testClass = $className . $routeArray[$i] . "\\" . Text::ucamelize($routeArray[$i]) . "Controller";
            $controllerRoute .= "$routeArray[$i]/";
            $className .= array_shift($routeArray) . "\\";

            if (class_exists($testClass)) {
                $controller = new $testClass();
                $controller->route = $controllerRoute;
                $controller->view = new View();
                $controller->init();
                break;
            }
        }

        $method = array_shift($routeArray);
        $controller->runMethod($routeArray, $method);
    }

    /**
     * Returns true if this controller has the requested method and returns
     * false otherwise.
     * @param string $method
     * @return booleam
     */
    public function hasMethod($method = null)
    {
        $ret = false;
        $path = $method === null ? $this->method : $method;
        if (method_exists($this, $path)) {
            $ret = true;
        } else {
            foreach ($this->componentInstances as $component) {
                $ret = $component->hasMethod($path);
                if ($ret)
                    break;
            }
        }
        return $ret;
    }

    private function runMethod($params, $method)
    {
        $view = $this->getView();
        $path = $method === null ? $this->defaultMethod : $method;
        $return = null;
        if (method_exists($this, $path)) {
            $controllerClass = new ReflectionClass($this);
            $method = $controllerClass->GetMethod($path);
            if ($view->getTemplate() == null) {
                $view->setTemplate(
                    str_replace("/", "_", Router::getRoute())
                    . '_' . $path
                    . '.tpl.php'
                );
            }
            $method->invokeArgs($this, $params);
            $return = $view->out($this->getData());
            echo $return;
        } else {
            foreach ($this->componentInstances as $component) {
                //@todo Look at how to prevent this from running several times
                if ($component->hasMethod($path)) {
                    $component->variables = $this->variables;
                    $component->runMethod($params, $path);
                    break;
                }
            }
        }
    }

    protected function getView()
    {
        return $this->view;
    }

    protected function setViewInstance($viewInstance)
    {
        $this->viewInstance = $viewInstance;
    }

    protected function getComponentInstance($component = false)
    {
        if ($component === false) {
            return $this->componentInstances;
        } else {
            if (is_object($this->componentInstances[$component])) {
                return $this->componentInstances[$component];
            } else {
                throw new ComponentNotFoundException("Component <code><b>$component</b></code> not currently loaded.");
            }
        }
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function init()
    {
        
    }
    
    public function setDefaultMethod($defaultMethod)
    {
        $this->defaultMethod = $defaultMethod;
    }
}
