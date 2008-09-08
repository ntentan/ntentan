<?php
include_once ("Element.php");
/**
 * The form field class. This class represents a form field element.
 * Sublcasses of this class are to be used to capture information from
 * the user of the application.
 *
 */
abstract class Field extends Element
{
	/**
	 * The name of the form field. This is what is to be outputed as
	 * the HTML name attribute of the field. 
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * A flag for setting the required state of the form.
	 *
	 * @var boolean
	 */
	protected $required = false;
	
	/**
	 * The value of the form field.
	 *
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * The enabled state of the field
	 *
	 * @var boolean
	 */
	protected $enabled;
	
	protected $validationFunc;
	
	/**
	 * The constructor for the fiel element.
	 *
	 * @param unknown_type $value
	 */
	public function __construct($name="", $value="")
	{
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * Public accessor for setting the name property of the field.
	 *
	 * @param  $name The name to assign to the form element.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Public accessor for getting the name property of the field.
	 *
	 * @return The name of the form field.
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Sets the value of the field.
	 *
	 * @param $value The value of the field.
	 */
	public function setValue($value)
	{
		$this->value = $value; 
	}
	
	/**
	 * Get the value of the field.
	 *
	 * @return unknown
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
     * Sets the required status of the field.
     * 
     * @param The required status of the field.
     */
	public function setRequired($required)
	{
		$this->required = $required;
	}
	
	/**
     * Returns the required status of the field.
     * 
     * @return The required status of the field.
     */
	public function getRequired()
	{
		return $this->required;
	}
	
	public function getData()
	{
		if($this->getMethod()=="POST")
		{
			$this->setValue($_POST[$this->getName()]);
		}
		else if($this->getMethod()=="GET")
		{
			$this->setValue($_GET[$this->getName()]);
		}
		else
		{
			print "No Method";
			$this->setValue("");
		}	
			
		return array($this->getName() => $this->getValue());
	}
	
	/**
	 * Sets a custom validation function which is to be called during the
	 * validation phase. This function takes as a parameter an array which must
	 * be used to store all the individual error messages encountered during the
	 * validation phase.
	 *
	 * @param $validationFunc A string representing the name of the validation
	 * function
	 */
	public function setValidationFunc($validationFunc)
	{
		$this->validationFunc = $validationFunc;
	}
	
	public function validate()
	{
		if($this->getRequired() && $this->getValue() == "")
		{
			$this->error = true;
			array_push($this->errors,"This field is required.");
			return false;
		}
		$validationFunc = $this->validationFunc;
		if($validationFunc!="")
		{
			$validationFunc($this->errors);
		}
		return true;
	}
	
	public function getType()
	{
		return __CLASS__;
	}
	
	public function getCSSClasses()
	{
		$classes=parent::getCSSClasses();
		if($this->error) $classes.="error ";
		if($this->getRequired()) $classes .="required ";
		return $classes;
	}
	
}
?>