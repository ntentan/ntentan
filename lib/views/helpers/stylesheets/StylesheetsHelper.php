<?php
namespace ntentan\views\helpers\stylesheets;

use ntentan\Ntentan;
use ntentan\minification\MinifiableHelper;

class StylesheetsHelper extends MinifiableHelper
{
    protected function getExtension()
    {
        return "css";
    }
    
    protected function getMinifier()
    {
        return "css.cssmin";
    }
    
    protected function getTag($url)
    {
        return "<link type='text/css' rel='stylesheet' href='$url'>";
    }
}
