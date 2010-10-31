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

/**
 * A simple container for containing form elements. This container does
 * not expose itself to styling by default but styling can be added
 * by adding a css class through the attributes interface.
 */
class BoxContainer extends Container
{
	public function __construct()
	{
		parent::__construct();
	}

	public function render()
	{
		$ret = "";
		$this->addAttribute("class","fapi-box {$this->getCSSClasses()}");
		$ret .= "<div {$this->getAttributes()}>";
		$ret .= $this->renderElements();
		$ret .= "</div>";
		return $ret;
	}

}
?>
