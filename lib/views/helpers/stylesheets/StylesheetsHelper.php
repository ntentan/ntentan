<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\views\helpers\stylesheets;

use ntentan\Ntentan;
use ntentan\views\helpers\Helper;

class StylesheetsHelper extends Helper
{
    private $loadedStylesheets = array();

    public function help($arguments)
    {
        if(is_array($arguments))
        {
            foreach($arguments as $argument)
            {
                if($argument == '') continue;
                $stylesheets .= "<link rel='stylesheet' type='text/css' href='$argument' />";
            }
        }
        else
        {
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='$arguments' />";
        }
        foreach($this->loadedStylesheets as $argument)
        {
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='$argument' />";
        }
        return $stylesheets;
    }

    public function add($stylesheet)
    {
        $this->loadedStylesheets[] = $stylesheet;
    }
}
