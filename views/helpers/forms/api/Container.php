<?php

namespace ntentan\views\helpers\forms\api;

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
	 * The name of the renderer currently in use.
	 */
	protected $renderer = "inline";

	/**
	 * The header function for the current renderer. This function contains the
	 * name of the renderer post-fixed with "_renderer_head"
	 */
	protected $renderer_head;

	/**
	 * The footer function for the renderer currently in use. This function
	 * contains the name of the renderer post-fixed with "_renderer_foot".
	 */
	protected $renderer_foot;

	/**
	 * The element function for the renderer currently in use.
	 * @var string
	 */
	protected $renderer_element;

	/** 
	 * When set to false the fields are not shown for editing.
	 * @var boolean
	 */
	protected $showfields = true;

	/**
	 * Stores the name of a custom function to call when the form is being
	 * rendered.
	 * @var string
	 */
	protected $onRenderCallback;

	/**
	 * The Ntentan Model which holds the form's data.
	 * @see Model
	 */

	protected $callback;
	protected $callbackData;
	public $isContainer = true;

	/**
	 * Returns the renderer which is currently being used by the class.
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

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

	public function isFormSent()
	{
		if($this->getMethod()=="POST") $sent=$_POST["is_form_{$this->formId}_sent"];
		if($this->getMethod()=="GET") $sent=$_GET["is_form_{$this->formId}_sent"];
		if($sent=="yes") return true; else return false;
	}

	public function getType()
	{
		return __CLASS__;
	}
	
	protected function getRendererInstance()
	{
	    $rendererClass = __NAMESPACE__ . "\\renderers\\" . Ntentan::camelize($this->renderer);
        return new $rendererClass();
	}

	/**
	 * Render all the elements currently contained in this container. This method
	 * would initialize the renderer class and use it to layout the elements
	 * on the form.
	 */
	protected function renderElements()
	{
	    $renderer = $this->getRendererInstance();
        $this->onRender();
		$ret = $renderer->head();
		foreach($this->elements as $element)
		{
			$ret .= $renderer->element($element);
		}
		$ret .= $renderer->foot();
		return $ret;
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


	//! Returns all Elements found in this container which are subclasses
	//! of the Field class.
	public function getFields()
	{
		$elements = $this->getElements();
		$fields = array();
		foreach($elements as $element)
		{
			if($element->getType()=="Field" || $element->getType()=="Checkbox")
			{
				$fields[] = $element;
			}
			else if($element->getType()=="Container")
			{
				foreach($element->getFields() as $field)
				{
					$fields[] = $field;
				}
			}
		}
		return $fields;
	}

    public function get($name)
    {
        return $this->getElementByName($name);
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

	public function getElementById($id)
	{
		foreach($this->getElements() as $element)
		{
			if($element->getType()!="Container")
			{
				if($element->getId()==$id) return $element;
			}
			else
			{
				if($element->getId()==$id) return $element;
				try
				{
					return $element->getElementById($id);
				}
				catch(Exception $e){}
			}
		}
		throw new Exception("No element with id $id found in Container");
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
}
