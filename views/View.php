<?php

/**
 * Template engine subclass which contains all the initial settings
 * that the smarty engine needs to work.
 */
class View
{
    private static function create()
    {
        $arguments = func_get_args();
        $className = array_shift($arguments);
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->newInstanceArgs($arguments);
    }

    public function addHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("views/helpers/$helper"));
    }

    public function out($template)
    {
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