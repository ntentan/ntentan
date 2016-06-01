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

    private $componentMap = [];
    
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
        $this->componentMap[Text::camelize($component, '.')] = $component;
        $componentInstance = $this->getComponentInstance($component);
        $componentInstance->setController($this);
        $componentInstance->init($params);
    }

    public function __get($property)
    {
        if (substr($property, -9) == "Component") {
            $component = substr($property, 0, strlen($property) - 9);
            return $this->getComponentInstance($this->componentMap[$component]);
        } else {
            throw new \Exception("Unknown property *{$property}* requested");
        }
    }
    
    /**
     * 
     * @param array $invokeParameters
     * @param \ReflectionParameter $methodParameter
     * @param array $params
     */
    private function bindParameter(&$invokeParameters, $methodParameter, $params)
    {        
        if(isset($params[$methodParameter->name])) {
            $invokeParameters[] = $params[$methodParameter->name];
            $this->boundParameters[$methodParameter->name] = true;
        } else {
            $type = $methodParameter->getClass();        
            if($type !== null) {
                $binder = controllers\ModelBinders::get($type->getName());
                $invokeParameters[] = $binder->bind($this, $type->getName());
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
            if(preg_match("/@ntentan\.(?<attribute>[a-z]+)\s+(?<value>.+)/", $line, $matches)) {
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
                    $methodName = $method->getName();
                    if(substr($methodName, 0, 2) == '__') continue;
                    if(array_search($methodName, ['addComponent', 'executeControllerAction', 'setComponentResolverParameters'])) continue;
                    $docComments = $this->parseDocComment($method->getDocComment());
                    $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;
                    $results[$keyName] = [
                        'name' => $method->getName(),
                        'binder' => isset($docComments['binder']) ? $docComments['binder'] : controllers\DefaultModelBinder::class
                    ];
                }
                return $results;
            }
        );
        
        if(isset($methods[$path . utils\Input::server('REQUEST_METHOD')])) {
            return $methods[$path . utils\Input::server('REQUEST_METHOD')];
        } else if(isset($methods[$path])) {
            return $methods[$path];
        }
        
        return false;
    }

    public function executeControllerAction($action, $params)
    {
        $this->name = strtolower(substr((new ReflectionClass($this))->getShortName(), 0, -10));
        $path = Text::camelize($action === null ? 'index' : $action);
        $return = null;
        $invokeParameters = [];       
        
        if ($methodDetails = $this->getMethod($path)) {
            panie\InjectionContainer::bind(controllers\ModelBinderInterface::class)
                ->to($methodDetails['binder']); 
            $method = new \ReflectionMethod($this, $methodDetails['name']);
            honam\TemplateEngine::prependPath("views/{$this->name}");
            if (View::getTemplate() == null) {
                View::setTemplate(
                    "{$this->name}_{$path}"
                    . '.tpl.php'
                );
            }
            
            $methodParameters = $method->getParameters();
            foreach($methodParameters as $methodParameter)
            {
                $this->bindParameter($invokeParameters, $methodParameter, $params);
            }
            
            $method->invokeArgs($this, $invokeParameters);
            $return = View::out();
            echo $return;
            return;
        } else {
            foreach ($this->loadedComponents as $component) {
                //@todo Look at how to prevent this from running several times
                if ($component->hasMethod($path)) {
                    $component->executeControllerAction($path, $params);
                    return;
                }
            }
        }
        throw new exceptions\ControllerActionNotFoundException($this, $path);
    }
}
