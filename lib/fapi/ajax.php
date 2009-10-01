<?php
/**
 * AJAX handler for the database forms.
 */

require_once("../../db_connect.php");

switch($_GET["action"])
{
case "check_unique":
	$ret = array("status"=>true);
	$result =
	$db->query(
		sprintf(
			"SELECT %s FROM %s WHERE %s='%s'",
			$db->escape_string($_GET["f"]),
			$db->escape_string($_GET["t"]),
			$db->escape_string($_GET["f"]),
			$db->escape_string($_GET["v"])
		)
	);
	if($result!==false)
	{
		if($result->num_rows > 0)
		{
			$ret["message"] = "A ".$_GET["f"]." with value ".$_GET["v"]." already exists";
			$ret["status"] = false;
		}
	}
	print json_encode($ret);
	break;

case "save_data":
	$ret = array("status"=>false);
	$keys = array_keys($_POST);
	$fields = array();
	$values = array();
	for($i = 0; $i < count($keys); $i++)
	{
		if($keys[$i]=="fapi_dt")
		{
			$database_table = $_POST[$keys[$i]];
		}
		else
		{
			$fields[] = $keys[$i];
			$values[] = "'".$db->escape_string($_POST[$keys[$i]])."'";
		}
	}
	$query = "INSERT INTO $database_table(".implode(",",$fields).") VALUES (".implode(",",$values).")";
	$result = $db->query($query);
	if($result!==false)
	{
		$ret["status"] = true;
		$ret["id"] = $db->insert_id;
	}
	else
	{
		$ret["message"] = $db->error;
	}
	print json_encode($ret);
	break;
}
?>
