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
    
    use panie\ComponentContainerTrait;

    /**
     * The instance of the view template which is going to be used to render
     * the output of this controller.
     * @var View
     */
    private $view;
    
    private $name;
    
    
    private $boundParameters = [];

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
    public function addComponent($component, $params = null)
    {
        $componentInstance = $this->getComponentInstance($component);
        $componentInstance->setController($this);
        $componentInstance->init();
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
    
    private function bindParameter(&$invokeParameters, $methodParameter, $params)
    {        
        if(isset($params[$methodParameter->name])) {
            $invokeParameters[] = $params[$methodParameter->name];
            $this->boundParameters[$methodParameter->name] = true;
        } else {
            $type = $methodParameter->getClass();        
            if($type !== null) {
                $instance = $type->newInstance();
                $binder = controllers\ModelBinders::get($type->getName());
                $invokeParameters[] = $binder->bind($instance);
                $this->boundParameters[$methodParameter->name] = $binder->getBound();
            } else {
                $invokeParameters[] = null;
            }
        }        
    }
    
    protected function isBound($parameter)
    {
        return $this->boundParameters[$parameter];
    }
    
    private function parseDocComment($comment)
    {
        $lines = explode("\n", $comment);
        $attributes = [];
        foreach($lines as $line) {
            if(preg_match("/@ntentan\.(?<attribute>[a-z]+)\s+(?<value>[a-zA-Z0-9]+)/", $line, $matches)) {
                $attributes[$matches['attribute']] = $matches['value'];
            }
        }
        return $attributes;
    }
    
    private function getMethod($path)
    {
        $methods = kaikai\Cache::read(
            "controller.{$this->name}.methods", 
            function() {
                $class = new ReflectionClass($this);
                $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
                $results = [];
                foreach($methods as $method) {
                    if($method->class != $class->getName()) continue;
                    $docComments = $this->parseDocComment($method->getDocComment());
                    $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['verb'] : $method->getName();
                    $results[$keyName] = [
                        'name' => $method->getName()
                    ];
                }
                return $results;
            }
        );
        
        if(isset($methods[$path . utils\Input::server('REQUEST_METHOD')])) {
            $methodName = $methods[$path . utils\Input::server('REQUEST_METHOD')]['name'];
        } else if(isset($methods[$path])) {
            $methodName = $path;
        }
        
        if(isset($methodName))
            return new \ReflectionMethod($this, $methodName);
        else
            return false;
    }

    public function executeControllerAction($action, $params)
    {
        $view = $this->getView();
        $this->name = strtolower(substr((new ReflectionClass($this))->getShortName(), 0, -10));
        $path = Text::camelize($action === null ? $this->defaultMethod : $action);
        $return = null;
        $invokeParameters = [];
        
        
        if ($method = $this->getMethod($path)) {
            honam\TemplateEngine::prependPath("views/{$this->name}");
            if ($view->getTemplate() == null) {
                $view->setTemplate(
                    "{$this->name}_{$action}"
                    . '.tpl.php'
                );
            }
            
            $methodParameters = $method->getParameters();
            foreach($methodParameters as $methodParameter)
            {
                $this->bindParameter($invokeParameters, $methodParameter, $params);
            }
            
            $method->invokeArgs($this, $invokeParameters);
            $return = $view->out();
            echo $return;
            return;
        } else {
            foreach ($this->loadedComponents as $component) {
                //@todo Look at how to prevent this from running several times
                if ($component->hasMethod($path)) {
                    $component->variables = $this->variables;
                    $component->executeControllerAction($path, $params);
                    return;
                }
            }
        }
        throw new exceptions\RouteNotAvailableException;
    }

    /**
     * Get an instance of the View class.
     * 
     * @return \ntentan\View
     */
    protected function getView()
    {
        if($this->view == null) {
            $this->view = new View();
        }
        return $this->view;
    }

    protected function setViewInstance($viewInstance)
    {
        $this->viewInstance = $viewInstance;
    }
}
