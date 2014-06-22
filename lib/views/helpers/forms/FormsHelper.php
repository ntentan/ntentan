<?php
/*
 * Forms helper
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

namespace ntentan\views\helpers\forms;

use ntentan\views\helpers\Helper;
use ntentan\Ntentan;
use \ReflectionMethod;
use \ReflectionClass;
use \Exception;

/**
 * Forms helper for rendering forms.
 */
class FormsHelper extends Helper
{
    private $container;
    public $id;
    private static $rendererInstance;
    public static $renderer = "inline";
    private static $data = array();
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
        \ntentan\views\template_engines\TemplateEngine::appendPath(
            Ntentan::getFilePath("lib/views/helpers/forms/views")
        );
    }
    
    /**
     * Renders the form when the value is used as a string
     * @return string
     */
    public function __toString()
    {
        $this->container->setId($this->id);
        $this->container->setData(self::$data);
        $this->container->setErrors($this->errors);
        $return = (string)$this->container;
        $this->container = new api\Form();
        return $return;
    }
    
    public function stylesheet()
    {
        return Ntentan::getFilePath('lib/views/helpers/forms/css/forms.css');
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
    
    public static function setData($data)
    {
        self::$data = $data;
    }
    
    public static function getDataField($field)
    {
        return self::$data[$field];
    }
    
    public function createModelField($field)
    {
        switch($field["type"])
        {
            case "double":
                $element = new api\TextField(ucwords(str_replace("_", " ", $field["name"])), $field["name"]);
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
            
            case "":
                throw new \Exception("Empty data type for {$field['name']}");
                
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
    
    public function help($arguments)
    {
        
    }
    
    public function addAttribute($key, $value)
    {
        $this->container->addAttribute($key, $value);
    }
    
    public function renderer($renderer = false)
    {
        if($renderer === false)
        {
            return self::$renderer; 
        }
        else 
        {
            self::$renderer = $renderer;
        }
    }

    public function __call($function, $arguments)
    {
        if($function == "open")
        {
            $this->container = new api\Form();
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
            $elementObject->setValue(self::$data[$name]);
            if(isset($this->errors[$name]))
            {
                $elementObject->setErrors($this->errors[$name]);
            }
            $return = $elementObject;
        }
        else if($function == "close")
        {
            if($arguments[0] != "")
            {
                foreach($arguments as $argument)
                {
                    $this->container->submitValues[] = $argument;
                }
            }
            elseif($arguments[0] === false)
            {
                $this->container->showSubmit = false;
            }
            $this->container->rendererMode = 'foot';
            $return = self::getRendererInstance()->foot();
            $return .= $this->container;
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
            if(isset(self::$data[$name])) 
            {
                $elementObject->setValue(self::$data[$name]);
            }
            if(isset($this->errors[$name]))
            {
                $elementObject->setErrors($this->errors[$name]);
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
            throw new Exception("Function *$function* not found in form helper.");
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
