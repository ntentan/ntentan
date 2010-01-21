<?php

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application. They are stored in modules and they contain methods which
 * are called from the url. Parameters to the methods are also passed through the
 * URL. If no method is specified, the Controller:getContents() method is called.
 * The methods called by the controllers are expected to generate HTML output
 * which should be directly displayed to the screen.
 *
 * All the controllers you build must extend this class and implement 
 *
 * @todo Controllers must output data that can be passed to some kind of template
 *       engine like smarty.
 * @author james
 *
 */
class Controller
{
    public $defaultMethodName = "contents";

	/**
	 * A copy of the path that was used to load this controller in an array
	 * form.
	 * @var String
	 */
	public $path;

	/**
	 * A short machine readable name for this controller.
	 * @var string
	 */
	public $name;

    /**
     *
     * @var Array
     */
    public $data;

    /**
     *
     * @var Array
     */
    private $components = array();

    public $viewInstance;

    protected $blocks = array();

    public function __get($property)
    {
        switch ($property)
        {
        case "view":
            return $this->viewInstance;
        default:
            if(substr($property, -5) == "Block")
            {
                $block = substr($property, 0, strlen($property) - 5);
                return $this->blocks[$block];
            }
        }
    }

    /**
     * Adds a component to the controller.
     * @param string $component Name of the component
     */
    public function addComponent($component)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("controllers/components/$component"));
        $component = new $component();
        $component->setController($this);
        $this->components[] = $component;
    }

    public function addBlock($block, $alias)
    {
        Ntentan::addIncludePath(Ntentan::$blocksPath . "$block");
        $block = ucfirst($block);
        $blockInstance = new $block();
        $this->blocks[$alias] = $blockInstance;
    }

    /**
     * 
     * @param mixed $params1
     * @param string $params2
     */
    protected function set($params1, $params2 = null)
    {
        if(is_array($params1))
        {
            $this->data = array_merge($this->data, $params1);
        }
        else
        {
            $this->data[$params1] = $params2;
        }
    }

    protected function get()
    {
        return $this->data;
    }

	/**
	 * A utility method to load a controller. This method loads the controller
	 * and fetches the contents of the controller into the Controller::$contents
	 * variable if the get_contents parameter is set to true on call. If a
     * controller doesn't exist in the module path, a ModelController is loaded
     * to help manipulate the contents of the model. If no model exists in that
     * location, it is asumed to be a package and a package controller is
     * loaded.
	 *
	 * @param $path 		The path for the model to be loaded.
	 * @return Controller
	 */
	public static function load($path)
	{
        $controllerPath = '';
        $pathArray = explode('/', $path);
        
		for($i = 0; $i<count($pathArray); $i++)
		{
			$p = $pathArray[$i];
            $pCamelized = ucfirst($p);
			if(file_exists(Ntentan::$packagesPath . "$controllerPath/$p/{$pCamelized}Controller.php"))
			{
				$controllerName = $pCamelized."Controller";
				$controllerPath .= "/$p";
				break;
			}
			else
			{
				$controllerPath .= "/$p";
			}
		}

        if($controllerName == "")
        {
            die("Path not found!");
        }
        else
        {
            require_once Ntentan::$packagesPath . "$controllerPath/$controllerName.php";
            $controller = new $controllerName();

            $view = new View();
            $view->layout = "main";
            
            $controller->setView($view);
            $controller->setPath($controllerPath);
            $controller->setName($controllerName);

            if($i != count($pathArray)-1)
            {
                $methodName = $pathArray[$i+1];
            }
            else
            {
                $methodName = $controller->defaultMethodName;
            }

            if($controller->hasPath($methodName))
            {
                $ret = $controller->runPath($methodName, array_slice($pathArray,$i+2));
            }
            else
            {
                die("Error!");
            }
        }
	}
    
    public function setName($name)
    {
        $this->name = $name;
        foreach($this->components as $component)
        {
            $component->setControllerName($name);
        }
    }

    public function setPath($path)
    {
        $this->path = $path;
        foreach($this->components as $component)
        {
            $component->setControllerPath($path);
        }
    }

    public function hasPath($path)
    {
        $ret = false;
        if(method_exists($this, $path))
        {
            $ret = true;
        }
        else
        {
            foreach($this->components as $component)
            {
                $ret = $component->hasPath($path);
                if($ret)
                {
                    break;
                }
            }
        }
        return $ret;
    }

    public function setView($view)
    {
        $this->viewInstance = $view;
    }

    public function runPath($path, $params)
    {
        if(method_exists($this, $path))
        {
            $this->mainPreRender();
            $controllerClass = new ReflectionClass($this->name);
            $method = $controllerClass->GetMethod($path);
            $method->invoke($this, $params);
            $this->view->layout->blocks = $this->blocks;
            $ret = $this->view->out("{$this->path}/{$path}.tpl.php", $this->get());
            $this->mainPostRender();
        }
        else
        {
            foreach($this->components as $component)
            {
                if($component->hasPath($path))
                {
                    $component->data = $this->data;
                    $component->runPath($path, $params);
                }
            }
        }
        print $ret;
    }

    public function mainPreRender()
    {
        foreach($this->components as $component)
        {
            $component->preRender();
        }
        $this->preRender();
    }

    public function mainPostRender()
    {
        foreach($this->components as $component)
        {
            $component->postRender();
        }
        $this->postRender();
    }

    public function preRender()
    {

    }

    public function postRender()
    {
        
    }
}
