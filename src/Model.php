<?php
namespace ntentan;

use ntentan\nibii\RecordWrapper;
use ntentan\utils\Text;

class Model extends RecordWrapper
{
    public static function getClassName($name)
    {
        $namespace = Ntentan::getNamespace();
        return "\\$namespace\\modules\\" . str_replace(".", "\\", $name) . "\\" . 
            Text::ucamelize(reset(explode('.', $name)));
    }
    
    public static function load($name)
    {
        $class = self::getClassName($name);
        return new $class();
    }
}
