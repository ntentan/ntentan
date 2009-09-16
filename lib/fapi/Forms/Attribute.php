<?php
/**
 * The Attribute class is used for storing and rendering HTML attributes.
 * \ingroup Form_API
 */
class Attribute
{
	/**
	 * The attribute.
	 */
	protected $attribute;
	
	/**
	 * The value to be attached to the value.
	 */
	protected $value;
	
	/**
	 * The constructor of the Attribute.
	 * 
	 * @param $attribute The attribute.
	 * @param $value The value to attach to the attribute.
	 * 
	 */
	public function __construct($attribute, $value)
	{
		$this->attribute = $attribute;
		$this->value = $value;
	}
	
	/**
	 * Returs the HTML representation of the attribute.
	 */
	public function getHTML()
	{
		return "$this->attribute=\"$this->value\"";
	}
	
	/**
	 * Sets the value for the attribute.
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}
	
	/**
	 * Gets the value of the attribute.
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}
	
	/**
	 * Sets the value represented as the value of the attribute.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Returns the value of the attribute.
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	public function __tostring()
	{
		
	}
}
?>
