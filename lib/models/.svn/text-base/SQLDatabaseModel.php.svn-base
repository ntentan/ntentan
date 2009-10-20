<?php
require_once "ModelServices.php";

/**
 * A model represents a basic data storage block. Services built around models
 * can be used to extend models to contains some form of logic which sort of
 * correspond to methods built into the models.
 *
 * @author james
 *
 */
abstract class SQLDatabaseModel extends Model
{

	/**
	 * An instance of the SimpleXML class which is used to store the XML definition
	 * of this model.
	 */
	private $xml;

	/**
	 * The database table in which this model's data is stored.
	 */
	protected $database;
	protected $data;
	protected $tempData;
	protected $fields;
	protected $referencedFields;
	protected $explicitRelations;
	public $label;
	public $description;
	public $showInMenu;

	public function __construct($model)
	{
		if(is_file($model))
		{
			$this->xml = simplexml_load_file($model);
			$this->database = $this->xml["database"];
			$this->label = $this->xml["label"];
			$this->name = $this->xml["name"];
			$this->showInMenu = $this->xml["showInMenu"];
			$description = $this->xml->xpath("/model:model/model:description");
			$this->description = (string)$description[0];

			// Get a list of all the fields from the model into an array
			$this->fields = array();
			$field_xml = $this->xml->xpath("/model:model/model:fields/model:field");

			$this->explicitRelations = $this->xml->xpath("/model:model/model:explicitRelations/model:model");

			foreach($field_xml as $field)
			{
				$description = $field->xpath("model:description");
				$validatorsXML = $field->xpath("model:validator");
				$validators = array();
				$optionsXML = $field->xpath("model:options/model:option");
				$options = array();

				foreach($validatorsXML as $validator)
				{
					$validators[] = array("type"=>(string)$validator["type"],"parameter"=>(string)$validator);
				}
				foreach($optionsXML as $option)
				{
					//$options[] = array("value"=>(string)$option["value"],"label"=>(string)$option);
					$options[(string)$option["value"]] = (string)$option;
				}


				$this->fields[(string)$field["name"]] =
				array(
				"name"=>(string)$field["name"],
				"type"=>(string)$field["type"],
				"label"=>(string)$field["label"],
				"reference"=>(string)$field["reference"],
				"referenceValue"=>(string)$field["referenceValue"],
				"key"=>(string)$field["key"],
				"description"=>(string)$description[0],
				"validators"=>$validators,
				"options"=>$options
				);
			}
		}
		else
		{
			throw new Exception("Could not load XML defined model from $model!");

		}
		parent::__construct();
	}

	private function getXpathArray($xpath)
	{
		$elements_xml = $this->xml->xpath($xpath);
		$elements = array();
		if($elements_xml!=null)
		{
			foreach($elements_xml as $element)
			{
				$elements[] = "".$element[0];
			}
		}
		return $elements;
	}

	public function getReferencedFields()
	{
		if($this->referencedFields == null)
		{
			$references = $this->getXpathArray("/model:model/model:fields/model:field/@reference");
			$fields = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@name");
			$values = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@referenceValue");
			$return = array();

			for($i = 0; $i < count($references); $i++)
			{
				$reference = array();
				$reference["referencing_field"] = $fields[$i];
				$reference["reference"] = $references[$i];
				$reference["referenced_value_field"] = $values[$i];

				$fieldInfo = model::resolvePath($reference["reference"]);
				$tempModel = model::load($fieldInfo["model"]);
				$table = $tempModel->getDatabase();
				$reference["table"] = "".$table;
				$reference["referenced_field"] = $fieldInfo["field"];

				$return[] = $reference;
			}
			$this->referencedFields = $return;
		}
		return $this->referencedFields;
	}

	public function getWithField($field,$value)
	{
  		return $this->query(sprintf("SELECT * FROM %s WHERE $field = '$value'",$this->database));//$rows;
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
					//print (string)$field."<br/>";
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

		//$stmt = oci_parse(oracle::$_conn, $query);
  		//oci_execute($stmt, OCI_DEFAULT);

		/*switch($mode)
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
		}*/

		$rows = $this->query($query,$mode);

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
		//oci_free_statement($stmt);

		return $rows;
	}

	protected function _saveModelData()
	{
		$fields = array();
		$values = array();
		$relatedData = array();

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

		//print $query;

		/*$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt,OCI_DEFAULT);
		oci_free_statement($stmt);*/

		$this->beginTransaction();

		$this->query($query);

		$key_field = $this->getKeyField();
		if(count($relatedData)>0)
		{
		$query = "SELECT MAX({$key_field}) as $key_field FROM $this->database";

		/*$stmt = oci_parse(oracle::$_conn, $query);
		oci_execute($stmt,OCI_DEFAULT);
		$key_value = oci_fetch_array($stmt,OCI_ARRAY + OCI_RETURN_NULLS);
		$key_value = $key_value[0];
		oci_free_statement($stmt);*/

		$key_value = $this->query($query);

		// Save related data

		foreach($relatedData as $database => $data)
		{
			$model = Model::load($database);
			foreach($data as $row)
			{
				$row[$key_field] = $key_value;
				$model->setData($row);
				$model->save();
			}
		}
		}

		//oci_commit(oracle::$_conn);
		$this->endTransaction();
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

		$this->query($query);

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
		$this->query($query);
	}

	public static function loadXML($model_path,$serviceClass)
	{
		return Model::_load($model_path,$serviceClass);
	}

	public function getDatabase()
	{
		return $this->database;
	}


	public function getFieldNames($key=false)
	{
		return array_keys($this->fields);
	}

	public function checkTemp($field,$value,$index=0)
	{
		return $this->tempData[$index][$field] == $value;
	}

	public function offsetGet($offset)
	{
		$data = $this->_getModelData(null,$this->getKeyField()."='$offset'",Model::MODE_ASSOC,true);
		return $data;
	}

	public function offsetSet($offset,$value)
	{

	}

	public function offsetExists($offset)
	{

	}

	public function offsetUnset($offset)
	{

	}

	public static function createDefaultDriver($model)
	{
		require "app/config.php";
		//$db_connection_info = array("username"=>$db_user, "password"=>$db_password, "host"=>$db_host, "database"=>$db_name);
		/*$method = new ReflectionMethod($db_driver,"connect");
		$method->invoke(null,$db_connection_info);*/
		$class = new ReflectionClass($db_driver);
		return $class->newInstance($model);
	}

	protected abstract function beginTransaction();
	protected abstract function endTransaction();
	protected abstract function query($query,$mode=SQLDatabaseModel::MODE_ARRAY);
}
?>
