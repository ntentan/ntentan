<?php
/*   Copyright 2008, James Ainooson 
 *
 *   This file is part of Ntentan.
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
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


include_once "Field.php";

class RadioGroup extends Field
{
	protected $buttons = array();
	
	public function __construct($label="",$name="",$description="")
	{
		$this->setLabel($label);
		$this->setName($name);	
		$this->setDescription($description);
	}
	
	public function add($button)
	{
		if($button->getType()=="RadioButton")
		{
			$button->setName($this->getName(false));
			$button->setNameEncryption($this->getNameEncryption());
			$button->setNameEncryptionKey($this->getNameEncryptionKey());
			array_push($this->buttons, $button);
		}
		else
		{
			throw new Exception("Object added to radio group is not of type RadioButton");
		}
	}
	
	public function removeRadioButton($index)
	{
		
	}
	
	public function render()
	{
		foreach($this->buttons as $button)
		{
			print "<div class='fapi-radio-button'>";
			$button->render();
			print "</div>";
		}
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
		return array($this->getName() => $this->getValue());
	}
	
	public function setNameEncryption($nameEncryption)
	{
		Element::setNameEncryption($nameEncryption);
		foreach($this->buttons as $element)
		{
			$element->setNameEncryption($nameEncryption);
		}
	}
	
	public function setNameEncryptionKey($nameEncryptionKey)
	{
		Element::setNameEncryptionKey($nameEncryptionKey);
		foreach($this->buttons as $element)
		{
			$element->setNameEncryptionKey($nameEncryptionKey);
		}
	}
}
?>