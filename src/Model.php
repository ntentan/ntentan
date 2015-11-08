<?php
namespace ntentan;

use ntentan\nibii\RecordWrapper;

class Model extends RecordWrapper
{
    public static function load($name)
    {
        return nibii\Nibii::load($name);
    }
}
