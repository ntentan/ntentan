<?php

/**
 * Description of XMLDefinedSQLDatabaseModel
 *
 * @author james
 */
class XMLDefinedSQLDatabaseModel extends SQLDatabaseModel
{

    /**
     *
     * @var XMLDefinedSQLDatabaseModelHooks
     */
    protected $hooks;

    public static function create($model_path,$model_name,$model_package,$path_prefix)
    {
		//$hooksClass = $hooksClass==null?$model_path.$model_name."Services.php":$hooksClass.".php";
		$hooksFile = $model_path.$model_name."Hooks.php";
        if(file_exists($hooksFile))
        {
        	add_include_path($model_path);
            $hooksClass = $model_name."Hooks"; 
        }
        else
        {
            $hooksClass = "XMLDefinedSQLDatabaseModelHooks";
        }
        return new XMLDefinedSQLDatabaseModel(Application::$packagesPath.$model_path."model.xml", $model_package, $path_prefix,$hooksClass);
    }

    public function __construct($model,$package,$path,$hooksClass)
    {
        $this->connect();
		if(is_file($model))
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

                if(isset($field["value"]))
                {
                    $fieldInfo["value"] = $field["value"];
                }

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
		$this->datastore->storedFields = $this->storedFields;

        $this->hooks = new $hooksClass;
        $this->hooks->setModel($this);

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

    public function preAddHook()
    {
    	$this->hooks->setData($this->getData());
        $this->hooks->preAdd();
        $this->setData($this->hooks->getData());
    }

    public function postAddHook($primaryKeyValue,$data)
    {
        $this->hooks->postAdd($primaryKeyValue,$data);
    }
    
    public function preValidateHook()
    {
        $this->hooks->setData($this->getData());
    	return $this->hooks->preValidate();
    }

    public function postValidateHook($errors)
    {
        $this->hooks->setData($this->getData());
        return $this->hooks->postValidate($errors);
    }

    public function validate()
    {
        $this->hooks->setData($this->getData());
        $ret = $this->hooks->validate();
        if($ret===false)
        {
            $ret = Model::validate();
        }
        return $ret;
    }
}
