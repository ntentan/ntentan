<?php
/**
 * Abstract form elements
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

use ntentan\views\helpers\forms\FormsHelper;

/**
 * The form element class. An element can be anything from the form
 * itself to the objects that are put in the form. Provides an
 * abstract render class that is used to output the HTML associated
 * with this form element. All visible elements of the form must be
 * subclasses of the element class.
 */
abstract class Element
{
    const SCOPE_ELEMENT = "";
    const SCOPE_WRAPPER = "_wrapper";

    protected $formId;

    /**
     * The id of the form useful for CSS styling and DOM access.
     * 
     * @var string
     */
    protected $id;

    /**
     * The label of the form element.
     * 
     * @var string
     */
    protected $label;

    /**
     * The description of the form element.
     * 
     * @var string
     */
    protected $description;

    /**
     * An array of all the CSS classes associated with this element.
     *
     * @var array 
     */
    protected $classes = array();
    
    /**
     * An array of all HTML attributes. These attributes are stored as
     * objects of the Attribute class. Attributes in this array are applied
     * directly to the form element.
     *
     * @var array 
     */    
    protected $attributes = array();
    
    /**
     * An array of all HTML attributes. These attributes are stored as
     * objects of the Attribute class. Attributes in this array are applied
     * directly to the wrapper which wraps the form element.
     *
     * @var array 
     */    
    protected $wrapperAttributes = array();
    
    /**
     * An array of all error messages associated with this element.
     * Error messages are setup during validation, when any element
     * fails its validation test.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * A boolean value which is set to true whenever there is an error 
     * assiciated with the field element in one way or the other.
     */
    protected $error;

    /**
     * The parent element which contains this element.
     * @var Element
     */
    protected $parent = null;

    /**
     * The name of the form field. This is what is to be outputed as
     * the HTML name attribute of the field. If name encryption is
     * enabled the outputed name to HTML is mangled by the encryption
     * algorithm. However internally the Field may still be referenced
     * bu the unmangled name.
     */
    public $name;

    public $renderLabel = true;

    private static $count;

    public function __construct($label="", $description="", $id="")
    {
        $this->setLabel($label);
        $this->description($description);
        $this->id($id);
    }

    public function id($id = false)
    {
        if($id === false)
        {
            return $this->id;
        }
        else
        {
            $this->id = str_replace(".","_",$id);
            $this->attribute('id', $this->id);
            return $this;
        }
    }
    
    /**
     * Public accessor for setting the name property of the field.
     *
     * @param  $name The name to assign to the form element.
     * @deprecated
     */
    public function setName($name)
    {
        $this->name($name);
        return $this;
    }

    /**
     * Public accessor for getting the name property of the field.
     *
     * @return The name of the form field.
     * @deprecated
     */
    public function getName()
    {
        return $this->name;
    }

    public function name($name = false)
    {
        if($name === false)
        {
            return $this->name;
        }
        else
        {
            $this->name = $name;
            $this->setValue(FormsHelper::getDataField($this->name));
            return $this;
        }
    }

    //! Sets the label which is attached to this element.
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    //! Gets the label which is attached to this element.
    public function getLabel()
    {
        return $this->label;
    }

    public function label($label = null)
    {
        if($label === null)
        {
            return $this->label;
        }
        else
        {
            $this->label = $label;
            return $this;
        }
    }

    /**
     * Gets the description which is attached to this element. The description
     * is normally displayed under the element when rendering HTML.
     *
     * @deprecated
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description which is attached to this element. The description
     * is normally displayed under the element when rendering HTML.
     *
     * @deprecated
     * @return string
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function description($description = null)
    {
        if($description === false)
        {
            return $this->description;
        }
        else
        {
            $this->description = $description;
            return $this;
        }
    }
    /**
     * Returns all the arrays associated with this document.
     *
     * @return array 
     */
    public function getErrors()
    {
        return $this->errors;
    }

    // Returns the error flag for this element.
    public function hasError()
    {
        return $this->error;
    }

    public function getType()
    {
        return __CLASS__;
    }

    /**
     * Renders the form element by outputing the HTML associated with
     * the element. This method is abstract and it is implemented by
     * all the other classes which inherit the Element class.
     */
    abstract public function render();

    public function __toString()
    {
        return FormsHelper::getRendererInstance()->element($this);
    }

    //! Returns an array of all the CSS classes associated with this
    //! element.
    public function getCSSClasses()
    {
        $ret = "";
        foreach($this->classes as $class)
        {
            $ret .= $class." ";
        }
        return $ret;
    }

    //! Adds a css class to this element.
    public function addCSSClass($class)
    {
        $this->classes[] = $class;
        return $this;
    }

    public function attribute($key, $value = false, $scope = Element::SCOPE_ELEMENT)
    {
        if($value === false)
        {
            switch($scope)
            {
                case Element::SCOPE_ELEMENT:
                    return $this->attributes[$key];
                    break;

                case Element::SCOPE_WRAPPER: 
                    return $this->wrapperAttributes[$key];
                    break;
            }
        }
        else
        {
            switch($scope)
            {
                case Element::SCOPE_ELEMENT:
                    $this->attributes[$key] = $value;
                    break;

                case Element::SCOPE_WRAPPER:
                    $this->wrapperAttributes[$key] = $value;
                    break;
            }
            return $this;
        }
    }

    //! Adds an attribute to the list of attributes of this element.
    //! This method internally creates a new Attribute object and appends
    //! it to the list of attributes.
    //! \see Attribute
    public function addAttribute($attribute,$value,$scope = Element::SCOPE_ELEMENT)
    {
        // Force the setting of the attribute.
        if($scope == Element::SCOPE_ELEMENT)
        {
            $this->attributes[$attribute] = $value;
        }
        else if($scope == Element::SCOPE_WRAPPER)
        {
            $this->wrapperAttributes[$attribute] = $value;
        }
        return $this;
    }

    //! Sets the value for a particular attribute.
    public function setAttribute($attribute,$value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    /**
     * Returns an HTML representation of all the attributes. This method is 
     * normally called when rendering the HTML for the element.
     */
    public function getAttributes($scope=Element::SCOPE_ELEMENT)
    {
        switch($scope)
        {
            case Element::SCOPE_ELEMENT: $attributes = $this->attributes; break;
            case Element::SCOPE_WRAPPER: $attributes = $this->wrapperAttributes; break;
        }
        $ret = "";
        foreach($attributes as $key => $value)
        {
            $ret .= $key . '="' . $value . '" ';
        }
        return $ret;
    }
    
    public function setErrors($errors)
    {
        $this->errors = $errors;
        $this->error = true;
    }

    public function addErrors($error)
    {
        if(is_array($error))
        {
            $this->errors = array_merge($this->errors, $error);
            $this->error = true;
        }
        else
        {
            $this->error = true;
            $this->errors[] = $error;
        }
    }

    public function clearErrors()
    {
        $this->error = false;
        $this->errors = array();
    }
    
    public function getShowField()
    {
        return $this->showField;
    }
}

