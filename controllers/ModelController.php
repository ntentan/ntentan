<?php
//require_once "ControllerPermissions.php";

/**
 * A controller for interacting with the data in models. This controller is loaded
 * automatically when the path passed to the Controller::load method points to
 * a module which contains only a model definition. This controller provides
 * an interface through which the user can add, edit, delete and also perform
 * other operations on the data store in the model.
 *
 * Extra configuration could be provided through an app.xml file which would be
 * found in the same module path as the model that this controller is loading.
 * This XML file is used to describe what fields this controller should display
 * in the table view list. It also specifies which fields should be displayed
 * in the form.
 *
 * A custom form class could also be provided for this controller. This form
 * class should be a subclass of the Form class. The name of the file in which
 * this class is found should be modelnameForm.php (where modelname represents
 * the actual name of the model). For exampld of your model is called users then
 * the custom form that this controller can pick up should be called usersForm.
 *
 * @author james
 */
class ModelController extends Controller// implements ControllerPermissions
{
	/**
	 * An instance of the model that this controller is linked to.
	 * @var Model
	 */
	protected $model;

	/**
	 * The name of the model that this controller is linked to.
	 */
	private $model_name;

	/**
	 * The URL path through which this controller's model can be accessed.
	 */
	public $urlPath;

	/**
	 * The local pathon the computer through which this controllers model can be
	 * accessed.
	 */
	protected $localPath;

	/**
	 * An instance of the template engine.
	 * @todo Take this variable out so that the output is handled by a third party;
	 */
	private $t;

	/**
	 * An instance of the Table class that is stored in here for the purpose
	 * of displaying and also manipulating the model's data.
	 */
	protected $table;

	/**
	 * An instance of the simple xml object that is used to represent the app.xml
	 * file which contains extra directives for the ModelController.
	 */
	private $app;

	/**
	 * An instance of the Toolbar class. This toolbar is put on top of the list
	 * which is used to display the model.
	 */
	private $toolbar;
	

	protected $action;
	public $listConditions;
	public $fieldNames = array();
	protected $callbackMethod = "ModelController::callback";

	/**
	 * Constructor for the ModelController
	 * @param $model An instance of the Model class which represents the model to be used.
	 */
	public function __construct($model)
	{
		$this->model = model::load($model);
		$this->name = $this->model->name;
		$this->model_name = $model;
		$this->t = $t;
		$this->path = $path;
		$this->urlPath = Application::$prefix.str_replace(".","/",$model);
		$this->localPath = "app/modules/".str_replace(".","/",$model);
		$this->label = $this->model->label;
		$this->description = $this->model->description;
		Application::setTitle($this->label);

		$this->toolbar = new Toolbar();
		$this->table = new ModelTable(Application::$prefix.str_replace(".","/",$this->model_name)."/");
		$this->table->useAjax = true;
				
		$this->_showInMenu = $this->model->showInMenu=="false"?false:true;
		
		if(file_exists($this->localPath."/app.xml"))
		{
			$this->app = simplexml_load_file($this->localPath."/app.xml");
		}
	}
	
	
	private function setupList()
	{
		if(User::getPermission($this->name."_can_add"))
		{
			$this->toolbar->addLinkButton("New",$this->urlPath."/add");
		}

		if(User::getPermission($this->name."_can_export"))
		{
			$exportButton = new MenuButton("Export");
			$exportButton->addMenuItem("PDF","#","NTHC.openWindow('".$this->urlPath."/export/pdf')");
			$exportButton->addMenuItem("Data","#","NTHC.openWindow('".$this->urlPath."/export/csv')");
			$exportButton->addMenuItem("Template","#","NTHC.openWindow('".$this->urlPath."/export/csv/template')");
			$exportButton->addMenuItem("HTML","#","NTHC.openWindow('".$this->urlPath."/export/html')");
			$this->toolbar->add($exportButton);//addLinkButton("Export",$this->urlPath."/export");
		}

		if(User::getPermission($this->name."_can_import"))
		{
			$this->toolbar->addLinkButton("Import",$this->urlPath."/import");
		}
		
		$this->toolbar->addLinkButton("Search","#")->linkAttributes="onclick=\"ntentan.tapi.showSearchArea('{$this->table->name}')\"";
	
		if(User::getPermission($this->name."_can_edit"))
		{
			$this->table->addOperation("edit","Edit");
		}
		if(User::getPermission($this->name."_can_delete"))
		{
			$this->table->addOperation("delete","Delete","javascript:ntentan.confirmRedirect('Are you sure you want to delete','%path%/%key%')");
			$this->toolbar->addLinkButton("Delete","javascript:ntentan.tapi.remove(\"{$this->table->name}\")");
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
            $concatenatedLabels = $this->app->xpath("/app:app/app:list/app:field/@label");
		}
		
		foreach($fieldNames as $i => $fieldName)
		{
			$fieldNames[$i] = (string)$fieldName;
		}
		
		if(count($this->fieldNames)>0) $fieldNames = $this->fieldNames;
		
		$this->setupList();
		
		$this->table->setModel($this->model,array("fields"=>$fieldNames,"conditions"=>$this->listConditions),$concatenatedLabels);
		return $this->toolbar->render().$this->table->render();
	}

	/**
	 * Returns the form that this controller uses to manipulate the data stored
	 * in its model. As stated earlier the form is either automatically generated
	 * or it is loaded from an existing file which is located in the same
	 * directory as the model and bears the model's name.
	 *
	 * @return Form
	 */
	protected function getForm()
	{
		// Load a local form if it exists.
		if(is_file($this->localPath."/".$this->name."Form.php"))
		{
			include_once $this->localPath."/".$this->name."Form.php";
			$formclass = $this->name."Form";
			$form = new $formclass();
			$form->setModel($this->model);
		}
		else
		{
			// Generate a form automatically
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
								$element = new Checkbox($field["label"],$field["name"],$field["description"],1);
								break;

							case "enum":
								$element = new SelectionList($field["label"],$field["name"]);
								foreach($field["options"] as $value => $option)
								{
									$element->addOption($option, $value);
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
								$element->setRegexp((string)$validator["parameter"]);
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

			$form->addAttribute("style","width:50%");
			$form->useAjax(true, false);
		}
		return $form;
	}

	/**
	 * Action method for adding new items to the model database.
	 * @return String
	 */
	public function add()
	{
		$form = $this->getForm();
		$this->label = "New ".$this->label;
		$form->setCallback($this->callbackMethod/*"ModelController::callback"*/,
			array(
				"action"=>"add",
				"instance"=>$this,
				"success_message"=>"Added new ".$this->model->name,
				"form"=>$form
			)
		);
		return $form->render(); //ModelController::frameText(400,$form->render());
	}

	public static function callback($data,&$form,$c,$redirect=true)
	{
		switch($c["action"])
		{
		case "add":
			$return = $c["instance"]->model->setData($data);
			if($return===true)
			{
				$id = $c["instance"]->model->save();
				User::log($c["success_message"],$data);
				if($redirect)
				{
					header("Location: ".$c["instance"]->urlPath."?notification=".$c["success_message"]);
				}
				else
				{
					return true;
				}
			}
			else
			{
				$fields = array_keys($return["errors"]);
				foreach($fields as $field)
				{
					foreach($return["errors"][$field] as $error)
					{
						$element = $c["form"]->getElementByName($field);
						$element->addError(str_replace("%field_name%",$element->getLabel(),$error));
					}
				}
			}
			break;

		case "edit":
			$return = $c["instance"]->model->setData($data,$c["key_field"],$c["key_value"]);
			if($return===true)
			{
				$c["instance"]->model->update($c["key_field"],$c["key_value"]);
				User::log($c["success_message"],$data);
				if($redirect)
				{
					header("Location: ".$c["instance"]->urlPath."?notification=".$c["success_message"]);
				}
				else
				{
					return true;
				}
			}
			else
			{
				$fields = array_keys($return["errors"]);
				foreach($fields as $field)
				{
					foreach($return["errors"][$field] as $error)
					{
						$element = $c["form"]->getElementByName($field);
						$element->addError(str_replace("%field_name%",$element->getLabel(),$error));
					}
				}
			}
			break;
		}
	}

	/**
	 * Action method for editing items already in the database.
	 * @param $params An array of parameters that the system uses.
	 * @return string
	 */
	public function edit($params)
	{
		$form = $this->getForm();
		$data = $this->model->get(array("conditions"=>$this->model->getKeyField()."='".$params[0]."'"),SQLDatabaseModel::MODE_ASSOC,true,false);
		$form->setData($data[0]);
		$this->label = "Edit ".$this->label;
		$form->setCallback($this->callbackMethod/*"ModelController::callback"*/,
			array(
				"action"=>"edit",
				"instance"=>$this,
				"success_message"=>"Edited ".$this->model->name,
				"key_field"=>$this->model->getKeyField(),
				"key_value"=>$params[0],
				"form"=>$form
			)
		);
		return $form->render(); //ModelController::frameText(400,$form->render());
	}

	/**
	 * Display the items already in the database for editing.
	 * @param $params An array of parameters that the system uses.
	 * @return string
	 */
	public function view($params)
	{
		$form = $this->getForm();
		$form->setShowField(false);
		$data = $this->model->get(array("conditions"=>$this->model->getKeyField()."='".$params[0]."'"),SQLDatabaseModel::MODE_ASSOC,true,false);
		$form->setData($data[0]);
		$this->label = "View ".$this->label;
		return $form->render(); //ModelController::frameText(400,$form->render());
	}

	/**
	 * Export the data in the model into a particular format. Formats depend on
	 * the formats available in the reports api.
	 * @param $params
	 * @return unknown_type
	 * @see Report
	 */
	public function export($params)
	{
		switch($params[0])
		{
			case "pdf":
				$report = new PDFReport();
				break;
				
			case "html":
				$report = new HTMLReport();
				$report->htmlHeaders = true;
				break;
				
			case "csv":
				if($params[1]=="")
					$report = new CSVReport();
				else if($params[1]=="template")
				{
					$report = new CSVReport();
					$table = new TableContent($this->model->getLabels(),array());
					$report->add($table);
					$report->output();
				}
				break;
		}
		
		$title = new TextContent($this->label);
		$title->style["size"] = 12;
		$title->style["bold"] = true;

		$headers = $this->model->getLabels();

		$fieldNames = $this->model->getFieldNames();
		array_shift($fieldNames);
		$data = $this->model->get(array("fields"=>$fieldNames));
		//$data = $this->model->formatData();
		$table = new TableContent($headers,$data);
		$table->style["decoration"] = true;

		$report->add($title,$table);

		$report->output();
	}

	/**
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	public function import($params)
	{
		$data = array();
		$form = new Form();
		$form->
		add(
			Element::create("FileUploadField","File","file","Select the file you want to upload.")->
				setScript(Application::$prefix."lib/controllers/import.php?model=$this->model_name")->
				setJsExpression("NTHC.showUploadedData(callback_data)")
		);
		$form->setRenderer("default");
		$form->addAttribute("style","width:230px");
		$form->setShowSubmit(false);

		$data["form"] = $form->render();
		return array
		(
			"template"=>"file:".getcwd()."/lib/controllers/import.tpl",
			"data"=>$data
		);
	}

	/**
	 * Delete a particular item from the model.
	 * @param $params
	 * @return unknown_type
	 */
	public function delete($params)
	{
		//xdebug_start_trace("/tmp/trace.out");
		$data = $this->model->getWithField($this->model->getKeyField(),$params[0]);
		$this->model->delete($this->model->getKeyField(),$params[0]);
		User::log("Deleted ".$this->model->name,$data[0]);
		header("Location: {$this->urlPath}?notification=Successfully+deleted+".strtolower($this->label));
		//xdebug_stop_trace();
	}

	/**
	 * Return a standard set of permissions which allows people within certain
	 * roles to access only parts of this model controller.
	 *
	 * @see lib/controllers/Controller#getPermissions()
	 * @return Array
	 */
	public function getPermissions()
	{
		return array
		(
			array("label"=>"Can add","name"=>$this->name."_can_add"),
			array("label"=>"Can edit","name"=>$this->name."_can_edit"),
			array("label"=>"Can delete","name"=>$this->name."_can_delete"),
			array("label"=>"Can view","name"=>$this->name."_can_view"),
			array("label"=>"Can export","name"=>$this->name."_can_export"),
			array("label"=>"Can import","name"=>$this->name."_can_import")
		);
	}
}
?>
