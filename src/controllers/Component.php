<?php
/**
 * The Component base class for the Ntentan framework
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

namespace ntentan\controllers;

use ntentan\Controller;
use \ReflectionMethod;

/**
 * The base class for all Componets. Components are little plugins which are
 * written to extend the functionality of Controllers. Components basically
 * provide extra pre defined action methods which extend the capability of any
 * Controller into which it is loaded. Components can set variables in their
 * parent controller through which they can directly interract with the views
 * and layouts.
 *
 * Since components are subclasses of controllers, they have access to all the
 * utility methods which are available to controllers.
 *
 * @author James Ekow Abaka Ainooson
 */
class Component extends Controller
{
    /**
     * The name of the controller this instance of the comonent is attached to.
     * @var string
     */
    protected $controllerName;

    /**
     * An instance of the controller this instance of the component is attached
     * to.
     * @var Controller
     */
    protected $controller;

    /**
     * Dummy constructor.
     */
    public function __construct()
    {

    }
    
    public function __get($property)
    {
        return $this->controller->__get($property);
    }
    
    protected function getRawMethod()
    {
        return $this->controller->rawMethod;
    }
    

    /**
     * Sets the controller instance of this component.
     * 
     * @param Controller $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Sets the controller name of this component.
     *
     * @param string $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * Sets the controller route of this component.
     * 
     * @param $controllerRoute
     */
    public function setControllerRoute($controllerRoute)
    {
        $this->route = $controllerRoute;
    }

    /**
     * (non-PHPdoc)
     * @see controllers/ntentan\controllers.Controller::set()
     */
    public function set($params1, $params2 = null)
    {
        $this->controller->set($params1, $params2);
    }
    
    public function setIfNotSet($params1, $params2 = null)
    {
        $this->controller->setIfNotSet($params1, $params2);
    }

    /**
     * (non-PHPdoc)
     * @see controllers/ntentan\controllers.Controller::get()
     */
    public function getData($variable = null)
    {
        return $this->controller->getData($variable);
    }

    /**
     * Calls a method from the controller to which this component is attached.
     */
    protected function callControllerMethod()
    {
        $arguments = func_get_args();
        $method = array_shift($arguments);
        if(method_exists($this->controller, $method))
        {
            $reflectionMethod = new ReflectionMethod($this->controller, $method);
            $ret = $reflectionMethod->invokeArgs($this->controller, $arguments);
        }
        return $ret;
    }

    /**
     * Execute a callback method
     */
    protected function executeCallbackMethod()
    {
        $arguments = func_get_args();
        $method = array_shift($arguments);
        if(method_exists($this->controller, $method))
        {
            $reflectionMethod = new ReflectionMethod($this->controller, $method);
            $reflectionMethod->invokeArgs($this->controller, $arguments);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function hasMethod($method = null)
    {
        $ret = false;
        $path = $method === null ? $this->method : $method;
        if(method_exists($this, $path))
        {
            $ret = true;
        }
        return $ret;
    }
    
    public function getView()
    {
        return $this->controller->getView();
    }
    
    protected function getViewInstance()
    {
        return $this->controller->getViewInstance();
    }
    
    protected function setViewInstance($viewInstance)
    {
        $this->controller->setViewInstance($viewInstance);
    }
    
    protected function getComponentInstance($component = false)
    {
    	return $this->controller->getComponentInstance($component);
    }    
}
