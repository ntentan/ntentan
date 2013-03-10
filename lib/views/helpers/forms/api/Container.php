<?php
/**
 * Containers for containing form elements
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

    /**
     * This method sets the data for the fields in this container. The parameter
     * passed to this method is a structured array which has field names as keys
     * and the values as value.
     */
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
