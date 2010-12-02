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

var curtab = 0;

function fapiSwitchTabTo(n)
{
	if(n==curtab) return;
	$("#fapi-tab-"+String(curtab)).toggle();
	$("#fapi-tab-"+String(n)).toggle();
	$("#fapi-tab-top-"+String(n)).removeClass("fapi-tab-unselected");
	$("#fapi-tab-top-"+String(n)).addClass("fapi-tab-selected");
	$("#fapi-tab-top-"+String(curtab)).removeClass("fapi-tab-selected");
	$("#fapi-tab-top-"+String(curtab)).addClass("fapi-tab-unselected");
	curtab=n;
}


