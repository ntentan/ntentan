<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\views\helpers\javascripts;

use ntentan\Ntentan;
use ntentan\views\helpers\Helper;

class JavascriptsHelper extends Helper
{
    public function help($arguments)
    {
        if(is_array($arguments))
        {
            foreach($arguments as $argument)
            {
                if($argument == '') continue;
                $javascripts .= "<script type='text/javascript' src='$argument'></script>";
            }
        }
        else
        {
            $javascripts .= "<script type='text/javascript' src='$arguments'></script>";
        }
        return $javascripts;
    }

    public function ntentan()
    {
        $url_prefix = Ntentan::$prefix;
        return "<script type='text/javascript'>
            var ntentan = {
            url : function(route)
            {
                return '$url_prefix' + '/' + route;
            }
        }
        </script>";
    }
}
