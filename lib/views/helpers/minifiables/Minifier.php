<?php
namespace ntentan\views\helpers\minifiables;

use ntentan\Ntentan;

abstract class Minifier
{
    public abstract function performMinification($script);

    public static function minify($script, $minifier)
    {
        return self::getMinifier($minifier)->performMinification($script);
    }

    private static function getMinifier($minifier)
    {
        $minifierName = end(explode('.', $minifier));
        $class = "ntentan\\views\\helpers\\minifiables\\minifiers\\" . str_replace(".", "\\", $minifier) . '\\' . Ntentan::camelize($minifierName) . "Minifier";
        $instance = new $class();
        return $instance;
    }
}
