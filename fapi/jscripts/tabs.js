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
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
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