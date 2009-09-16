<?php
class oracle extends model
{
	private static $_conn = null;
	private static $_s_conn = null;

	public static function connect($username,$password,$host,$database)
	{
		$db = "$host/$database";
		//print $db;
		oracle::$_s_conn = oci_connect($username, $password, $db);
	}

	public function __construct($model="",$conn=null)
	{
		parent::__construct($model);
		if($conn==null)
		{
			oracle::$_conn = oracle::$_s_conn;
		}
		else
		{
			oracle::$_conn = $conn;
		}
	}
	
	private function formatFields($row)
	{
		$n_row = array();
		$o_fields = array_keys($row);
		foreach($o_fields as $o_field)
		{
			$n_row[strtolower($o_field)] = $row[$o_field];
		}		
		return $n_row;
	}

	public function getWithField($field,$value)
	{
		$rows = array();
		//print sprintf("SELECT * FROM %s WHERE $field = '$value'",$this->database);
  		$stmt = oci_parse(oracle::$_conn, sprintf("SELECT * FROM %s WHERE $field = '$value'",$this->database));
  		oci_execute($stmt, OCI_DEFAULT);
	  	while ($row = oci_fetch_assoc($stmt))
	  	{
			$rows[] = $this->formatFields($row);
  		}
		oci_free_statement($stmt);
  		return $rows;
	}
	
	public function get($fields = null,$conditions=null,$mode=model::MODE_ASSOC)
	{
		$rows = array();
		
		// Get information about all referenced models and pull out all
		// the required information as well as build up the join parts
		// of the query.
		$references = $this->getReferencedFields();
		$joins = "";
		$do_join = false;
	
		
		// Generate the field list i.e. the fields that are to be returned
		// by the query. 
		if($fields!=null)
		{
			$expanded_fields = array();
			
			//Go through all the fields in the system.
			foreach($fields as $field)
			{
				$referred = false;
				foreach($references as $reference)
				{
					if($reference["referencing_field"] == (string)$field)
					{
						$referred = true;
						$do_join = true;
						$expanded_fields[] = $reference["table"].".".$reference["referenced_value_field"];
						break;
					}
				}
				if(!$referred)
				{
					$expanded_fields[]= $this->database . "." . (string)$field;
				}
			}
			$field_list = implode(",",$expanded_fields);
		}
		else
		{
			$do_join = true;
			$field_list = "*";
		}

		if($do_join)
		{
			foreach($references as $reference)
			{
				$joins .= " JOIN {$reference["table"]} ON {$this->database}.{$reference["referencing_field"]} = {$reference["table"]}.{$reference["referenced_field"]} ";
			}
		}

		$query = sprintf("SELECT $field_list FROM %s ",$this->database).($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"");
		//print $query;
		$stmt = oci_parse(oracle::$_conn, $query);
  		oci_execute($stmt, OCI_DEFAULT);
		
		switch($mode)
		{
		case model::MODE_ASSOC:
			$o_mode = OCI_ASSOC;
			break;
		case model::MODE_ARRAY:	
			$o_mode = OCI_NUM;
			break;
		}
		
		while ($row = oci_fetch_array($stmt,$o_mode + OCI_RETURN_NULLS))
		{
			$rows[] = $this->formatFields($row);
		}
  		
		oci_free_statement($stmt);
		return $rows;		
	}
	
	public function saveData()
	{		
		$fields = implode(",",array_keys($this->data));
		$query = "INSERT INTO $this->database ($fields) VALUES ";
		$values = array();
		foreach(array_keys($this->data) as $field)
		{
			$values[] = "'".$this->data[$field]."'";
		}
		$query .= "(".implode(",",$values).")";
		
		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt);
		oci_free_statement($stmt);
	}
	
	public function update($key_field,$key_value)
	{
		$fields = array_keys($this->data);
		$assignments = array();
		foreach($fields as $field)
		{
			$assignments[] = "$field = '{$this->data[$field]}'";
		}
		$query = "UPDATE {$this->database} SET ".implode(",",$assignments)." WHERE $key_field='$key_value'";
		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt);
		oci_free_statement($stmt);
	}
	
	public function delete($key_field,$key_value)
	{
		$query = "DELETE FROM {$this->database} WHERE $key_field='$key_value'";
		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt);		
		oci_free_statement($stmt);
	}
}
?>
