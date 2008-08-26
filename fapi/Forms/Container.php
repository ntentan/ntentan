<?php
include_once "Element.php";

/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
 *
 */
abstract class Container extends Element
{	
	/**
	 * The array which holds all the elements.
	 *
	 * @var Array
	 */
	protected $elements = array();
	
	/**
	 * Method for adding an element to the form container.
	 *
	 * @param unknown_type $element
	 */
	public function add($element)
	{
		array_push($this->elements, $element);
		$element->setMethod($this->method);
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
	
	public function getData()
	{
		$data = array();
		foreach($this->elements as $element)
		{
			$data+=$element->getData();
		}
		return $data;
	}
	
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
		foreach($this->elements as $element)
		{
			$element->setMethod($method);
		}
	}
	
	public function validate()
	{
		$retval = true;
		foreach($this->elements as $element)
		{
			if($element->validate()==false) 
			{
				$retval=false;
			}
		}
		return $retval;
	}
	
	public function getType()
	{
		return __CLASS__;
	}
		
}
?>