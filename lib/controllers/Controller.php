<?php
/*
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\controllers;

use ntentan\caching\Cache;
use ntentan\controllers\exceptions\ComponentNotFoundException;
use \ReflectionClass;
use \ReflectionObject;
use ntentan\Ntentan;
use ntentan\views\View;
use ntentan\models\Model;

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
    /**
     * The name of the default method to execute when the controller is called
     * without any action methods specified.
     * @var string
     */
    public $defaultMethodName = "run";

    /**
     * A copy of the route that was used to load this controller.
     * @var String
     */
    public $route;

    /**
     * A short machine readable name for this controller.
     * @var string
     */
    public $name;

    /**
     * The variables generated by any method called in this controller are
     * stored in this array. The values from this array would later be used
     * with the view classes to render the views.
     * @var Array
     */
    public $variables = array();

    /**
     * An array to hold the names of all the loaded components of this instance
     * of the controller class.
     * @var type Array
     */
    public $components = array();

    /**
     * An array to hold the instances of all the loaded components of this instance
     * of the controller class. The controller names in the Controller::variables
     * property correspond directly with the instances in this array.
     * @var type Array
     */
    private $componentInstances = array();

    public $rawMethod;

    /**
     *
     */
    private static $loadedComponents = array();

    /**
     * The instance of the view template which is going to be used to render
     * the output of this controller.
     * @var View
     */
    private $viewInstance;

    /**
     * The instance of the model class which shares the same package or namespace
     * with this controller.
     * @var Model
     */
    private $modelInstance;

    /**
     * A route to the model of the default model this controller is liked to.
     * @var string
     */
    private $modelRoute;

    /**
     * Stores the data this controller holds for passing ot to the template.
     * This data is stored as an associative array in this variable. The values
     * can be manipulated through the Controller::set() method.
     * @var array
     */
    public $data;

    /**
     * The directory path to the file of this controller's class.
     * @var string
     */
    public $filePath;

    public $method;

    /**
     * Returns the name of the controller.
     * @return string
     */
    public function getName()
    {
        $object = new ReflectionObject($this);
        return $object->getName();
    }

    /**
     * Setter property
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        switch($property)
        {
        case "layout":
            $this->view->layout = $value;
            break;
        }
    }

    public function __get($property)
    {
        switch ($property)
        {
        case "view":
            $viewInstance = $this->getViewInstance();
            if($viewInstance == null)
            {
                $viewInstance = new View();
                $this->setViewInstance($viewInstance);
                $viewInstance->defaultTemplatePath = $this->filePath;
            }
            return $viewInstance;

        case "layout":
            return $this->view->layout;

        case "model":
            if($this->modelInstance == null)
            {
                $this->modelInstance = Model::load($this->modelRoute);
            }
            return $this->modelInstance;

        case "directory":
            return Ntentan::$modulesPath . $this->route . "/";

        default:
            if(substr($property, -6) == "Widget")
            {
                $widget = Ntentan::deCamelize(substr($property, 0, strlen($property) - 6));
                return $this->widgets[$widget];
            }
            else if(substr($property, -9) == "Component")
            {
                $component = substr($property, 0, strlen($property) - 9);
                return $this->getComponentInstance($component);
            }
            else
            {
                throw new \Exception("Unknown property <code><b>{$property}</b></code> requested");
            }
        }
    }

    /**
     * Adds a component to the controller.
     * @param string $component Name of the component
     * @todo cache the location of a component once found to prevent unessearry
     * checking
     */
    public function addComponent()
    {
        $arguments = func_get_args();
        $component = array_shift($arguments);
        if(!$this->loadComponent($component, $arguments, '\\ntentan\\controllers\\components'))
        {
            if(!$this->loadComponent($component, $arguments, "\\ntentan\\plugins\\components"))
            {
                if(!$this->loadComponent($component, $arguments, "\\" . Ntentan::$modulesPath . "\\components"))
                {
                    throw new exceptions\ComponentNotFoundException("Component not found <code><b>$component</b></code>");
                }
            }
        }
    }

    private function loadComponent($component, $arguments, $path)
    {
        $componentName = "$path\\$component\\" . Ntentan::camelize($component) . 'Component';
        if(file_exists(get_class_file($componentName)))
        {
            if(!isset(Controller::$loadedComponents[$component]))
            {
                $componentClass = new ReflectionClass($componentName);
                $componentInstance = $componentClass->newInstanceArgs($arguments);
                $componentInstance->filePath = Ntentan::getFilePath("lib/controllers/components/$component");
                Controller::$loadedComponents[$component] = $componentInstance;
            }
            $this->componentInstances[$component] = Controller::$loadedComponents[$component];
            $this->componentInstances[$component]->setController($this);
            $this->componentInstances[$component]->route = $this->route;
            $this->componentInstances[$component]->init();
            return true;
        }
        else
        {
            return false;
        }
    }

    public function hasWidget($widgetName)
    {
        return isset($this->widgets[$widgetName]);
    }

    /**
     *
     * @param mixed $params1
     * @param string $params2
     */
    protected function set($params1, $params2 = null)
    {
        if(is_object($params1) && \method_exists($params1, "toArray"))
        {
            $this->variables = array_merge($this->variables, $params1->toArray());
        }
        else if(is_array($params1))
        {
            $this->variables = array_merge($this->variables, $params1);
        }
        else
        {
            if(\is_object($params2) && method_exists($params2, "toArray"))
            {
                $params2 = $params2->toArray();
            }
            $this->variables[$params1] = $params2;
        }
    }

    protected function getVariable($variable)
    {
        return $this->variables[$variable];
    }

    protected function getRawMethod()
    {
        return $this->rawMethod;
    }

    /**
     * Appends a string to an already setup template variable.
     * @param string $params1
     * @param string $params2
     */
    protected function append($params1, $params2)
    {
        $this->variables[$params1] .= $params2;
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
     * @param $path 		The path for the model to be loaded.
     * @return Controller
     */
    public static function load($route)
    {
        $controllerRoute = '';
        $routeArray = explode('/', $route);

        // Loop through the filtered path and extract the controller class
        for($i = 0; $i<count($routeArray); $i++)
        {
            $p = $routeArray[$i];
            $pCamelized = Ntentan::camelize($p);
            $filePath = Ntentan::$modulesPath . "/modules/$controllerRoute/$p/";
            if(file_exists($filePath . "{$pCamelized}Controller.php"))
            {
                $controllerName = $pCamelized."Controller";
                $controllerRoute .= "/$p";
                $modelRoute .= "$p";

                if($controllerRoute[0] == "/") $controllerRoute = substr($controllerRoute,1);

                if($controllerName == "")
                {
                    Ntentan::error("Path not found! [$route]");
                }
                else
                {
                    Ntentan::addIncludePath(Ntentan::$modulesPath . "/$controllerRoute/"); //$controllerName.php";
                    $controllerNamespace = "\\" . str_replace("/", "\\", Ntentan::$modulesPath . "/modules/$controllerRoute/");
                    $controllerName = $controllerNamespace . $controllerName;
                    if(class_exists($controllerName))
                    {
                        $controller = new $controllerName();
                        foreach($controller->components as $component)
                        {
                            $controller->addComponent($component);
                        }

                        $controller->setRoute($controllerRoute);
                        $controller->setName($controllerName);
                        $controller->modelRoute = $modelRoute;
                        $controller->filePath = $filePath;
                        $controller->init();

                        // Trap for the cache
                        if($controller->view->cacheTimeout !== false && Cache::exists("view_" . Ntentan::getRouteKey()) && Ntentan::$debug === false)
                        {
                            echo Cache::get('view_' . Ntentan::$route);
                            return;
                        }

                        if($controller->method == '')
                        {
                            $controller->method = $routeArray[$i + 1] != '' ? Ntentan::camelize($routeArray[$i + 1], ".", "", true) : $controller->defaultMethodName;
                            $controller->rawMethod = $routeArray[$i + 1] != '' ? $routeArray[$i + 1]: $controller->defaultMethodName;
                        }

                        if(!$controller->hasMethod())
                        {
                            $modelRoute .= ".";
                            continue;
                        }
                    }
                    else
                    {
                        Ntentan::error("Controller class <b><code>$controllerName</code></b> not found.");
                    }

                    $controller->runMethod(array_slice($routeArray,$i+2));
                    return;
                }
            }
            else
            {
                $controllerRoute .= "/$p";
                $modelRoute .= "$p.";
            }
        }
        if(is_object($controller))
        {
            $message = "Controller method <code><b> {$routeArray[$i - 1]}()</b></code> not found for the <code><b>{$controllerName}</b></code> controller.";
        }
        else
        {
            $message = "Controller not found for route <code><b>" . Ntentan::$route . "</b></code>";
        }
        Ntentan::error($message);
    }

    /**
     * Set the name of this controller
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        foreach($this->componentInstances as $component)
        {
            $component->setControllerName($name);
        }
    }

    /**
     * Set the value of the route used to load this controller.
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
        foreach($this->componentInstances as $component)
        {
            $component->setControllerRoute($route);
        }
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
        if(method_exists($this, $path))
        {
            $ret = true;
        }
        else
        {
            foreach($this->componentInstances as $i => $component)
            {
                $ret = $component->hasMethod($path);
                if($ret)
                {
                    break;
                }
            }
        }
        return $ret;
    }

    public function setView($view)
    {
        $this->viewInstance = $view;
    }

    public function runMethod($params, $method = null)
    {
        $path = $method === null ? $this->method : $method;
        if(method_exists($this, $path))
        {
            $controllerClass = new ReflectionClass($this->getName());
            $method = $controllerClass->GetMethod($path);
            if($this->view->template == null)
            {
                $this->view->template = 
                    str_replace("/", "_", $this->route) 
                    . '_' . $this->getRawMethod() 
                    . '.tpl.php';
            }
            $method->invokeArgs($this, $params);
            $this->preRender();
            $return = $this->view->out($this->getData());
            $return = $this->postRender($return);
        }
        else
        {
            foreach($this->componentInstances as $component)
            {
                if($component->hasMethod($path))
                {
                    $component->variables = $this->variables;
                    $component->runMethod($params, $path);
                }
            }
        }
        if($this->view->cacheTimeout !== false && Ntentan::$debug !== false)
        {
            Cache::add('view_' . Ntentan::getRouteKey(), $return, $this->view->cacheTimeout);
        }
        echo $return;
    }

    public function preRender()
    {

    }

    /**
     *
     */
    public function postRender($data)
    {
        return $data;
    }

    /**
     * Checks whether this controller has a particular component loaded.
     * @param string $component
     * @return boolean
     */
    public function hasComponent($component)
    {
        if(array_search($component, array_keys($this->componentInstances)) !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function getViewInstance()
    {
        return $this->viewInstance;
    }

    protected function setViewInstance($viewInstance)
    {
        $this->viewInstance = $viewInstance;
    }

    protected function getComponentInstance($component = false)
    {
        if($component === false)
        {
            return $this->componentInstances;
        }
        else
        {
            if(is_object($this->componentInstances[$component]))
            {
                return $this->componentInstances[$component];
            }
            else
            {
                throw new ComponentNotFoundException("Component <code><b>$component</b></code> not currently loaded.");
            }
        }
    }

    /**
     * Function called automatically after the controller is initialized. This
     * method should be overriden by controllers which want to initialize
     * certain variables after the constructor function is called.
     */
    public function init()
    {

    }
}
