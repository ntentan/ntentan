<?php

/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
 *
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
	protected $renderer = "default";

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
	 */
	protected $renderer_element;

	/**
	 * The database table into which all the data represented in this
	 * container is to be dumped.
	 */
	//protected $database_table;

	/**
	 * The database schema in which the table into which the data is to
	 * be dumped is found.
	 */
	//private $database_schema;

	/**
	 * The primary key field of the database table.
	 */
	//protected $primary_key_field;

	//! The primary key value of the database table.
	//protected $primary_key_value;

	//! When set to false the fields are not shown for editing.
	protected $showfields = true;

	//! Stores the name of a custom function to call when the form is
	//! being rendered.
	protected $onRenderCallback;

	/**
	 * The Ntentan Model which holds the form's data.
	 * @see Model
	 */
	//protected $model;

	protected $callback;
	protected $callbackData;
	public $isContainer = true;


	/**
	 * Sets the current renderer being used by the container. The renderer
	 * is responsible for rendering the HTML form content.
	 * @param $renderer The name of the renderer being used.
	 * @return Container
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;
		include_once "Renderers/$this->renderer.php";
		$this->renderer_head = $renderer."_renderer_head";
		$this->renderer_foot = $renderer."_renderer_foot";
		$this->renderer_element = $renderer."_renderer_element";
		return $this;
	}

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
            $element->formId = $this->formId;
            array_push($this->elements, $element);
            $element->setMethod($this->getMethod());
            $element->setShowField($this->getShowField());
            $element->parent = $this;
            $element->setNameEncryption($this->getNameEncryption());
            $element->setNameEncryptionKey($this->getNameEncryptionKey());
            $element->ajax = $this->ajax;
            $this->hasFile |= $element->getHasFile();
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
	 * Method for removing a particular form element from the
	 * container.
	 *
	 * @param $index The index of the element to be removed.
	 * @todo Implement the method to remove an element from the Container.
	 */
	public function remove($index)
	{

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

	//! This method returns a structured array which represents the data stored
	//! in all the fields in the class. This method is recursive so one call
	//! to it extracts all the fields in all nested containers within this
	//! container.
	//! \param $storable This variable is set as true only when data for
	//!                  storable fields is required. A storable field
	//!                  is field which can be stored in the database.
	public function getData($storable=false)
	{
		$data = array();

		if($this->isFormSent())
		{
			foreach($this->elements as $element)
			{
				if($storable)
				{
					if($element->getStorable()==true) $data+=$element->getData($storable);
				}
				else
				{
					$data+=$element->getData();
				}
			}
		}
		else
		{
			foreach($this->elements as $element)
			{
				if($element->getType()=="Container")
				{
					$data+=$element->getData();
				}
				else
				{
					$data+=array($element->getName(false) => $element->getValue());
				}
			}
		}

		return $data;
	}

	//! This method sets the method of transfer for this container. The method
	//! could either be "GET" or "POST".
	public function setMethod($method) {
		$this->method = strtoupper($method);
		foreach($this->elements as $element) {
			$element->setMethod($method);
		}
	}

	public function getType()
	{
		return __CLASS__;
	}

	//! Render all the Elements found within this container. The Elements
	//! are rendered using the current renderer.
	protected function renderElements()
	{
        $this->setRenderer($this->renderer);
		$renderer_head = $this->renderer_head;
		$renderer_foot = $this->renderer_foot;
		$renderer_element = $this->renderer_element;
		$ret = "";

        $this->onRender();

		if($renderer_head!="") $ret .= $renderer_head();
		foreach($this->elements as $element)
		{
			$ret .= $renderer_element($element,$this->getShowField());
		}
		if($renderer_head!="") $ret .= $renderer_foot();
		return $ret;
	}

	//! Sets whether the fields should be exposed for editing. If this
	//! field is set as true then the values of the fields as retrieved
	//! from the database are showed.
	public function setShowField($showfield)
	{
		Element::setShowField($showfield);
		foreach($this->getElements() as $element)
		{
			$element->setShowField($showfield);
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

	//! Sets the value of the render callback function.
	public function setRenderCallback($onRenderCallback)
	{
		$this->onRenderCallback = $onRenderCallback;
	}

	//! Returns the value of the render callback function.
	public function getRenderCallback()
	{
		return $this->onRenderCallback;
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

	/**
	 * Sets the callback function which should be fired whenever this container
	 * is successfully submitted.
	 * @param $callback The callback function
	 * @return Container
	 */
	public function setCallback($callback,$data)
	{
		$this->callback = $callback;
		$this->callbackData = $data;
		return $this;
	}

	protected static function executeCallback()
	{
		$args = func_get_args();
		$function = array_shift($args);
		$function = explode("::",$function);
		if(count($function)==2)
		{
			$method = new ReflectionMethod($function[0], $function[1]);
			return $method->invokeArgs(null, $args);
		}
		else if(count($function)==1)
		{
			$method = $function[0];
			if(function_exists($method))
			{
				return $method($args[0],$args[1],$args[2]);
			}
		}
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
