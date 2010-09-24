<html>
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
#message p{
    padding:10px;
    margin:0px;
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

#trace h2
{
margin:0px;
padding:0px;
}
</style>
<title>Ntentan Error!</title>
</head>
<div id='message'>
<h1>Ntentan</h1>
<p><?php echo $message ?></p>
<?php if($showTrace===true):?>
<div id='trace'>
<h2>Debug Trace</h2>
<?php 
var_dump($trace);
?>
</div>
<?php endif ?>
</div>
</html>
