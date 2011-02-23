<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

class Template
{
    private static $engineCache;

    public static function getEngine($template)
    {
        $engine = end(explode(".", $template));
        if(!isset(Template::$engineCache[$engine]))
        {
            Ntentan::addIncludePath(
                Ntentan::getFilePath("lib/views/template_engines/" . $engine)
            );
            $engineClass = "ntentan\\views\\template_engines\\$engine\\" . Ntentan::camelize($engine);
            $engineInstance = new $engineClass();
            Template::$engineCache[$engine] = $engineInstance;
        }
        Template::$engineCache[$engine]->template = $template;
        return Template::$engineCache[$engine];
    }

    public static function out($template, $templateData, $view = null)
    {
        if(is_string($template))
        {
            return Template::getEngine($template)->out($templateData, $view);
        }
        else
        {
            foreach($template as $singleTemplate)
            {
                if(Template::fileExists($singleTemplate))
                {
                    return Template::getEngine($template)->out($templateData, $view);
                }
            }
        }
    }

    /**
     * Based on code from PHP manual User Contribution by (plsnhat at gmail dot com)
     * on the file_exists function. Comment contributed on 31-Jul-2009 02:30.
     * @param <type> $filename
     * @return <type>
     */
    private static function fileExists($filename) {
        if(function_exists("get_include_path"))
        {
            $include_path = get_include_path();
        }
        elseif(false !== ($ip = ini_get("include_path")))
        {
            $include_path = $ip;
        }
        else
        {
            return false;
        }

        if(false !== strpos($include_path, PATH_SEPARATOR)) {
            if(false !== ($temp = explode(PATH_SEPARATOR, $include_path)) && count($temp) > 0) {
                for($n = 0; $n < count($temp); $n++) {
                    if(false !== @file_exists($temp[$n] . $filename)) {
                        return true;
                    }
                }
                return false;
            } else {return false;}
        } elseif(!empty($include_path)) {
            if(false !== @file_exists($include_path)) {
                return true;
            } else {return false;}
        } else {return false;}
    }
}