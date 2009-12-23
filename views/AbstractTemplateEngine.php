<?php

/**
 * Template engine subclass which contains all the initial settings
 * that the smarty engine needs to work.
 */
abstract class AbstractTemplateEngine
{
    protected $data;
    private $templateFile;

    public function out($param1, $param2 = null)
    {
        if(is_array($param1))
        {
            array_merge($this->data, $param1);
        }
        else
        {
            $this->data[$param1] = $param2;
        }
    }

    public function setTemplate($template)
    {
        $this->templateFile = $template;
    }

    public function getTemplate()
    {
        return $this->templateFile;
    }
    
    abstract public function getOutput();
}