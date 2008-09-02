<?php
include_once "Element.php";

/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
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
	 * The name of the renderer currently in use.
	 */
	protected $renderer;
	
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
	
	public function __construct($renderer="default")
	{
		$this->setRenderer($renderer);
	}
	
	/**
	 * Sets the current renderer being used by the container.
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;
		include_once "Renderers/$this->renderer.php";
		$this->renderer_head = $renderer."_renderer_head";
		$this->renderer_foot = $renderer."_renderer_foot";
		$this->renderer_element = $renderer."_renderer_element";
	}
	
	public function getRenderer()
	{
		return $this->renderer;
	}
	
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
	
	protected function renderElements()
	{
		$renderer_head = $this->renderer_head;
		$renderer_foot = $this->renderer_foot;
		$renderer_element = $this->renderer_element;
		
		foreach($this->elements as $element)
		{
			$renderer_element($element);
		}		
	}
		
}
?>