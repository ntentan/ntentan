<?php
namespace ntentan\minification;

use ntentan\views\helpers\Helper;
use ntentan\Ntentan;

abstract class MinifiableHelper extends Helper
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
        if(!file_exists($filename))
        {
            foreach($this->minifiableScripts as $script)
            {
                $minifiedScript .= file_get_contents($script);
            }
            file_put_contents($filename, Minifier::minify($minifiedScript, $this->getMinifier()));
        }
        $tags = $this->getTag(Ntentan::getUrl($filename));
        
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
        else
        {
            $this->otherScripts[]= $arguments;
        }
        return $this;
    }
    
    public function add($javascript)
    {
        $this->minifiableScripts[] = $javascript;
        return $this;
    }    
}
