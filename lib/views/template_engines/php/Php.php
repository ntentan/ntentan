<?php
namespace ntentan\views\template_engines\php;

use ntentan\views\template_engines\TemplateEngine;

class Php extends TemplateEngine
{
    public function out($templateData)
    {
        foreach($templateData as $key => $value)
        {
            $$key = $value;
        }
        ob_start();
        include $this->template;
        return ob_get_clean();
    }

    public function truncate($text, $length, $terminator = '...')
    {
        return substr($text, 0, $length) . $terminator;
    }
}