<?php
/**
 * The Attribute class is used for storing and rendering HTML attributes.
 *
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
	
	public function getHTML()
	{
		return "$this->attribute=\"$this->value\"";
	}
	
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}
	
	public function getAttribute()
	{
		return $this->attribute;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
}
?>