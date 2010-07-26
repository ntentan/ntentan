<?php
require_once "ModelServices.php";

/**
 * A model represents an abstract data storing entity. Models are used to access
 * data. Models can have hooks and 
 * @author james
 *
 */
abstract class Model implements ArrayAccess
{
	const MODE_ASSOC = "assoc";
	const MODE_ARRAY = "array";

	/**
	 * @todo rename this to hooks
	 * @var unknown_type
	 */
	protected $services;

	public $name;
	public $prefix;
	public $package;
	public $database;
	public $label;
	public $description;
	public $showInMenu;
	protected $storedFields;
	public $referencedFields;	

	/**
	 *
	 * @var Array
	 */
	protected $fields;
	public $datastore;

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

	/**
	 * @todo Make it possible to load other types of model objects which could
	 * 		 also be wrapped in different objects.
	 * @param $model
	 * @param $serviceClass
	 * @return Model
	 */
	public static function load($model,$path=null,$serviceClass=null)
	{
		$model_path = $path."app/modules/".str_replace(".","/",$model)."/";
		add_include_path($model_path);
		$model_name = array_pop(explode(".",$model));
		$serviceClass = $serviceClass==null?$model_path.$model_name."Services.php":$serviceClass.".php";
		return Model::_load($model_path."model.xml",$path,$model_name,$model,$serviceClass);
	}
	
	public function escape($text)
	{
		return $this->datastore->escape($text);
	}

	private static function _load($model_path,$path_prefix,$model_name,$model_package,$service_class_file=null)
	{
		/*if(file_exists($service_class_file))
		{
			include_once($service_class_file);
		}*/
		return new SQLDatabaseModel($model_path, $model_package, $path_prefix);//SQLDatabaseModel::createDefaultDriver($model_path,$model_package,$prefix);
	}

	public static function resolvePath($path)
	{
		$path_array = explode(".",$path);
		$field_name = array_pop($path_array);
		$model_name = implode(".",$path_array);
		return array("model"=>$model_name, "field"=>$field_name);
	}

	public function getLabels($fields = null, $key = false)
	{
		$labels = array();
		if($fields==null)
		{
			foreach($this->fields as $field)
			{
				$labels[] = $field["label"];
			}
			if(!$key) array_shift($labels);
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
		return $this->datastore->data;
	}
	
	public function setData($data,$primary_key_field=null,$primary_key_value=null)
	{
		$this->datastore->data = $data;
		if($primary_key_field!="")
		{
			$this->datastore->tempData = $this->getWithField($primary_key_field,$primary_key_value);
		}
		return $this->validate();
	}
	
	public function setResolvableData($data,$primary_key_field=null,$primary_key_value=null)
	{
		$errors = array();
		foreach($data as $key => $value)
		{
			switch($this->fields[$key]["type"])
			{
			case "date":
				$data[$key] = strtotime($value);
				break;
			case "enum":
				$data[$key] = array_search(trim($value),$this->fields[$key]["options"]);
				if($data[$key]===false)
				{
					$errors[$key][] = "Invalid Value '<b>$value</b>'<br/>Possible values may include <ul><li>'".implode("'</li><li>'",$this->fields[$key]["options"])."'</li></ul>";
				}
				break;
			case "reference":
				
			}
		}
		if(count($errors)==0)
		{
			return $this->setData($data,$primary_key_field,$primary_key_value);
		}
		else
		{
			return array("errors"=>$errors);
		}
	}

	private function service($service_name,$field_name=null,$args=array())
	{
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

	public function getFields($fieldList=null, $displayFields = false)
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
	
	public function hasField($fieldName)
	{
		//var_dump(array_keys($this->fields));
		return array_search($fieldName,array_keys($this->fields))===false?false:true;
	}

	public function getKeyField($type="primary")
	{
		foreach($this->fields as $name => $field)
		{
			if($field["key"]==$type) return $name;
		}
	}

	public function save()
	{
		$this->service("preAdd");
		$ret = $this->datastore->save();
		$this->service("postAdd","key",array($ret,$this->getData()));
		return $ret;
	}

	public function getFieldNames($key=false)
	{
		return array_keys($this->fields);
	}
	
	public function get($params=null,$mode=Model::MODE_ASSOC,$explicit_relations=false,$resolve=true)
	{
		$data = $this->datastore->get($params,$mode,$explicit_relations,$resolve);
		//$this->data = $data;
		return $data;
	}

	public function update($field,$value)
	{
		$this->service("preUpdate");
		$this->datastore->update($field,$value);
		$this->service("postUpdate");
	}
	
	public function delete($key_field,$key_value)
	{
		$this->datastore->delete($key_field,$key_value);
	}	

	public static function getModels($path="app/modules")
	{
		$prefix = "app/modules";
		$d = dir($path);
		$list = array();

		// Go through every file in the module directory
		while (false !== ($entry = $d->read()))
		{
			// Ignore certain directories
			if($entry!="." && $entry!=".." && is_dir("$path/$entry"))
			{
				// Extract the path, load the controller and test weather this
				// role has the rights to access this controller.

				$url_path = substr(Application::$prefix,0,strlen(Application::$prefix)-1).substr("$path/$entry",strlen($prefix));
				$module_path = explode("/",substr(substr("$path/$entry",strlen($prefix)),1));
				$module = Controller::load($module_path, false);
				$list = $module->name;
				//$children = $this->generateMenus($role_id,"$path/$entry");
			}
		}
		array_multisort($list,SORT_ASC);
		return $list;
	}
	
	public function offsetGet($offset)
	{
		$data = $this->datastore->get(array("conditions"=>$this->getKeyField()."='$offset'"),Model::MODE_ASSOC,true);
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
	
	public function getWithField($field,$value)
	{
		return $this->get(array("conditions"=>"$field='$value'"),SQLDatabaseModel::MODE_ASSOC,false,false);
	}
	

}
?>
