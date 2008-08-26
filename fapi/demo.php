<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Testing Forms</title>
<link type='text/css' rel='stylesheet' href='css/fapi.css' />
</head>
    <body>
    <?php
    
    function hello($data)
    {
    	print "Validated";
    	print_r($data);
    }
    
    include_once "FAPI.php";
	$form = new Form();
	
	$personal_info_frame = new Fieldset("Personal Information");
	$personal_info_frame->setDescription("This is just a brief description of this frameset.");
	$text = new TextField("Some Field","some_field", "This is just a description of this field");
	$text->setRequired(true);
	$text->setAsNumeric(0,100);
	$checkbox = new Checkbox("Some Checkbox","some_checkbox","A description of the checkbox","what");
	
	$radiogroup = new RadioGroup("asem");
	$radiogroup->setName("radios");
	$radiogroup->addRadiobutton(new RadioButton("Radio 1","radio_1"));
	$radiogroup->addRadiobutton(new RadioButton("Radio 2","Hell"));
	$radiogroup->addRadiobutton(new RadioButton("Radio 3","radio 3"));
	$radiogroup->addRadiobutton(new RadioButton("Radio 4","radio 4"));
		
	$personal_info_frame->add($text);
	$personal_info_frame->add($checkbox);
	$personal_info_frame->add($radiogroup);
	
	$selection = new SelectionList("Some List","some_list","This is just some damn list");
	$selection->setRequired(true);
	$selection->addOption("Ghana", "gh");
	$selection->addOption("Nigeria", "ng");
	$selection->addOption("Algeria", "al");
	$selection->addOption("Guinea", "gi");
	
	$table = new TableLayout();
	$form->add($table);
	$table->add($personal_info_frame);
	
	$description = new TextArea("Description","description","An optional description of the form");
	$personal_info_frame->add($description);
		
	$personal_info_frame->add($selection);
	
	$form->setSubmitValue("Submit");
	$form->setCallback("hello");
	$form->render(); 
	
	?>
	
    </body>
</html>