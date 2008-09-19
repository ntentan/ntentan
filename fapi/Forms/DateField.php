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
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

include_once "Field.php";
class DateField extends Field
{
	protected $dayField;
	protected $monthField;
	protected $yearField;
	
	public function __construct($label="",$name="",$description="")
	{
		Field::__construct($name);
		Element::__construct($label,$description);
		
		//Setup the field to contain the days.
		$this->dayField = new SelectionList("Day",$name."_day");
		for($i=1; $i<32; $i++)
		{
			$this->dayField->addOption(strval($i),strval($i));
		}
		
		//Setup the Month Field
		$this->monthField = new MonthField("Month",$name."_month");
		
		//Setup the year field
		$this->yearField = new TextField("Year", $name."_year");
		$this->yearField->setAsNumeric(1900,4000);
	}
	
	public function render()
	{
		print "<div style='float:left'><span class='fapi-description'>Day</span><br />";
		$this->dayField->render();
		print "</div>
		       <div style='float:left'><span class='fapi-description'>Month</span><br />";
		$this->monthField->render();
		print "</div>
		       <div style='float:left'><span class='fapi-description'>Year</span><br />";
		$this->yearField->render();
		print "</div><p style='clear:both'></p>";
	}
	
	public function setMethod($method)
	{
		Element::setMethod($method);
		$this->dayField->setMethod($method);
		$this->monthField->setMethod($method);
		$this->yearField->setMethod($method);
	}
	
	public function getValue()
	{
		if( $this->dayField->getValue()=="" ||
			$this->monthField->getValue()=="" ||
			$this->yearField->getValue()=="")
		{
			return "";	
		}
		else
		{
			return $this->dayField->getValue()."-".$this->monthField->getValue()."-".$this->yearField->getValue();
		}
	}
	
	public function getData()
	{
		$this->dayField->getData();
		$this->yearField->getData();
		$this->monthField->getData();
		return array($this->getName() => $this->getValue());
	}
	
	public function validate()
	{
		if(parent::validate())
		{
			$ret = true;
			if( $this->dayField->getValue()!="" ||
				$this->monthField->getValue()!="" ||
				$this->yearField->getValue()!="")
			{
				$ret = $this->dayField->validate();
				$ret = $this->monthField->validate();
				$ret = $this->yearField->validate();
			}
			
			if(!$ret)
			{
				$this->error = true;
				array_push($this->errors,"Please ensure that the date entered is valid");
			}
			return $ret;
		}
		else
		{
			return false;
		}
	}
}
?>