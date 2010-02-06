<?php
class Block extends Presentation
{
    protected $data = array();
    protected $template;

    protected function set($params1, $params2 = null)
    {
        if(is_array($params1))
        {
            $this->data = array_merge($this->data, $params1);
        }
        else
        {
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
            $block = new ReflectionObject($this);
            $blockName = strtolower($block->getName());
            $block = substr($blockName, 0, strlen(blockName) - 5);
            $this->template = Ntentan::$blocksPath . "$block/$block.tpl.php";
        }
        ob_start();
        include $this->template;
        $this->postRender();
        return ob_get_clean();
    }
}
