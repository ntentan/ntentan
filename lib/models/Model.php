<?php
abstract class Model implements ArrayAccess
{
	const MODE_ASSOC = "assoc";
	const MODE_ARRAY = "array";

	protected $services;

	public $name;

	/**
	 *
	 * @var Array
	 */
	protected $fields;

	/*private $properties = array(
		"name"=>null,
		"label"=>null,
		"description"=>null,
		"labels"=>null
	);*/

	public function __construct($name=null, $serviceClass=null)
	{
		//setup the services for this model
		$service_class_name = $serviceClass==null?$this->name."Services":$serviceClass;
		if(!class_exists($service_class_name))
		{
			$service_class_name = "ModelServices";
		}
		$servicesClass = new ReflectionClass($service_class_name);
		$services = $servicesClass->newInstance();
		$this->services = array("instance"=>$services, "class"=>$servicesClass);
	}

	public static function load($model,$serviceClass=null)
	{
		$model_path = "app/modules/".str_replace(".","/",$model)."/";
		$model_name = array_pop(explode(".",$model));
		$serviceClass = $serviceClass==null?$model_path.$model_name."Services.php":$serviceClass.".php";
		return Model::_load($model_path."model.xml",$model_name,$serviceClass);
	}

	private static function _load($model_path,$model_name,$service_class_file=null)
	{
		if(file_exists($service_class_file))
		{
			include_once($service_class_file);
		}
		return new oracle($model_path);
	}

	public static function resolvePath($path)
	{
		$path_array = explode(".",$path);
		$field_name = array_pop($path_array);
		$model_name = implode(".",$path_array);
		return array("model"=>$model_name, "field"=>$field_name);
	}

	/*public function __set($name, $value)
	{
		if(array_key_exists($name,$this->properties))
		{
			$this->properties[$name] = $value;
		}
		else
		{
			throw new Exception("Unknown property $name in Model");
		}
	}*/

	public function getLabels($fields = null, $key = false)
	{
		$labels = array();
		if($fields==null)
		{
			foreach($this->fields as $field)
			{
				$labels[] = $field["label"];
			}
		}
		else
		{
			foreach($fields as $header_field)
			{
				if(array_key_exists((string)$header_field,$this->fields))
				{
					$labels[] = $this->fields[(string)$header_field]["label"];
				}
			}
		}
		return $labels;
	}

	public function getData()
	{
		return $this->data;
	}

	public function formatData()
	{
		$data = $this->data;
		foreach($data as $index => $row)
		{
			foreach($row as $field => $value)
			{
				switch($this->fields[$field]["type"])
				{
					case "enum":
						$data[$index][$field] = $this->fields[$field]["options"][$value];
						break;
					case "date":
						$data[$index][$field] = $value > 0 ? date("l, jS F, Y",$value) : $value;
						break;
					case "time":
						$data[$index][$field] = $value > 0 ? date("g:i:s A",$value) : $value;
						break;
					case "datetime":
						$data[$index][$field] = $value > 0 ? date("jS F, Y g:i:s A",$value) : $value;
						break;
					case "boolean":
						$data[$index][$field] = $value==1 ? "Yes":"No";
				}
			}
		}
		return $data;
	}

	private function service($service_name,$field_name=null,$args=array())
	{
		//$args = func_get_args();
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
				$ret = $this->service($method_name,$field["name"],array($validator["parameter"]));
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

	public function getKeyField()
	{
		foreach($this->fields as $name => $field)
		{
			if($field["key"]=="true") return $name;
		}
	}

	public function save()
	{
		$this->service("preAdd");
		$this->_saveModelData();
		$this->service("postAdd");
	}

	public function get($fields=null,$conditions=null,$mode=Model::MODE_ASSOC,$explicit_relations=false)
	{
		$data = $this->_getModelData($fields,$conditions,$mode,$explicit_relations);
		$this->data = $data;
		return $data;
	}

	public function update($field,$value)
	{
		$this->service("preUpdate");
		$this->_updateData($field,$value);
		$this->service("postUpdate");
	}

	public abstract function getWithField($field,$value);
	protected abstract function _getModelData($fields=null,$conditions=null,$mode=Model::MODE_ASSOC,$explicit_relations=false);
	protected abstract function _saveModelData();
	protected abstract function _updateData($field,$value);
	public abstract function delete($field,$value);

}
?>
