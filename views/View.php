<?php

/**
 * Template engine subclass. 
 */
class View
{
    protected $layout;

    public function __call ($method, $arguments)
    {
        if(substr($method, 0, 6) == "create")
        {
            $class = substr($method, 6);
            $reflectionClass = new ReflectionClass($class);
            return $reflectionClass->newInstanceArgs($arguments);
        }
    }

    public function addHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("views/helpers/$helper"));
    }

    public function out($template, $data)
    {
        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                $$key = $value;
            }
        }

        ob_start();
        if(file_exists( Ntentan::$packagesPath . $template ))
        {
            include Ntentan::$packagesPath . $template;
        }
        else
        {
            die("View template not Found!");
        }
        $data = ob_get_clean();
        return $data;
    }
}