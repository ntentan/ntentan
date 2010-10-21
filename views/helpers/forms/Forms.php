<?php
namespace ntentan\views\helpers\forms;

use ntentan\views\helpers\Helper;
use ntentan\Ntentan;
use \ReflectionMethod;
use \ReflectionClass;

/**
 * Forms helper for rendering forms.
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class Forms extends Helper
{
    private $container;
    
    public $submitValue;
    
    public $id;
    
    public function __construct()
    {
        Ntentan::addIncludePath(
            Ntentan::getFilePath("views/helpers/forms/api")
        );
        Ntentan::addIncludePath(
            Ntentan::getFilePath("views/helpers/forms/api/renderers")
        );
        $this->container = new api\Form();
    }
    
    public function __toString()
    {
        $this->container->submitValue = $this->submitValue;
        $this->container->setId($this->id);
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
            $elementClass = new ReflectionMethod(__NAMESPACE__ . "\\Forms", 'create');
            $element = $elementClass->invokeArgs(null, $args);
            $this->container->add($element);
        }
        return $element;
    }
    
    public function setErrors($errors)
    {
        $this->container->setErrors($errors);
    }
    
    public function setData($data)
    {
        $this->container->setData($data);
    }
    
    public function createModelField($field)
    {
        switch($field["type"])
        {
            case "double":
                $element = new TextField(ucwords(str_replace("_", " ", $field["name"])), $field["name"]);
                $element->setAsNumeric();
                break;

            case "integer":
                if($field["foreing_key"]===true)
                {
                    $element = new ModelField(ucwords(str_replace("_", " ", substr($field["name"], 0, strlen($field["name"])-3))), $field["model"]);
                    $element->name = $field["name"];
                }
                else
                {
                    $element = new TextField(ucwords(str_replace("_", " ", $field["name"])), $field["name"]);
                    $element->setAsNumeric();
                }
                break;

            case "string":
                $element = new TextField(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "text":
                $element = new TextArea(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "boolean":
                $element = new Checkbox(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "datetime":
                $element = new DateField(Ntentan::toSentence($field["name"]), $field["name"]);
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
    
    public function open()
    {
        
    }
}
