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
    public function help($arguments)
    {
        foreach($arguments as $argument)
        {
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='$argument' />";
        }
        return $stylesheets;
    }
}
