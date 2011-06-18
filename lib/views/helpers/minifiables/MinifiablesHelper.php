<?php
namespace ntentan\views\helpers\minifiables;

use ntentan\views\helpers\Helper;
use ntentan\Ntentan;
use ntentan\views\template_engines\TemplateEngine;

abstract class MinifiablesHelper extends Helper
{
    private $minifiableScripts = array();
    private $otherScripts = array();
    private $context = 'default';

    protected abstract function getExtension();
    protected abstract function getMinifier();
    protected abstract function getTag($url);
    
    public function __toString()
    {
        $filename = "public/".$this->getExtension()."/bundle_{$this->context}." . $this->getExtension();
        if(!file_exists($filename) || Ntentan::$debug == true)
        {
            foreach($this->minifiableScripts as $script)
            {
                if(Ntentan::$debug == true)
                {
                    $tags .= $this->getTag(Ntentan::getUrl(TemplateEngine::loadAsset($this->getExtension() . "/" . basename($script), $script)));
                }
                else
                {
                    $minifiedScript .= file_get_contents($script);
                }
            }
            if(Ntentan::$debug == false)
            {
                file_put_contents($filename, Minifier::minify($minifiedScript, $this->getMinifier()));
            }
        }
        if(Ntentan::$debug == false)
        {
            $tags = $this->getTag(Ntentan::getUrl($filename));
        }
        
        foreach($this->otherScripts as $script)
        {
            $tags .= $this->getTag(Ntentan::getUrl($script));
        }
        return $tags;
    }

    public function help($arguments)
    {
        if(is_array($arguments))
        {
            foreach($arguments as $argument)
            {
                if($argument == '') continue;
                $this->otherScripts[]= $argument;
            }
        }
        else if($arguments != '')
        {
        	$this->otherScripts[]= $arguments;
        }
        return $this;
    }

    public function add($script)
    {
        if($script != '') $this->minifiableScripts[] = $script;
        return $this;
    }
    
    public function context($context)
    {
        $this->context = $context;
        return $this;
    }
    
}
