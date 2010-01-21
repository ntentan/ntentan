<?php
class Presentation
{
    private $_helpers = array();

    public function __get($property)
    {
        return $this->_helpers[$property];
    }

    public function addHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("views/helpers/$helper"));
        $helperClass = ucfirst($helper."Helper");
        $this->_helpers[$helper] = new $helperClass();
    }
}
