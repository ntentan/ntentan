<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan;

/**
 * Description of View
 *
 * @author ekow
 */
class View 
{
    private $layout;
    private $template;
    private $cacheTimeout = false;
    
    public function setContentType($contentType)
    {
        header("Content-Type: $contentType");
    }
    
    public function __construct()
    {
        $this->layout = 'main.tpl.php';
    }
    
    public function getCacheTimeout()
    {
        return $this->cacheTimeout;
    }
    
    public function setCacheTimeout($cacheTimeout)
    {
        $this->cacheTimeout = $cacheTimeout;
    }
    
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    public function out($viewData)
    {
        require_once 'view_functions.php';
        if($this->template != false)
        {
            $renderedTemplate = honam\TemplateEngine::render($this->template, $viewData);
            $viewData['contents'] = $renderedTemplate;
        }
        if($this->layout != FALSE)
        {
            return honam\TemplateEngine::render($this->layout, $viewData);
        }
    }
}
