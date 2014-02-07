<?php
namespace ntentan\views\helpers\file_sizes;
use ntentan\views\helpers\Helper;

class FileSizesHelper extends Helper
{
    public function help($size)
    {
        if($size < 1023)
        {
            return "$size bytes";
        }
        elseif ($size > 1023 && $size < 1048575) 
        {
            return round($size/1024, 1) . "KB";
        }
        elseif ($size > 1048575 && $size < 1073741823)
        {
            return round($size/1048576, 1) . "MB";
        }
    }
}
