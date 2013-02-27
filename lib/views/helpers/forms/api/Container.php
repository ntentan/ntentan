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

namespace ntentan\views\helpers\forms\api;

use ntentan\views\helpers\forms\Forms;
use ntentan\Ntentan;
use \Exception;

/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
 */
abstract class Container extends Element
{

    /**
     * The array which holds all the elements contained in this container.
     */
    protected $elements = array();

    /** 
     * When set to false the fields are not shown for editing.
     * @var boolean
     */
    protected $showfields = true;

    public $isContainer = true;
    
    public $rendererMode = 'all';

    private function addElement($element)
    {
        //Check if the element has a parent. If it doesnt then add it
        //to this container. If it does throw an exception.
        if($element->parent==null)
        {
            $this->elements[] = $element;
            $element->setShowFields($this->getShowField());
            $element->parent = $this;
        }
        else
        {
            throw new Exception("Element added already has a parent");
        }
    }

    /**
     * Method for adding an element to the form container.
     * @return Container
     */
    public function add()
    {
        $arguments = func_get_args();

        if(is_array($arguments[0]))
        {
            foreach($arguments[0] as $elementString)
            {
                $this->addElement(
                    Element::createFromString
                    (
                        $elementString[0],
                        $elementString[1],
                        $elementString[2],
                        $elementString[3]
                    )
                );
            }
        }
        else if(is_string($arguments[0]))
        {
            $this->addElement(
                Element::createFromString
                (
                    $arguments[0],
                    $arguments[1],
                    $arguments[2],
                    $arguments[3])
                );
        }
        else
        {
            foreach(func_get_args() as $element)
            {
                $this->addElement($element);
            }
            }
            return $this;
    }

    //! This method sets the data for the fields in this container. The parameter
    //! passed to this method is a structured array which has field names as keys
    //! and the values as value.
    public function setData($data)
    {
        if(is_array($data))
        {
            foreach($this->elements as $element)
            {
                $element->setData($data);
            }
        }
        return $this;
    }

    public function getType()
    {
        return __CLASS__;
    }

    /**
     * Render all the elements currently contained in this container. This method
     * would initialize the renderer class and use it to layout the elements
     * on the form.
     */
    private function renderElements()
    {
        $renderer = Forms::getRendererInstance();
        $this->onRender();
        $ret = $renderer->head();
        foreach($this->elements as $element)
        {
                $ret .= $renderer->element($element);
        }
        $ret .= $renderer->foot();
        return $ret;
    }

    abstract protected function renderHead();
    abstract protected function renderFoot();

    public function render()
    {
        switch($this->rendererMode)
        {
            case 'head';
                return $this->renderHead();
            case 'foot':
                return $this->renderFoot();
            case 'elements':
                return $this->renderElements();
        }
    }

    //! Sets whether the fields should be exposed for editing. If this
    //! field is set as true then the values of the fields as retrieved
    //! from the database are showed.
    public function setShowFields($showfield)
    {
        Element::setShowFields($showfield);
        foreach($this->getElements() as $element)
        {
            $element->setShowFields($showfield);
        }
    }

    //! Returns an array of all the Elements found in this container.
    public function getElements()
    {
        return $this->elements;
    }

    //! Returns an element in the container with a particular name.
    //! \param $name The name of the element to be retrieved.
    public function getElementByName($name)
    {
        foreach($this->getElements() as $element)
        {
            if($element->getType()!="Container")
            {
                if($element->getName(false)==$name) return $element;
            }
            else
            {
                try
                {
                    return $element->getElementByName($name);
                }
                catch(Exception $e){}
            }
        }
        throw new Exception("No element with name $name found in array");
    }

    public function setErrors($errors)
    {
        if(is_array($errors))
        {
            foreach($errors as $field => $error)
            {
                $this->getElementByName($field)->addErrors($error);
            }
        }
    }

    public function __toString()
    {
        return $this->render();
    }
}
