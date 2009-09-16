<?php
include_once "Attribute.php";

/**
 * The form element class. An element can be anything from the form
 * itself to the objects that are put in the form. Provides an
 * abstract render class that is used to output the HTML associated
 * with this form element. All visible elements of the form must be
 * subclasses of the element class.
 *
 * \ingroup Form_API
 *
 */
abstract class Element
{
	protected $ajax = true;	
	
	/**
	 * The id of the form useful for CSS styling and DOM access.
	 */
	protected $id;

	/**
	 * The label of the form element.
	 */
	protected $label;

	/**
	 * The description of the form element.
	 */
	protected $description;

	/**
	 * The method by which the form should be submitted.
	 */
	protected $method = "POST";

	//! An array of all the CSS classes associated with this element.
	protected $classes = array();

	//! An array of all HTML attributes. These attributes are stored as
	//! objects of the Attribute class.
	//! \see Attribute
	protected $attributes = array();

	//! An array of all error messages associated with this element.
	//! Error messages are setup during validation, when any element
	//! fails its validation test.
	protected $errors = array();

	//! A boolean value which is set to true whenever there is an error
	//! assiciated with the class in one way or the other.
	protected $error;

	//! A boolean value which is set to true if the form elements are
	//! to be made available for editing. If this property is set to false
	//! the form element shows only the value associated with this field
	//! in cases where the data has been collected from the database.
	protected $showfield = true;

	//! The parent element which contains this element.
	protected $parent = null;

	//! Sets up the name encryption. If this value is set to true, all
	//! the form element names which are output to HTML would be
	//! encrypted so that the structure of the internal database is not
	//! exposed in any way.
	protected $nameEncryption = false;

	//! The encryption key if some form of key based encryption is used.
	protected $nameEncryptionKey;

	//! A value which determines whether this field is to be used in
	//! constructing database queries
	protected $storable = true;

	//! A value which determines whether this element contains file data;
	protected $hasFile = false;

	public function __construct($label="", $description="", $id="")
	{
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setId($id);
	}

	/**
	 * Public accessor for setting the ID of the element.
	 */
	public function setId($id)
	{
		$this->id = $id;
		$this->addAttribute("id",$id);
	}

	/**
	 * Public accessor for getting the Id of the element.
	 */
	public function getId()
	{
		return $this->id;
	}

	//! Sets the label which is attached to this element.
	public function setLabel($label)
	{
		$this->label = $label;
	}

	//! Gets the label which is attached to this element.
	public function getLabel()
	{
		return $this->label;
	}

	//! Gets the description which is attached to this element. The description
	//! is normally displayed under the element when rendering HTML.
	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Sets the method for the form.
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
	}

	/**
     * Gets the method being used by the form. The method could be either
     * "GET" or "POST".
     */
	public function getMethod()
	{
		return $this->method;
	}

	//! Returns all the arrays associated with this document.
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
		array_push($this->classes, $class);
	}

	//! Adds an attribute object to the internal attribute array of the
	//! element.
	//! \see Attribute
	public function addAttributeObject($attribute)
	{
		array_push($this->attributes, $attribute);
	}

	//! Adds an attribute to the list of attributes of this element.
	//! This method internally creates a new Attribute object and appends
	//! it to the list of attributes.
	//! \see Attribute
	public function addAttribute($attribute,$value)
	{
		// Force the setting of the attribute.
		foreach($this->attributes as $attribute_obj)
		{
			if($attribute_obj->getAttribute()==$attribute)
			{
				$attribute_obj->setValue($value);
				return;
			}
		}
		$attribute = new Attribute($attribute, $value);
		$this->addAttributeObject($attribute);
	}

	public function removeAttribute($attribute)
	{
		$i=0;
		foreach($this->attributes as $attribute_obj)
		{
			if($attribute_obj->getAttribute()==$attribute)
			{
				array_splice($this->attributes,$i,1);
			}
			$i++;
		}
	}

	//! Sets the value for a particular attribute.
	public function setAttribute($attribute,$value)
	{
		foreach($this->attributes as $attrib)
		{
			if($attrib->getAttribute()==$attribute)
			{
				$attrib->setValue($value);
			}
		}
	}

	//! Returns an HTML representation of all the attributes. This method
	//! is normally called when rendering the HTML for the element.
	public function getAttributes()
	{
		$ret = "";
		foreach($this->attributes as $attribute)
		{
			$ret .= $attribute->getHTML()." ";
		}
		return $ret;
	}

	//! Sets whether the field should be shown or hidden.
	//! \see $showfield
	public function setShowField($showfield)
	{
		$this->showfield = $showfield;
	}

	//! Gets the value of the $showfield property.
	public function getShowField()
	{
		return $this->showfield;
	}

	//! Sets whether the form names should be encrypted. If this method
	//! is called with a parameter true, all the names that are rendered
	//! in the HTML code are encrypted so that the database internals
	//! are not exposed in anyway to the end users.
	public function setNameEncryption($nameEncryption)
	{
		$this->nameEncryption = $nameEncryption;
	}

	public function setNameEncryptionKey($nameEncryptionKey)
	{
		$this->nameEncryptionKey = $nameEncryptionKey;
	}

	public function getNameEncryption()
	{
		return $this->nameEncryption;
	}

	public function getNameEncryptionKey()
	{
		return $this->nameEncryptionKey;
	}

	public function getForeing()
	{
		return false;
	}

	public function hasOptions()
	{
		return false;
	}

	public function setStorable($storable)
	{
		$this->storable = $storable;
	}

	public function getStorable()
	{
		return $this->storable;
	}

	public function getHasFile()
	{
		return $this->hasFile;
	}
	
	public function addError($error)
	{
		$this->error = true;
		$this->errors[] = $error;
	}
}
?>
