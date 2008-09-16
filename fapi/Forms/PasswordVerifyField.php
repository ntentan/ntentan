<?php
/**
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


/**
 * The PasswordVerifyField for getting two passwords and verifying them.
 *
 */
class PasswordVerifyField extends Field
{
	protected $passwordField1;
	protected $passwordField2;
	protected $container;
	
	public function __construct($name="")
	{
		$this->setName($name);
		$container = new BoxContainer();
		$passwordField1 = new PasswordField("Password","password_1","The password you want to be associated with your account.");
		$container->add($passwordField1);
		$passwordField2 = new PasswordField("Retype-Password","password_2","Retype the password you entered above");
		$container->add($passwordField2);
	}
	
	public function render()
	{
		$container->render();
	}
	
	public function getData()
	{
		if($this->getMethod()=="POST")
		{
			return array($this->getName()=>$this->getValue());
		}
	}
	
	public function validate()
	{
		
	}
}
?>