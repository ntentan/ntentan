<?php
class SQLDatabaseModel extends Model
{
	public function __construct(/*$model,$package,$path*/)
	{
		/*if(is_file($model))
		{
			$this->prefix = $path;
			$this->package = $package;
			$this->xml = simplexml_load_file($model);
			$this->database = $this->xml["database"];
			$this->label = $this->xml["label"];
			$this->name = $this->xml["name"];
			$this->showInMenu = $this->xml["showInMenu"];
			$description = $this->xml->xpath("/model:model/model:description");
			$this->description = (string)$description[0];

			// Get a list of all the fields from the model into an array
			$this->fields = array();
			$field_xml = $this->xml->xpath("/model:model/model:fields/model:field");//[@type!='displayReference']");
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

				$fieldInfo =
				array(
				"name"=>(string)$field["name"],
				"type"=>(string)$field["type"],
				"label"=>(string)$field["label"],
				"reference"=>(string)$field["reference"],
				"referenceValue"=>$this->datastore->concatenate(explode(",", (string)$field["referenceValue"])),
				"key"=>(string)$field["key"],
				"description"=>(string)$description[0],
				"validators"=>$validators,
				"options"=>$options
				);

				$this->fields[(string)$field["name"]] = $fieldInfo;

				if($field["type"]!="displayReference")
				{
					$this->storedFields[(string)$field["name"]] = $fieldInfo;
				}
			}
		}
		else
		{
			throw new Exception("Could not load XML defined model from $model!");

		}

		$references = $this->getXpathArray("/model:model/model:fields/model:field/@reference");
		$fields = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@name");
		$values = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@referenceValue");
		$return = array();

		for($i = 0; $i < count($references); $i++)
		{
			$reference = array();
			$reference["referencing_field"] = $fields[$i];
			$reference["reference"] = $references[$i];
			$reference["referenced_value_field"] = $this->datastore->concatenate(explode(",",$values[$i]));
			
			$fieldInfo = model::resolvePath($reference["reference"]);
			$tempModel = model::load($fieldInfo["model"],$this->prefix);
			$table = $tempModel->getDatabase();
			$reference["table"] = (string)$table;
			$reference["referenced_field"] = $fieldInfo["field"];

			$return[] = $reference;
		}
		$this->referencedFields = $return;
		$this->datastore->referencedFields = $this->referencedFields;
		$this->datastore->explicitRelations = $this->explicitRelations;
		$this->datastore->fields = $this->fields;
		$this->datastore->database = $this->database;
		$this->datastore->storedFields = $this->storedFields;*/
	
		parent::__construct($this->datastore->name);
	}
	
	protected function connect()
	{
		require "app/config.php";
		require_once "lib/models/datastores/databases/$db_driver/$db_driver.php";
		$class = new ReflectionClass($db_driver);
		$this->datastore = $class->newInstance($model,$package,$path);		
	}
	
	public function getDatabase()
	{
		return $this->datastore->getDatabase();
	}
	
	public function getSearch($searchValue,$field)
	{
		return $this->datastore->getSearch($searchValue,$field);
	}
}

//require_once "ModelServices.php";
/**
 * A model represents a basic data storage block. Services built around models
 * can be used to extend models to contains some form of logic which sort of
 * correspond to methods built into the models.
 *
 * @author james
 *
 */

/*abstract class SQLDatabaseModel extends Model
{*/

	/**
	 * An instance of the SimpleXML class which is used to store the XML definition
	 * of this model.
	 */
//	private $xml;

	/**
	 * The database table in which this model's data is stored.
	 */
	
	/*protected $database;
	protected $data;
	protected $tempData;
	protected $fields;
	protected $referencedFields;
	protected $explicitRelations;
	public $label;
	public $description;
	public $showInMenu;
	public $package;
	public $prefix;
	protected $storedFields;

	public function __construct($model,$package="",$prefix="")
	{
		if(is_file($model))
		{
			$this->prefix = $prefix;
			$this->package = $package;
			$this->xml = simplexml_load_file($model);
			$this->database = $this->xml["database"];
			$this->label = $this->xml["label"];
			$this->name = $this->xml["name"];
			$this->showInMenu = $this->xml["showInMenu"];
			$description = $this->xml->xpath("/model:model/model:description");
			$this->description = (string)$description[0];

			// Get a list of all the fields from the model into an array
			$this->fields = array();
			$field_xml = $this->xml->xpath("/model:model/model:fields/model:field");//[@type!='displayReference']");
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

				$fieldInfo =
				array(
				"name"=>(string)$field["name"],
				"type"=>(string)$field["type"],
				"label"=>(string)$field["label"],
				"reference"=>(string)$field["reference"],
				"referenceValue"=>$this->concatenate(explode(",", (string)$field["referenceValue"])),
				"key"=>(string)$field["key"],
				"description"=>(string)$description[0],
				"validators"=>$validators,
				"options"=>$options
				);

				$this->fields[(string)$field["name"]] = $fieldInfo;

				if($field["type"]!="displayReference")
				{
					$this->storedFields[(string)$field["name"]] = $fieldInfo;
				}
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
	}*/

	/**
	 *
	 * @return unknown_type
	 */
	/*public function getReferencedFields()
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
				$reference["referenced_value_field"] = $this->concatenate(explode(",",$values[$i]));

				$fieldInfo = model::resolvePath($reference["reference"]);
				$tempModel = model::load($fieldInfo["model"],$this->prefix);
				$table = $tempModel->getDatabase();
				$reference["table"] = (string)$table;
				$reference["referenced_field"] = $fieldInfo["field"];

				$return[] = $reference;
			}
			$this->referencedFields = $return;
		}
		return $this->referencedFields;
	}

	public function getWithField($field,$value)
	{
		//return $this->query(sprintf("SELECT * FROM %s WHERE $field = '$value'",$this->database));//$rows;
		return $this->get(array("conditions"=>"$field='$value'"),SQLDatabaseModel::MODE_ASSOC,false,false);
	}

	public function getExpandedFieldList($fields,$references,$resolve=true)
	{
		if($fields == null) $fields = array_keys($this->storedFields);

		$expanded_fields = array();
		$r_expanded_fields = array();

		//Go through all the fields in the system.
		foreach($fields as $field)
		{
			$referred = false;
			foreach($references as $reference)
			{
				//print (string)$field."<br/>";
				//var_dump($reference);die();
				if($reference["referencing_field"] == (string)$field)
				{
					$do_join = true;
					$referred = true;
					$r_expanded_fields[$field] = $reference["table"].".".$reference["referenced_value_field"];
					$expanded_fields[$field] = $reference["table"].".".$reference["referenced_value_field"]." as \"{$reference["referencing_field"]}\"";
					break;
				}
			}
			if(!$referred)
			{
				$r_expanded_fields[$field]=(count($references)>0?$this->database.".":"").(string)$field;
				if($resolve)
					$expanded_fields[$field]= $this->formatField($this->fields[$field],(count($references)>0?$this->database.".":"").(string)$field);
				else
					$expanded_fields[$field]=$r_expanded_fields[$field]." as \"{$this->fields[$field]["name"]}\"";
				//var_dump($this->fields[$field]['type']);
			}
		}
		$field_list = implode(",",$expanded_fields);
		return array("fields"=>$field_list,"expandedFields"=>$r_expanded_fields,"doJoin"=>$do_join);
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
				$values[] = "'".$this->escape($value)."'";
			}
		}

		$fields = implode(",",$fields);
		$query = "INSERT INTO $this->database ($fields) VALUES ";
		$query .= "(".implode(",",$values).")";


		$this->beginTransaction();

		$this->query($query);


		if(count($relatedData)>0)
		{
			$key_field = $this->getKeyField();
			$query = "SELECT MAX({$key_field}) as \"$key_field\" FROM $this->database";
			$key_value = $this->query($query);
				
			// Save related data
			foreach($relatedData as $database => $data)
			{
				$model = Model::load($database);
				foreach($data as $row)
				{
					$row[$key_field] = $key_value[0][$key_field];
					$model->setData($row);
					$model->save();
				}
			}
		}

		//oci_commit(oracle::$_conn);
		$this->endTransaction();
		return $key_value[0][$key_field];
	}

	/**
	 *
	 * @see lib/models/model#update($field, $value)
	 */
	/*protected function _updateData($key_field,$key_value)
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
				$assignments[] = "$field = '".$this->escape($value)."'";
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

	public function checkTemp($field,$value,$index=0)
	{
		return $this->tempData[$index][$field] == $value;
	}

	public function offsetGet($offset)
	{
		$data = $this->_getModelData(array("conditions"=>$this->getKeyField()."='$offset'"),Model::MODE_ASSOC,true);
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

	public static function createDefaultDriver($model,$package,$path)
	{
		require "app/config.php";
		$class = new ReflectionClass($db_driver);
		return $class->newInstance($model,$package,$path);
	}
	
	public function sqlFunctionLENGTH($field)
	{
		return "LENGTH($field)";
	}
	
	public function sqlFunctionMAX($field)
	{
		return "MAX($field)";
	}
	
	public function applySqlFunctions($field,$functions,$index=0)
	{
		if(!isset($functions[$index])) return $field;
		$method = new ReflectionMethod(__CLASS__,"sqlFunction$functions[$index]");
		if(!isset($functions[$index+1]))
		{
			return $method->invoke($this,$field);
		}
		else
		{
			return $this->applySqlFunctions($method->invoke($this,$field),$functions,$index+1); 
		}
	}
	
	
	public static function getMulti($params,$mode=SQLDatabaseModel::MODE_ASSOC)
	{
		//Load all models
		$fields = array();
		$field_infos = array();
		$models = array();
		
		foreach($params["fields"] as $field)
		{
			$fieldreferences = explode(",",$field); 
			if(count($fieldreferences)==1)
			{
				$fields[]=(string)$field; 
				$field_infos[] = Model::resolvePath((string)$field);
			}
			else
			{
				$fields[] = $fieldreferences;
				foreach($fieldreferences as $ref)
				{
					$infos[] = Model::resolvePath((string)$ref); 
				}
				$field_infos[] = $infos;
			}
		}
		
		foreach($fields as $i=>$field)
		{
			if(is_array($field))
			{
				foreach($field_infos[$i] as $info)
				{
					if(array_search($info["model"],array_keys($models))===false)
					{
						$models[$info["model"]] = Model::load($info["model"]);
					}
				}
			}
			else
			{
				if(array_search($field_infos[$i]["model"],array_keys($models))===false)
				{
					$models[$field_infos[$i]["model"]] = Model::load($field_infos[$i]["model"]);
				}
			}
		}
		
		//Buld the query
		$query = "SELECT ";
		$fieldList = array();
		$functions = $params["global_functions"];
		foreach($fields as $i => $field)
		{
			$field_info = $field_infos[$i];
			if(is_array($field))
			{
				$concatFieldList = array();
				foreach($field_info as $info)
				{
					$fieldData = $models[$info["model"]]->getFields(array($info["field"]));
					$concatFieldList[] = $models[$info["model"]]->formatField($fieldData[0],$models[$info["model"]]->getDatabase().".".$info["field"],false);
				}
				$fieldList[] = $models[$info["model"]]->applySqlFunctions($models[$info["model"]]->concatenate($concatFieldList),$functions);
			}
			else
			{
				$fieldData = $models[$field_infos[$i]["model"]]->getFields(array($field_info["field"]));
				$fieldList[] = $models[$field_info["model"]]->formatField($fieldData[0],$models[$field_info["model"]]->getDatabase().".".$field_info["field"],true,$functions);
			}
		}
		
		$tableList = array();
		foreach($models as $model)
		{
			$tableList[] = $model->getDatabase();
		}
		
		$joinConditions = array();
		foreach($models as $model)
		{
			foreach($models as $other_model)
			{
				if($model->name == $other_model->name) continue;
				if($model->hasField($other_model->getKeyField()))
				{
					$joinConditions[] = "{$model->getDatabase()}.{$other_model->getKeyField()}={$other_model->getDatabase()}.{$other_model->getKeyField()}";
				}
			}
		}
		
		$query.=implode(",",$fieldList)." FROM ".implode(",",$tableList);
		
		if(count($joinConditions)>0)
		{
			$query .= " WHERE (".implode(" AND ",$joinConditions).")";
		}
		
		$query.=(strlen($params["conditions"])>0?" AND (".$params["conditions"].")":"");
		
		//print $query."<br/><hr/>";
		if($params["sort_field"]!="")
		{
			$query .= " ORDER BY {$params["sort_field"]} {$params["sort_type"]}";
		}		
		
		return $other_model->query($query,$mode);
	}

	protected abstract function beginTransaction();
	protected abstract function endTransaction();
	protected abstract function query($query,$mode=SQLDatabaseModel::MODE_ARRAY);
	public abstract function concatenate($fields);
	public abstract function formatField($field,$value);
	public abstract function escape($string);
	public abstract function getSearch($searchValue,$field);	
}*/
?>
