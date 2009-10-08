<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../../");

$uploaddir = '../../app/uploads/';

include "lib/models/Model.php";
include "lib/models/SQLDatabaseModel.php";
include "app/config.php";
include "connection.php";

$uploadfile = $uploaddir . basename($_FILES['file']['name']);

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
{
	$file = fopen($uploadfile,"r");
	$headers = fgetcsv($file);
	$model = Model::load($_GET["model"],"../../");
	$fieldInfo = $model->getFields(); array_shift($fieldInfo);
	$fields = array_keys($fieldInfo);
	$secondary_key = $model->getKeyField("secondary");
	
	foreach($model->getLabels() as $i => $label)
	{
		if($label!=$headers[$i])
		die("<div id='error'>Invalid file imported</div>");
	}
	
	
	$out = "<table class='data-table'>";
	$out .= "<thead><tr><td>".implode("</td><td>",$headers)."</td></tr></thead>";
	$out .= "<tbody>";
	$line = 1;
	$status = "<h3>Successfully Imported</h3>";
	
	while(!feof($file))
	{
		$data = fgetcsv($file);
		$model_data = array();
		$errors = array();
		if(count($data)<count($headers)) break;
		
		foreach($data as $i => $value)
		{
			$model_data[$fields[$i]] = $value;
		}				
		$display_data = $model_data;
		
		if($secondary_key!=null && count($errors==false))
		{
			$temp_data = $model->getWithField($secondary_key,$model_data[$secondary_key]);
			if(count($temp_data)>0) 
			{
				$validated = $model->setResolvableData($model_data,$secondary_key,$model_data[$secondary_key]);
				if($validated===true) $model->update($secondary_key,$model_data[$secondary_key]);
			}
			else
			{
				$validated = $model->setResolvableData($model_data);
				if($validated===true) $model->save();
			}
		}
		
		if($validated===true)
		{
			$out .= "<tr><td>".implode("</td><td>",$display_data)."</td></tr>";
		}
		else
		{
			$out .= "<tr style='border:1px solid red'>";
			foreach($display_data as $field=>$value)
			{
				$out .= "<td>$value";
				if(count($validated["errors"][$field])>0)
				{
					$out .= "<div class='fapi-error'><ul>";
					foreach($validated["errors"][$field] as $error)
					{
						$error = str_replace("%field_name%",$fieldInfo[$field]["label"],$error);
						$out .= "<li>$error</li>";
					}
					$out .= "</ul></div>";
				}
				$out .= "</td>";
			}
			$out .= "</tr>";
			$status = "<h3>Errors Importing Data</h3>Errors on line $line";
			break;
		}
		$line++;
	}
	$out .= "</tbody>";
	$out .= "</table>";
	
	print $status.$out;
}

?>
