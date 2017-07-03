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

use \ReflectionClass;
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
    private $componentMap = [];
    private $boundParameters = [];
    private $activeAction;
    private $context;

    public function __get($property)
    {
        if (substr($property, -9) == "Component") {
            $component = substr($property, 0, strlen($property) - 9);
            return $this->getComponentInstance($this->componentMap[$component]);
        } else {
            throw new \Exception("Unknown property *{$property}* requested");
        }
    }

    protected function getContext()
    {
        return $this->context;
    }

    protected function getRedirect()
    {
        $redirect = new Redirect($this->context->getParameter('controller_path'));
        return $redirect;
    }

    protected function getActionUrl($action)
    {
        return "{$this->context->getParameter('controller_path')}/$action";
    }

    /**
     *
     * @param array $invokeParameters
     * @param \ReflectionParameter $methodParameter
     * @param array $params
     */
    private function bindParameter(&$invokeParameters, $methodParameter, $params)
    {
        if (isset($params[$methodParameter->name])) {
            $invokeParameters[] = $params[$methodParameter->name];
            $this->boundParameters[$methodParameter->name] = true;
        } else {
            $type = $methodParameter->getClass();
            if ($type !== null) {
                $binder = $this->context->getModelBinders()->get($type->getName());
                $invokeParameters[] = $binder->bind($this, $this->activeAction, $type->getName(), $methodParameter->name);
                $this->boundParameters[$methodParameter->name] = $binder->getBound();
            } else {
                $invokeParameters[] = $methodParameter->isDefaultValueAvailable() ?
                        $methodParameter->getDefaultValue() : null;
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
        foreach ($lines as $line) {
            if (preg_match("/@ntentan\.(?<attribute>[a-z]+)\s+(?<value>.+)/", $line, $matches)) {
                $attributes[$matches['attribute']] = $matches['value'];
            }
        }
        return $attributes;
    }

    private function getMethod($path)
    {
        $className = (new ReflectionClass($this))->getShortName();
        $methods = $this->context->getCache()->read(
                "controller.{$className}.methods", function () {
                    $class = new ReflectionClass($this);
                    $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
                    $results = [];
                    foreach ($methods as $method) {
                        $methodName = $method->getName();
                        if (substr($methodName, 0, 2) == '__') {
                            continue;
                        }
                        if (array_search($methodName, ['getActiveControllerAction', 'executeControllerAction'])) {
                            continue;
                        }
                        $docComments = $this->parseDocComment($method->getDocComment());
                        $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;
                        $results[$keyName] = [
                    'name' => $method->getName(),
                    'binder' => isset($docComments['binder']) ? $docComments['binder'] : $this->context->getModelBinders()->getDefaultBinderClass()
                ];
                    }
                    return $results;
                }
        );

        if (isset($methods[$path . utils\Input::server('REQUEST_METHOD')])) {
            return $methods[$path . utils\Input::server('REQUEST_METHOD')];
        } elseif (isset($methods[$path])) {
            return $methods[$path];
        }

        return false;
    }

    public function executeControllerAction($action, $params, $context)
    {
        $action = $action == '' ? 'index' : $action;
        $methodName = Text::camelize($action);
        $return = null;
        $invokeParameters = [];
        $this->context = $context;

        if ($methodDetails = $this->getMethod($methodName)) {
            $this->activeAction = $action;
            $container = $context->getContainer();
            $container->bind(controllers\ModelBinderInterface::class)
                    ->to($methodDetails['binder']);
            $method = new \ReflectionMethod($this, $methodDetails['name']);
            $methodParameters = $method->getParameters();
            foreach ($methodParameters as $methodParameter) {
                $this->bindParameter($invokeParameters, $methodParameter, $params);
            }

            return $method->invokeArgs($this, $invokeParameters);
        }
        throw new exceptions\ControllerActionNotFoundException($this, $methodName);
    }
}
