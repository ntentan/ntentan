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

namespace ntentan\views\helpers\forms;

use ntentan\views\helpers\Helper;
use ntentan\Ntentan;
use \ReflectionMethod;
use \ReflectionClass;
use \Exception;

/**
 * Forms helper for rendering forms.
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class FormsHelper extends Helper
{
    private $container;
    public $submitValue;
    public $id;
    private static $rendererInstance;
    public static $renderer = "inline";
    private $data = array();
    private $errors = array();
    public $echo = false;
    
    public function __construct()
    {
        Ntentan::addIncludePath(
            Ntentan::getFilePath("lib/views/helpers/forms/api")
        );
        Ntentan::addIncludePath(
            Ntentan::getFilePath("lib/views/helpers/forms/api/renderers")
        );
        $this->container = new api\Form();
    }
    
    public function __toString()
    {
        $this->container->submitValue = $this->submitValue;
        $this->container->setId($this->id);
        $this->container->setData($this->data);
        $this->container->setErrors($this->errors);
        $return = (string)$this->container;
        $this->container = new api\Form();
        return $return;
    }
    
    public static function create()
    {
        $args = func_get_args();
        $element = __NAMESPACE__ . "\\api\\" . array_shift($args);
        $element = new ReflectionClass($element);
        return $element->newInstanceArgs($args==null?array():$args);
    }
        
    public function add()
    {
        $args = func_get_args();
        if(is_string($args[0]))
        {
            $elementClass = new ReflectionMethod(__NAMESPACE__ . "\\FormsHelper", 'create');
            $element = $elementClass->invokeArgs(null, $args);
            $this->container->add($element);
        }
        return $element;
    }
    
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function createModelField($field)
    {
        switch($field["type"])
        {
            case "double":
                $element = new api\TextField(ucwords(str_replace("_", " ", $field["name"])), $field["name"]);
                $element->setAsNumeric();
                break;

            case "integer":
                if($field["foreign_key"]===true)
                {
                    $element = new api\ModelField(ucwords(str_replace("_", " ", substr($field["name"], 0, strlen($field["name"])-3))), $field["model"]);
                    $element->name = $field["name"];
                }
                else
                {
                    $element = new api\TextField(ucwords(str_replace("_", " ", $field["name"])), $field["name"]);
                    $element->setAsNumeric();
                }
                break;

            case "string":
                $element = new api\TextField(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "text":
                $element = new api\TextArea(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "boolean":
                $element = new api\Checkbox(Ntentan::toSentence($field["name"]), $field["name"], "", 1);
                break;
            case "datetime":
            case "date":
                $element = new api\DateField(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
                
            default:
                throw new \Exception("Unknown data type {$field["type"]}");
        }
        if($field["required"]) $element->setRequired(true);
        $element->setDescription($field["comment"]);
        return $element;
    }
    
    public function addModelField($field, $return = false)
    {
        $element = $this->createModelField($field);
        if($return)
        {
            return $element;
        }
        else
        {
            $this->container->add($element);
        }
    }
    
    public static function getRendererInstance()
    {
        if(self::$rendererInstance == null || self::$renderer != self::$rendererInstance->type())
        {
            $rendererClass = __NAMESPACE__ . "\\api\\renderers\\" . Ntentan::camelize(self::$renderer);
            self::$rendererInstance = new $rendererClass();
        }
        return self::$rendererInstance;
    }

    public static function getStylesheet()
    {
        return Ntentan::getFilePath('lib/views/helpers/forms/css/forms.css');
    }
    
    public function __call($function, $arguments)
    {
        if($function == "open")
        {
            if($arguments[0] != '')
            {
                $this->container->setId($arguments[0]);
            }
            $this->container->rendererMode = 'head';
            $return = $this->container;
        }
        else if($function == "get")
        {
            $name = $arguments[0]['name'];
            $elementObject = $this->createModelField($arguments[0]);
            $elementObject->setValue($this->data[$name]);
            if(isset($this->errors[$name]))
            {
                $elementObject->addErrors($this->errors[$name]);
            }
            $return = $elementObject;
        }
        else if($function == "close")
        {
            if($arguments[0] != "")
            {
                $this->container->submitValue = $arguments[0];
            }
            elseif($arguments[0] === false)
            {
                $this->container->showSubmit = false;
            }
            $this->container->rendererMode = 'foot';
            $return = $this->container;
        }
        else if(substr($function, 0, 5) == "open_")
        {
            $container = "ntentan\\views\\helpers\\forms\\api\\" . Ntentan::camelize(substr($function, 5, strlen($function)));
            $containerClass = new ReflectionClass($container);
            $containerObject = $containerClass->newInstanceArgs($arguments);
            $return = $containerObject->renderHead();
        }
        elseif(substr($function, 0, 6) == "close_")
        {
            $container = "ntentan\\views\\helpers\\forms\\api\\" . Ntentan::camelize(substr($function, 6, strlen($function)));
            $containerClass = new ReflectionClass($container);
            $containerObject = $containerClass->newInstanceArgs($arguments);
            $return = $containerObject->renderFoot();
        }
        elseif(substr($function, 0, 4) == "get_")
        {
            $element = "ntentan\\views\\helpers\\forms\\api\\" . Ntentan::camelize(substr($function, 4, strlen($function)));
            $elementClass = new ReflectionClass($element);
            $elementObject = $elementClass->newInstanceArgs($arguments);
            $name = $elementObject->getName();
            $elementObject->setValue($this->data[$name]);
            if(isset($this->errors[$name]))
            {
                $elementObject->addErrors($this->errors[$name]);
            }
            $return = $elementObject;
        }
        elseif(substr($function, 0, 4) == "add_")
        {
            $element = "ntentan\\views\\helpers\\forms\\api\\" . Ntentan::camelize(substr($function, 4, strlen($function)));
            $elementClass = new ReflectionClass($element);
            $elementObject = $elementClass->newInstanceArgs($arguments);
            $return = $this->container->add($elementObject);
        }
        else
        {
            throw new Exception("Function <code><b>$function</b></code> not found in form helper.");
        }
        if($this->echo)
        {
            echo $return;
        }
        else
        {
            return $return;
        }
    }
}
