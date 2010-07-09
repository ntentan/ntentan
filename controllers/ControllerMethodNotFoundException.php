<?php
class ControllerMethodNotFoundException extends Exception
{
    public $method;
    public $controller;

    public function __construct($controller, $method)
    {
        parent::__construct("Method $method not found in controller $controller", $code);
        $this->method = $method;
        $this->controller = $controller;
    }
}