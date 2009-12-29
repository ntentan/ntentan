<?php

/**
 * 
 */
abstract class AbstractComponent extends Controller
{
    /**
     *
     * @var string
     */
    private $controller;
    private $controllerPath;

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    public function setControllerPath($controllerPath)
    {
        $this->controllerPath = $controllerPath;
    }

    public function runPath($path, $params)
    {
        if(method_exists($this, $path))
        {
            $this->mainPreRender();
            $controllerClass = new ReflectionClass($this->name);
            $method = $controllerClass->GetMethod($path);
            $ret = $method->invoke($this, $params); //array_slice($pathArray,$i+2));
            $view = new View();
            $ret = $view->out("{$this->controllerPath}/{$path}.tpl.php");
            $this->mainPostRender();
        }
        else
        {
            foreach($this->components as $component)
            {
                if($component->hasPath($path))
                {
                    $component->runPath($path, $params);
                }
            }
        }
        print $ret;
    }
}
