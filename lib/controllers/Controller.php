<?php
require_once "ModelController.php";
require_once "PackageController.php";
require_once "ErrorController.php";

abstract class Controller
{
	protected $_showInMenu = false;
	public $label;
	public $description;
	public $content;
	const TYPE_MODULE = "module";
	const TYPE_MODEL = "model";
	protected $path;
	public $name;

	public function getName()
	{

	}

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
			else
			{
				$controller_path .= "/$p";
			}
		}

		//If it doesn't refer to a page assume the initial name is a module load
		//it and pass the rest of the path to it.
		switch($controller_type)
		{
		case Controller::TYPE_MODULE:
			require_once "app/modules$controller_path/$controller_name.php";
			$controller = new $controller_name();
			break;

		case Controller::TYPE_MODEL;
			$model = substr(str_replace("/",".",$controller_path),1);
			$controller_name = "ModelController";
			$controller = new ModelController($model);
			break;

		default:
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

		if($get_contents)
		{
			if($i == count($path)-1)
			{
				$controller->content = $controller->getContents();
			}
			else
			{
				if(method_exists($controller,$path[$i+1]))
				{
					$controller_class = new ReflectionClass($controller_name);
					$method = $controller_class->GetMethod($path[$i+1]);
					$controller->content = $method->invoke($controller,array_slice($path,$i+2));
				}
				else
				{
					$controller->content = "Error! ".$path[$i+1];
				}
			}
		}

		return $controller;
	}

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
}
?>
