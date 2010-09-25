<?php
namespace ntentan\views\helpers\gravatar;

use \ntentan\views\helpers\Helper;

class Gravatar extends Helper
{
    public function get($email, $size = 48)
    {
        $hash = md5(strtolower(trim($email)));
        return "http://www.gravatar.com/avatar/$hash.jpg?s=$size&amp;d=mm";
    }
}