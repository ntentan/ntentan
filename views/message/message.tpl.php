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
?><html>
<head>
<style>
body {
	background-color: #c0c0c0;
	font-family:"Helvetica", sans-serif;
}

#message {
	margin:5px;
	background-color: #f0f0f0;
	border-radius: 10px;
	-moz-border-radius: 10px;
	color: #404040;
	box-shadow: 0px 1px 5px rgba(0, 0, 0, .5);
	-webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, .5);
	-moz-box-shadow: 0px 1px 5px rgba(0, 0, 0, .5);	
}

#message h1 {
	margin: 0px;
	color: black;
	background-color: #808080;
	padding:10px;
    border-top-left-radius:10px;
    -moz-border-radius-topleft:10px;
    border-top-right-radius:10px;
    -moz-border-radius-topright:10px;
    background-color:#505050;
    background: -webkit-gradient(linear, left top, left bottom, from(#808080), to(#606060));
    background: -moz-linear-gradient(top, #808080, #606060);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#808080, endColorstr=#606060);
    -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#808080, endColorstr=#606060)";
    text-shadow: 0px 1px 0px #a0a0a0;
}
#message-body{
    padding:10px;
    margin:0px;
    color:black;
}

#message h2{
    margin:0px;
    padding:10px;
    background-color:#a0a0a0;
    color:white;
}

#trace
{
    background-color:white;
    margin:0px;
    padding:10px;
    border-bottom-left-radius:10px;
    -moz-border-radius-bottomleft:10px;
    border-bottom-right-radius:10px;
    -moz-border-radius-bottomright:10px;    
    font-size:small;
}

/*#trace h2
{
margin:0px;
padding:0px;
}*/

#trace table
{
width:100%;
border-collapse:collapse;
}

#trace table > thead > tr > th
{
    text-align:left;
    background-color:#e0e0e0;
    padding:10px;
}

#trace table > tbody > tr > td
{
    text-align:left;
    background-color:#f8f8f8;
    padding:10px;
    padding-top: 5px;
    padding-bottom: 5px;
}

</style>
<title>Ntentan Error!</title>
</head>
<div id='message'>
<h1>Ntentan Error</h1>
<?php echo ($subTitle == '' ? '' : "<h2>$subTitle</h2>") ?>
<div id="message-body">
<?php echo $message;?>
</div>
<?php if($showTrace===true):?>
<div id='trace'>
<h2>Debug Trace</h2>
<table>
<thead>
    <tr><th>File</th><th>Line</th><th>Function</th></tr>
</thead>
<tbody>
<?php foreach($trace as $trace_item):?>
    <tr>
        <td><code><?php echo $trace_item["file"]?></code></td>
        <td><code><?php echo $trace_item["line"]?></code></td>
        <td>
            <code>
                <b><?php echo $trace_item["class"].$trace_item["type"].$trace_item["function"]; ?></b>
            </code>
        </td>
    </tr>
<?php endforeach ?>
</tbody>
</table>
</div>
<?php endif ?>
</div>
</html>
