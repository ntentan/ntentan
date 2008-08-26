<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Testing Forms</title>
</head>
    <body>
    <?php
    include_once "FAPI.php";
	$form = new Form();
	$text = new TextField("Some Field", "This is just a description of this field");
	$form->add($text);
	$form->render();
	?>
    </body>
</html>