<?php

abstract class AbstractComponent extends Controller
{
    protected $controllerName;
    protected $controller;

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    public function setControllerPath($controllerPath)
    {
        $this->path = $controllerPath;
    }

    protected function set($params1, $params2)
    {
        $this->controller->set($params1, $params2);
    }

    protected function get()
    {
        return $this->controller->get();
    }

    protected function callControllerMethod()
    {
        $arguments = func_get_args();
        $method = array_shift($arguments);
        if(method_exists($this->controller, $method))
        {
            $reflectionMethod = new ReflectionMethod($this->controller, $method);
            $ret = $reflectionMethod->invokeArgs($this->controller, $arguments);
        }
//        else
//        {
//            throw new ControllerMethodNotFoundException($this->controllerName, $method);
//        }
        return $ret;
    }

    public function __get($property)
    {
        switch ($property)
        {
            case "view":
                return $this->controller->view;//Instance;
                break;

            default:
                return parent::__get($property);
        }
    }

}
