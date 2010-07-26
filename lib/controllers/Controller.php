<?php
/*require_once "ModelController.php";
require_once "PackageController.php";
require_once "ErrorController.php";
require_once "ReportController.php";*/

require_once "Model.php";

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application. They are stored in modules and they contain methods which
 * are called from the url. Parameters to the methods are also passed through the
 * URL. If no method is specified, the Controller:getContents() method is called.
 * The methods called by the controllers are expected to generate HTML output
 * which should be directly displayed to the screen.
 *
 * All the controllers you build must extend this class end implement
 *
 * @todo Controllers must output data that can be passed to some kind of template
 *       engine like smarty.
 * @author james
 *
 */
abstract class Controller
{
	/**
	 * Check if this controller is supposed to be shown in any menus that are
	 * created. This property is usually false for modules which are built for
	 * internal use within the application.
	 */
	protected $_showInMenu = false;

	/**
	 * A descriptive label for this controler.
	 */
	public $label;

	/**
	 * A piece of text which briefly described the use of this model.
	 */
	public $description;

	/**
	 * A variable which contains the contents of a given controller after a
	 * particular method has been called. This is what external controllers
	 * usually use.
	 */
	public $content;

	/**
	 * This constant represents controllers that are loaded from modules
	 */
	const TYPE_MODULE = "module";

	/**
	 * This constant represents controllers that are loaded from models.
	 * @var unknown_type
	 */
	const TYPE_MODEL = "model";

	/**
	 *
	 */
	const TYPE_REPORT = "report";

	/**
	 * A copy of the path that was used to load this controller in an array
	 * form.
	 * @var Array
	 */
	public $path;

	/**
	 * A short machine readable name for this label.
	 * @var string
	 */
	public $name;

	/**
	 * A utility method to load a controller. This method loads the controller
	 * and fetches the contents of the controller into the Controller::$contents
	 * variable if the get_contents parameter is set to true on call. If a controller
	 * doesn't exist in the module path, a ModelController is loaded to help
	 * manipulate the contents of the model. If no model exists in that location,
	 * it is asumed to be a package and a package controller is loaded.
	 *
	 * @param $path 		The path for the model to be loaded.
	 * @param $get_contents A flag which determines whether the contents of the
	 *						controller should be displayed.
	 * @return Controller
	 */
	public static function load($path,$get_contents=true)
	{
		$controller_path = "";
		$controller_name = "";

		//Go through the whole path and build the folder location of the system
		for/*each*/($i = 0; $i<count($path);$i++) //  as $p)
		{
			$p = $path[$i];
			if(file_exists("app/modules/$controller_path/$p/$p.php"))
			{
				$controller_name = $p;
				$controller_path .= "/$p";
				$controller_type = Controller::TYPE_MODULE;
				break;
			}
			else if(file_exists("app/modules/$controller_path/$p/model.xml"))
			{
				$controller_name = $p;
				$controller_path .= "/$p";
				$controller_type = Controller::TYPE_MODEL;
				break;
			}
			else if(file_exists("app/modules/$controller_path/$p/report.xml"))
			{
				$controller_name = $p;
				$controller_path .= "/$p";
				$controller_type = Controller::TYPE_REPORT;
				break;
			}
			else
			{
				$controller_path .= "/$p";
			}
		}

		// Check the type of controller and load it.
		switch($controller_type)
		{
			case Controller::TYPE_MODULE:
				// Load a module controller which would be a subclass of this
				// class
				require_once "app/modules$controller_path/$controller_name.php";
				$controller = new $controller_name();
				break;

			case Controller::TYPE_MODEL;
			// Load the ModelController wrapper around an existing model class.
			$model = substr(str_replace("/",".",$controller_path),1);
			$controller_name = "ModelController";
			$controller = new ModelController($model);
			break;
				
			case Controller::TYPE_REPORT:
				$controller = new ReportController($controller_path."/report.xml");
				$controller_name = "ReportController";
				break;

			default:
				// Load a package controller for this folder
				if(is_dir("app/modules$controller_path"))
				{
					$controller = new Packagecontroller();
					$controller_name = "PackageController";
				}
				else
				{
					$controller = new ErrorController();
					$controller_name = "ErrorController";
				}
		}

		// If the get contents flag has been set return all the contents of this
		// controller.
		$controller->path = $controller_path;
		
		if($get_contents)
		{
			if($i == count($path)-1)
			{
				$ret = $controller->getContents();
			}
			else
			{
				if(method_exists($controller,$path[$i+1]))
				{
					$controller_class = new ReflectionClass($controller_name);
					$method = $controller_class->GetMethod($path[$i+1]);
					$ret = $method->invoke($controller,array_slice($path,$i+2));
				}
				else
				{
					$ret = "<h2>Error</h2> Method does not exist. ".$path[$i+1];
				}
			}
			
			
			if(is_array($ret))
			{
				$t = new template_engine();
				$t->assign($ret["data"]);
				//print isset($ret["template"])?$ret["template"]:$path[$i+1].".tpl";
				//if(file_exists(isset($ret["template"])?$ret["template"]:$path[$i+1].".tpl")) print "Found!";
				$controller->content = $t->fetch(isset($ret["template"])?$ret["template"]:$path[$i+1].".tpl");
			}
			else if(is_string($ret))
			{
				$controller->content = $ret;
			}
		}
		
		return $controller;
	}

	/**
	 * An implementation of the default getContents method which returns a No
	 * content string.
	 *
	 * @todo When the controllers are changed to return variables for template
	 * 		 engines make this return that to.
	 * @return string
	 */
	protected function getContents()
	{
		return "No Content";
	}

	public function showInMenu()
	{
		return $this->_showInMenu;
	}

	public function getPermissions()
	{

	}

	public function getTemplateDescription($template,$data)
	{
		return array("template"=>"file:/".getcwd()."/app/modules/$template","data"=>$data);
	}
}
?>
