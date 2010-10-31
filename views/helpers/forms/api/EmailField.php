<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\views\helpers\forms\api;

include_once("TextField.php");
/**
 * A text field for accepting email addresses. This field validates
 * the email addresses using a regular expression.
 * \ingroup Form_API
 */
class EmailField extends TextField
{
	public function __construct($label="",$name="",$description="",$value="")
	{
		parent::__construct($label,$name,$description,$value);
		$this->setRegExp('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$');
		/*$this->addJsValidation
		(array(
			"func"=>"fapiCheckRegexp",
			"regexp"=>Field::prepareMessage('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i'),
			"message"=>Field::prepareMessage("This is not a valid email address")
			)
		);*/		
	}
	
	public function validate()
	{
		if(!parent::validate())
		{
			array_push($this->errors, "Invalid email address entered");
			$this->error = true;
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
