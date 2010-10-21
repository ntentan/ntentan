<?php
namespace ntentan\views\helpers\forms\api\renderers;

abstract class Renderer
{
    public $showFields = true;
    
    abstract public function head();
    abstract public function element($element);
    abstract public function foot();
}