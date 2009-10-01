<?php
class oracle extends SQLDatabaseModel
{
	protected static $_conn = null;
	protected static $_s_conn = null;

	public static function connect($username,$password,$host,$database)
	{
		$db = "$host/$database";
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
  		$stmt = oci_parse(oracle::$_conn, sprintf("SELECT * FROM %s WHERE $field = '$value'",$this->database));
  		oci_execute($stmt, OCI_DEFAULT);
	  	while ($row = oci_fetch_assoc($stmt))
	  	{
			$rows[] = $this->formatFields($row);
  		}
		oci_free_statement($stmt);
  		return $rows;
	}

	protected function _getModelData($fields = null,$conditions=null,$mode=model::MODE_ASSOC, $explicit_relations=false)
	{
		$rows = array();

		// Get information about all referenced models and pull out all
		// the required information as well as build up the join parts
		// of the query.
		$references = $this->getReferencedFields();
		$joins = "";
		//$do_join = count($references)>0?true:false;


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
						$do_join = true;
						$referred = true;
						$expanded_fields[] = $reference["table"].".".$reference["referenced_value_field"];
						break;
					}
				}
				if(!$referred)
				{
					$expanded_fields[]= (count($references)>0?$this->database.".":"").(string)$field;
				}
			}
			$field_list = implode(",",$expanded_fields);
		}
		else
		{
			$field_list = "*";
		}

		foreach($references as $reference)
		{
			$joins .= " LEFT JOIN {$reference["table"]} ON {$this->database}.{$reference["referencing_field"]} = {$reference["table"]}.{$reference["referenced_field"]} ";
		}

		$query = sprintf("SELECT $field_list FROM %s ",$this->database).($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"");
		$stmt = oci_parse(oracle::$_conn, $query);
  		oci_execute($stmt, OCI_DEFAULT);

		switch($mode)
		{
		case SQLDatabaseModel::MODE_ASSOC:
			$o_mode = OCI_ASSOC;
			break;
		case SQLDatabaseModel::MODE_ARRAY:
			$o_mode = OCI_NUM;
			break;
		}

		while ($row = oci_fetch_array($stmt,$o_mode + OCI_RETURN_NULLS))
		{
			$rows[] = $this->formatFields($row);
		}

		// Retrieve all explicitly related data
		if($explicit_relations)
		{
			foreach($this->explicitRelations as $explicitRelation)
			{
				foreach($rows as $i => $row)
				{
					$model = Model::load((string)$explicitRelation);
					$data = $model->get(null,$this->getKeyField()."='".$row[$this->getKeyField()]."'");
					//print $this->getKeyField()."='".$row[$this->getKeyField()]."'";
					$rows[$i][(string)$explicitRelation] = $data;
					
				}
			}
		}
		oci_free_statement($stmt);
		
		return $rows;
	}

	protected function _saveModelData()
	{
		$fields = array();
		$values = array();
		$relatedData = array();
		/*foreach(array_keys($this->data) as $field)
		{
			$values[] = "'".$this->data[$field]."'";
		}*/

		foreach($this->data as $field => $value)
		{
			if(is_array($value))
			{
				$relatedData[$field] = $value;
			}
			else
			{
				$fields[] = $field;
				$values[] = "'".$value."'";
			}
		}

		$fields = implode(",",$fields);
		$query = "INSERT INTO $this->database ($fields) VALUES ";
		$query .= "(".implode(",",$values).")";

		print $query;

		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt,OCI_DEFAULT);
		oci_free_statement($stmt);

		$key_field = $this->getKeyField();
		$query = "SELECT MAX({$key_field}) as $key_field FROM $this->database";

		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt,OCI_DEFAULT);
		$key_value = oci_fetch_array($stmt,OCI_ARRAY + OCI_RETURN_NULLS);
		$key_value = $key_value[0];
		oci_free_statement($stmt);

		// Save related data

		foreach($relatedData as $database => $data)
		{
			$model = Model::load($database);
			foreach($data as $row)
			{
				$row[$key_field] = $key_value;
				$model->setData($row);
				$model->save();
				/*$fields = implode(",",array_keys($row));
				$values = implode("','",$row);
				$query = "INSERT INTO $database ($fields) VALUES ";
				$query .= "('".$values."')";

				print $query."<br/>";

				$stmt = oci_parse(oracle::$_conn, $query);
				oci_execute($stmt,OCI_DEFAULT);
				oci_free_statement($stmt);*/
			}
		}

		oci_commit(oracle::$_conn);
	}

	/**
	 *
	 * @see lib/models/model#update($field, $value)
	 */
	protected function _updateData($key_field,$key_value)
	{
		
		$fields = array(); // array_keys($this->data);
		$relatedData = array();
		$assignments = array();
		
		foreach($this->data as $field => $value)
		{
			if(is_array($value))
			{
				$relatedData[$field] = $value;
			}
			else
			{
				$fields[] = $field;
				$assignments[] = "$field = '".$value."'";
			}
		}		
		
		$query = "UPDATE {$this->database} SET ".implode(",",$assignments)." WHERE $key_field='$key_value'";
		$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt);
		oci_free_statement($stmt);

		foreach($relatedData as $database => $data)
		{
			$model = Model::load($database);
			$model->delete($key_field,$key_value);
			foreach($data as $row)
			{
				$row[$key_field] = $key_value;
				$model->setData($row);
				$model->save();
			}
		}
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
