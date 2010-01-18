<?php

class View
{
    private $_layout;
    private $_helpers = array();

    public function __construct()
    {
        $this->_layout = new Layout();
    }
    
    public function __get($property)
    {
        switch($property)
        {
            case "layout":
                return $this->_layout;
                break;
            
            default:
                return $this->_helpers[$property];
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
            case "layout":
                $this->layout->name = $value;
                break;
        }
    }

    public function addHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("views/helpers/$helper"));
        $helperClass = ucfirst($helper."Helper");
        $this->_helpers[$helper] = new $helperClass();
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

        ob_start();
        $this->_layout->out($data);
        $data = ob_get_clean();

        return $data;
    }
}