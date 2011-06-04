<?php
namespace ntentan\views\template_engines\php;

use ntentan\Ntentan;
use ntentan\views\template_engines\TemplateEngine;

/**
 * 
 */
class Php extends TemplateEngine
{
    public function generate($templateData, $view)
    {
        if(file_exists($this->template))
        {
            foreach($templateData as $key => $value)
            {
                $$key = $value;
            }
            ob_start();
            include $this->template;
            return ob_get_clean();
        }
        else
        {
            echo Ntentan::message("Template file <b><code>{$this->template}</code></b> not found");
        }
    }

    public function load_asset($asset, $copyFrom = null)
    {
        return $this->loadAsset($asset, $copyFrom);
    }

    public function strip_html($text)
    {
        return \ntentan\utils\Janitor::cleanHtml($text, true);
    }

    public function truncate($text, $length, $terminator = ' ...')
    {
        while(mb_substr($text, $length, 1) != ' ' && $length > 0)
        {
            $length--;
        }
        return mb_substr($text, 0, $length) . $terminator;
    }

    public function nl2br($text)
    {
        return str_replace("\n", '<br/>', $text);
    }
}
