<?php

class View extends Presentation
{
    private $_layout;

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
                return parent::__get($property);
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

    public function out($template, $data)
    {
        // Convert all keys of the data array into variables
        xdebug_start_trace("/tmp/trace.out");
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
        xdebug_stop_trace();

        return $data;
    }
}