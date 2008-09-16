<?php
/*
 *  
 *  Copyright 2008, James Ainooson 
 *
 *  This file is part of Ntentan.
 *
 *   Ntentan is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Ntentan is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once ("Element.php");
require_once ("DatabaseInterface.php");
require_once ("ValidatableInterface.php");


/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
 */
abstract class Container extends Element implements DatabaseInterface, Validatable
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
	
	protected $database_table;
	
	protected $database_schema;
	
	protected $primary_key_field;
	protected $primary_key_value;
	protected $showfields = true;
	
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
		if($element->parent==null)
		{
			array_push($this->elements, $element);
			$element->setMethod($this->getMethod());
			$element->setShowField($this->getShowField());
			$element->parent = $this;
		}
		else
		{
			throw new Exception("Element added already has a parent");
		}
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
	
	public function setData($data)
	{
		foreach($this->elements as $element)
		{
			$element->setData($data);
		}
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
	
	public function retrieveData()
	{
		$data = array();
		if($this->database_table!="")
		{
			$query = "SELECT * FROM ".($this->database_schema!=""?$this->database_schema.".":"")." {$this->database_table} WHERE {$this->primary_key_field}='{$this->primary_key_value}'";
			$result = mysql_query($query);
			$data = mysql_fetch_assoc($result);
		}
		return $data;
	}
	
	public function saveData()
	{
		if($this->database_table!="")
		{
			//Get Data from the database
			$data = $this->getData();
			
			//Extract Fields and build query
			$field = array_keys($data);
			
			if($this->primary_key_field=='')
			{
				$query = "INSERT INTO ".($this->database_schema!=""?$this->database_schema.".":"").$this->database_table."(";
				for($i=0; $i<count($field); $i++)
				{
					if($i!=0) $query.=",";
					$query.=$field[$i];
				}
				$query.=") VALUES(";
				for($i=0; $i<count($field); $i++)
				{
					if($i!=0) $query.=",";
					$query.="\"".mysql_escape_string($data[$field[$i]])."\"";
				}
				$query.=")";
			}
			else
			{
				$query = "UPDATE ".($this->database_schema!=""?$this->database_schema.".":"").$this->database_table.
				         " SET ";
				for($i=0; $i<count($field); $i++)
				{
					$query.=$field[$i]."=\"".mysql_escape_string($data[$field[$i]])."\" ";
					if($i!=count($field)-1) $query.=", ";
				}
				
				$query .= " WHERE {$this->primary_key_field}='{$this->primary_key_value}'";
			}
			//print $query;
			mysql_query($query) or die(mysql_error());
		}
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
		
		if($render_head!="") $render_head();
		foreach($this->elements as $element)
		{
			$renderer_element($element,$this->getShowField());
		}
		if($render_head!="") $render_foot();
	}
	
	public function setDatabaseTable($database_table)
	{
		$this->database_table = $database_table;
	}
	
	public function getDatabaseTable()
	{
		if($this->database_table=="")
		{
			if($this->parent != null) return $this->parent->getDatabaseTable();
		}
		else
		{
			return $this->database_table;
		}
	}
	
	public function setDatabaseSchema($database_schema)
	{
		$this->database_schema = $database_schema;
	}
	
	public function getDatabaseSchema()
	{
		return $this->database_schema;
	}
	
	public function setPrimaryKey($primary_key_field,$primary_key_value)
	{
		$this->primary_key_field = $primary_key_field;
		$this->primary_key_value = $primary_key_value;
	}
	
	public function getPrimaryKeyField()
	{
		if($this->primary_key_field=="")
		{
			if($this->parent != null) return $this->parent->getPrimaryKeyField();
		}
		else
		{
			return $this->primary_key_field;
		}
	}
	
	public function getPrimaryKeyValue()
	{
		if($this->primary_key_field=="")
		{
			if($this->parent != null) return $this->parent->getPrimaryKeyValue();
		}
		else
		{
			return $this->primary_key_value;
		}
	}
	
	public function setShowField($showfield)
	{
		Element::setShowField($showfield);
		foreach($this->getElements() as $element)
		{
			$element->setShowField($showfield);
		}
	}
	
	public function getElements()
	{
		return $this->elements;
	}
	
		
}
?>