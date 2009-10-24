<?php
/**
 * A model represents an abstract data storing entity.
 * @author james
 *
 */
abstract class Model implements ArrayAccess
{
	const MODE_ASSOC = "assoc";
	const MODE_ARRAY = "array";

	protected $services;

	public $name;
	public $prefix;

	/**
	 *
	 * @var Array
	 */
	protected $fields;

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
	 * 
	 * @param $model
	 * @param $serviceClass
	 * @return Model
	 */
	public static function load($model,$path=null,$serviceClass=null)
	{
		$model_path = $path."app/modules/".str_replace(".","/",$model)."/";
		$model_name = array_pop(explode(".",$model));
		$serviceClass = $serviceClass==null?$model_path.$model_name."Services.php":$serviceClass.".php";
		return Model::_load($model_path."model.xml",$path,$model_name,$model,$serviceClass);
	}

	private static function _load($model_path,$prefix,$model_name,$model_package,$service_class_file=null)
	{
		if(file_exists($service_class_file))
		{
			include_once($service_class_file);
		}
		return SQLDatabaseModel::createDefaultDriver($model_path,$model_package,$prefix);
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
		return $this->data;
	}

	/*public function formatData()
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
	}*/
	
	public function setData($data,$primary_key_field=null,$primary_key_value=null)
	{
		$this->data = $data;
		if($primary_key_field!="")
		{
			$this->tempData = $this->getWithField($primary_key_field,$primary_key_value);
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
		$ret = $this->_saveModelData();
		$this->service("postAdd","key",array($ret,$this->getData()));
		return $ret;
	}

	public function get($params=null,$mode=Model::MODE_ASSOC,$explicit_relations=false,$resolve=true)
	{
		$data = $this->_getModelData($params,$mode,$explicit_relations,$resolve);
		//$this->data = $data;
		return $data;
	}

	public function update($field,$value)
	{
		$this->service("preUpdate");
		$this->_updateData($field,$value);
		$this->service("postUpdate");
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
		
		//var_dump($fields);
		//var_dump($field_infos);
		
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

	public abstract function getWithField($field,$value);
	protected abstract function _getModelData($params=null,$mode=Model::MODE_ASSOC,$explicit_relations=false,$resolve=true);
	protected abstract function _saveModelData();
	protected abstract function _updateData($field,$value);
	public abstract function delete($field,$value);
	public abstract function escape($string);
	public abstract function getSearch($searchValue,$field);
	public abstract function concatenate($fields);
	public abstract function formatField($field,$value);

}
?>
