<?php
class Block extends Presentation
{
    protected $data = array();

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
    
    public function __toString()
    {
        foreach($this->data as $key => $value)
        {
            $$key = $value;
        }
        $block = new ReflectionObject($this);
        $block = strtolower($block->getName());
        ob_start();
        include Ntentan::$blocksPath . "$block/$block.tpl.php";
        return ob_get_clean();
    }
}
