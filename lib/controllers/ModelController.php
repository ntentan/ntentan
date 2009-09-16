<?php
/**
 * A model for interacting with the data in modules.
 */
require_once "ControllerPermissions.php";

class ModelController extends Controller implements ControllerPermissions
{
	//! An instance of the model.
	private $model;
	private $model_name;
	private $urlPath;
	private $t;
	protected $table;
	private $app;
	private $toolbar;
	protected $action;

	public function __construct($model)
	{
		$this->model = model::load($model);
		$this->name = $this->model->name;
		$this->model_name = $model;
		$this->t = $t;
		$this->path = $path;
		$this->urlPath = Application::$prefix.str_replace(".","/",$model);
		$localPath = "app/modules/".str_replace(".","/",$model);
		$this->label = $this->model->label;
		$this->description = $this->model->description;

		$this->toolbar = new Toolbar();
		
		if(User::getPermission($this->name."_can_add"))
		{
			$this->toolbar->addLinkButton("New",$this->urlPath."/add");
		}

		if(file_exists($localPath."/app.xml"))
		{
			$this->app = simplexml_load_file($localPath."/app.xml");
		}
		$this->_showInMenu = $this->model->showInMenu=="false"?false:true;
		//$this->action;
		
		$this->table = new ModelTable(Application::$prefix.str_replace(".","/",$this->model_name)."/");
		
		if(User::getPermission($this->name."_can_edit")) 
		{
			$this->table->addOperation("edit","Edit");
		}
		if(User::getPermission($this->name."_can_delete"))
		{
			$this->table->addOperation("delete","Delete","javascript:confirm_redirect('Are you sure you want to delete','%path%/%key%')");
		}
		
		if(User::getPermission($this->name."_can_view")) 
		{
			$this->table->addOperation("view","View");
		}
		
	}

	public function getContents()
	{
		if($this->app == null)
		{
			$fieldNames = $this->model->getFieldNames();
		}
		else
		{
			$fieldNames = $this->app->xpath("/app:app/app:list/app:field");
		}
		
		$this->table->setModel($this->model,$fieldNames);
		return $this->toolbar->render().$this->table->render();
	}

	protected function getForm()
	{
		if($this->app == null)
		{
			$fieldNames = array();
			$fields = $this->model->getFields();
			array_shift($fields);
		}
		else
		{
			$fieldNames = $this->app->xpath("/app:app/app:form/app:field");
			$fields = $this->model->getFields($fieldNames);
		}

		$form = new Form();
		$form->setModel($this->model);
		$names = array_keys($fields);

		for($i=0; $i<count($fields); $i++)
		{
			$field = $fields[$names[$i]];
			if($fieldNames[$i]["renderer"]=="")
			{
				if($field["reference"]=="")
				{
					switch($field["type"])
					{
					case "boolean":
						$element = new CheckBox($field["label"],$field["name"],$field["description"],1);
						break;
						
					case "enum":
						$element = new SelectionList($field["label"],$field["name"]);
						foreach($field["options"] as $option)
						{
							$element->addOption($option["label"],$option["value"]);
						}
						break;
						
					case "date":
						$element = new DateField($field["label"], $field["name"]);
						break;
						
					default:
						$element = new TextField($field["label"],$field["name"],$field["description"]);
						break;
					}
				}
				else
				{
					$element = new ModelField($field["reference"],$field["referenceValue"]);
				}
				
				foreach($field["validators"] as $validator)
				{
					switch($validator["type"])
					{	
					case "required":
						$element->setRequired(true);
						break;
					case "regexp":
						$element->setRegexp((string)$validator);
						break;
					}
				}
			}
			else
			{
				$renderer = (string)$fieldNames[$i]["renderer"];
				$element = new $renderer();
			}
			$form->add($element);
		}

		$form->useAjax(true, false);
		return $form;
	}

	protected static function frameText($width,$text)
	{
		return "<div class='model-frame' style='width:{$width}px'>$text</div>";
	}

	public function add()
	{
			$form = $this->getForm();
			$form->successUrl = $this->urlPath."?notification=Successfully+added+".strtolower($this->label);
			$this->label = "New ".$this->label;
			return ModelController::frameText(400,$form->render());
	}

	public function edit($params)
	{
		$form = $this->getForm();
		$form->setPrimaryKey($this->model->getKeyField(),$params[0]);
		$form->successUrl = $this->urlPath."?notification=Successfully+editted+".strtolower($this->label);
		$this->label = "Edit ".$this->label;
		return ModelController::frameText(400,$form->render());
	}

	public function view($params)
	{
		$form = $this->getForm();
		$form->setShowField(false);
		$form->setPrimaryKey($this->model->getKeyField(),$params[0]);
		$form->successUrl = $this->urlPath."?notification=Successfully+editted+".strtolower($this->label);
		$this->label = "Edit ".$this->label;
		return ModelController::frameText(400,$form->render());
	}

	public function delete($params)
	{
		$this->model->delete($this->model->getKeyField(),$params[0]);
		header("Location: {$this->urlPath}?notification=Successfully+deleted+".strtolower($this->label));
	}
	
	public function getPermissions()
	{
		return array(
			array("label"=>"Can add","name"=>$this->name."_can_add"),
			array("label"=>"Can edit","name"=>$this->name."_can_edit"),
			array("label"=>"Can delete","name"=>$this->name."_can_delete"),
			array("label"=>"Can view","name"=>$this->name."_can_view")
		);
	}

}
?>
