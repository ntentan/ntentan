<?php
namespace ntentan\views\blocks;

use \ntentan\Ntentan;
use \ntentan\views\Presentation;

class Block extends Presentation
{
    protected $data = array();
    protected $template;
    protected $name;
    
    public function getName() {
    	return $this->name;
    }
    
    public function setName($name) {
    	$this->name = $name;
    }

    protected function set($params1, $params2 = null) {
    	
        if(is_array($params1)) {
            $this->data = array_merge($this->data, $params1);
        } else {
            $this->data[$params1] = $params2;
        }
    }

    protected function getData()
    {
        return $this->data;
    }

    public function preRender()
    {

    }

    public function postRender()
    {
        
    }

    public function __toString()
    {
        $this->preRender();
        foreach($this->data as $key => $value)
        {
            $$key = $value;
        }
        if($this->template == "")
        {
            $block = $this->getName();
            $this->template = Ntentan::$blocksPath . "$block/$block.tpl.php";
        }
        ob_start();
        include $this->template;
        $this->postRender();
        return ob_get_clean();
    }
}
