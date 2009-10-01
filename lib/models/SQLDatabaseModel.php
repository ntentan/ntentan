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

	public static function loadXML($model_path,$serviceClass)
	{
		return Model::_load($model_path,$serviceClass);
	}

	public function getDatabase()
	{
		return $this->database;
	}

	/*public function getLabels($fields=null,$key=false)
	{
		if($fields==null)
		{
			$labels = $this->getXpathArray("/model:model/model:fields/model:field/@label");
			if(!$key) array_shift($labels);
		}
		else
		{
			$labels = array();
			foreach($fields as $field)
			{
				$label = $this->getXpathArray("/model:model/model:fields/model:field[@name='$field']/@label");
				$labels[] = $label[0];
			}
		}
		return $labels;
	}*/

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
	}*/



	public function getFieldNames($key=false)
	{
		return array_keys($this->fields);
	}

	/*public function getFields($fieldList=null)
	{
		if($fieldList == null)
		{
			return $this->fields;
		}
		else
		{
			$fields=array();
			foreach($fieldList as $field)
			{
				$fields[] = $this->fields[(string)$field];
			}
			return $fields;
		}
	}*/

	public function setData($data,$primary_key_field=null,$primary_key_value=null)
	{
		$this->data = $data;
		//var_dump($primary_key_field);
		if($primary_key_field!="")
		{
			$this->tempData = $this->getWithField($primary_key_field,$primary_key_value);
		}
		//var_dump($this->tempData);
		return $this->validate();
	}

	public function checkTemp($field,$value,$index=0)
	{
		//var_dump($this->tempData);
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
}
?>
