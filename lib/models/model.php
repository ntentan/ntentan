<?php
require_once "ModelServices.php";

abstract class model
{
	private $xml;
	public $name;
	protected $database;
	protected $data;
	protected $fields;
	protected $referencedFields;
	public $label;
	public $description;
	private $services;
	public $showInMenu;

	const MODE_ASSOC = "assoc";
	const MODE_ARRAY = "array";

	public function __construct($model="model.xml")
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
				$options[] = array("value"=>(string)$option["value"],"label"=>(string)$option);
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

		//setup the services for this model
		$service_class_name = $this->name."Services";
		if(!class_exists($service_class_name))
		{
			$service_class_name = "ModelServices";
		}
		$servicesClass = new ReflectionClass($service_class_name);
		$services = $servicesClass->newInstance();
		$this->services = array("instance"=>$services, "class"=>$servicesClass);
	}

	public static function load($model,$type="oracle")
	{
		$model_path = "app/modules/".str_replace(".","/",$model)."/";
		$model_name = array_pop(explode(".",$model));
		if(file_exists($model_path.$model_name."Services.php"))
		{
			include_once($model_path.$model_name."Services.php");
		}
		return new oracle($model_path."model.xml");
	}

	public function getDatabase()
	{
		return $this->database;
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

	public function getLabels($fields=null,$key=false)
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
	}

	public static function resolvePath($path)
	{
		$path_array = explode(".",$path);
		$field_name = array_pop($path_array);
		$model_name = implode(".",$path_array);
		return array("model"=>$model_name, "field"=>$field_name);
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

	public function getKeyField()
	{
		$key_field = $this->xml->xpath("/model:model/model:fields/model:field[@key='true']/@name");
		return (string)$key_field[0]["name"];
	}

	public function getFieldNames($key=false)
	{
		return array_keys($this->fields);
	}

	public function getFields($fieldList=null)
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
	}

	public function setData($data)
	{
		$this->data = $data;
		return $this->validate();
	}

	public function getData()
	{
		return $this->data;
	}

	private function service($service_name,$field_name=null,$args=array())
	{
		$args = func_get_args();
		$this->services["instance"]->setModel($this);
		$ret = false;

		if(method_exists($this->services["instance"],$service_name))
		{
			$method = $this->services["class"]->GetMethod($service_name);
			$ret = $method->invoke($this->services["instance"],$field_name,$args);
		}

		return $ret;
	}

	public function validate()
	{
		$fields = $this->getFields();
		$errors = array();
		$numErrors = 0;

		foreach($fields as $field)
		{
			$errors[$field["name"]] = array();
			foreach($field["validators"] as $validator)
			{
				$method_name = "validator_".$validator["type"];
				$ret = $this->service($method_name,$field["name"]);
				if($ret !== true)
				{
					$errors[$field["name"]][] = $ret;
					$numErrors++;
				}
			}
		}

		if($numErrors>0)
		{
			return array("errors"=>$errors,"numErrors"=>$numErrors);
		}
		else
		{
			return true;
		}
	}

	public function save()
	{
		$this->service("preAdd");
		$this->saveData();
		$this->service("postAdd");
	}

	public abstract function getWithField($field,$value);
	public abstract function get($fields=null,$mode=model::MODE_ASSOC);
	protected abstract function saveData();
	public abstract function update($field,$value);
	public abstract function delete($field,$value);

}
?>
