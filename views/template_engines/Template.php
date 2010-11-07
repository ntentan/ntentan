<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

class Template
{
    public static function out($template, $data)
    {
        $engine = end(explode(".", $template));
        Ntentan::addIncludePath(
            Ntentan::getFilePath("views/template_engines/" . $engine)
        );
        $engine = "ntentan\\views\\template_engines\\$engine\\" . Ntentan::camelize($engine);
        $engine = new $engine();
        return $engine->out($template, $data);
    }
}