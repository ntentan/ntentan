<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;
use ntentan\caching\Cache;

class Template
{
    private static $engineCache;
    private static $path;

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

    public static function appendPath($path)
    {
        self::$path[] = $path;
    }

    public static function prependPath($path)
    {
        array_unshift(self::$path, $path);
    }

    public static function out($template, $templateData, $view = null)
    {
        $cacheKey = "template_$template";
        if(Cache::exists($cacheKey))
        {
            $templateFile = Cache::get($cacheKey);
        }
        else
        {
            foreach(self::$path as $path)
            {
                $templateFile = "$path/$template";
                if(file_exists($templateFile))
                {
                    Cache::add($cacheKey, $templateFile);
                    break;
                }
            }
        }
        return Template::getEngine($templateFile)->out($templateData, $view);
    }
}
