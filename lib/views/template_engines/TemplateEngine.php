<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;
use ntentan\caching\Cache;

abstract class TemplateEngine
{
    private static $loadedInstances;
    public $template;
    private $widgetsLoader;
    private $helpersLoader;
    private static $path = array();
    private static $context = 'default';

    public static function appendPath($path)
    {
        self::$path[] = $path;
    }

    public static function prependPath($path)
    {
        array_unshift(self::$path, $path);
    }

    public static function getPath()
    {
        return self::$path;
    }

    public static function setContext($context)
    {
        self::$context = $context;
    }

    public static function getContext()
    {
        return self::$context;
    }

    public static function getEngineInstance($template)
    {
        $engine = end(explode(".", $template));
        if(!isset(TemplateEngine::$loadedInstances[$engine]))
        {
            Ntentan::addIncludePath(
                Ntentan::getFilePath("lib/views/template_engines/" . $engine)
            );
            $engineClass = "ntentan\\views\\template_engines\\$engine\\" . Ntentan::camelize($engine);
            try{
                $engineInstance = new $engineClass();
            }
            catch(\Exception $e)
            {
                throw new \ntentan\exceptions\ClassNotFoundException("Could not load template engine class [$engineClass] for $template");
            }
            TemplateEngine::$loadedInstances[$engine] = $engineInstance;
        }
        TemplateEngine::$loadedInstances[$engine]->template = $template;
        return TemplateEngine::$loadedInstances[$engine];
    }

    public static function loadAsset($asset, $copyFrom = null)
    {
        $assetPath = "assets/".($copyFrom==null ? $asset : $copyFrom);
        if(file_exists($assetPath) && file_exists(dirname("public/$asset")) && is_writable(dirname("public/$asset")))
        {
            copy($assetPath, "public/$asset");
        }
        else if(file_exists(Ntentan::getFilePath("assets/$asset")) && file_exists(dirname("public/$asset")) && is_writable(dirname("public/$asset")))
        {
            copy(Ntentan::getFilePath("assets/$asset"), "public/$asset");
        }
        else if(file_exists($copyFrom) && is_writable(dirname("public/$asset")))
        {
            copy($copyFrom, "public/$asset");
        }
        else
        {
            Ntentan::error("File not found or not writable <b><code>public/$asset</code></b>");
            die();
        }
        return "public/$asset";
    }

    public function __get($property)
    {
        switch($property)
        {
            case "widgets":
                if($this->widgetsLoader == null)
                {
                    $this->widgetsLoader = new WidgetsLoader();
                }
                return $this->widgetsLoader;


            case "helpers":
                if($this->helpersLoader == null)
                {
                    $this->helpersLoader = new HelpersLoader();
                }
                return $this->helpersLoader;
        }
    }

    public static function render($template, $templateData, $view = null)
    {
        $cacheKey = "template_{$template}_" . TemplateEngine::getContext();
        $path = TemplateEngine::getPath();
        if(Cache::exists($cacheKey) && Ntentan::$debug === false)
        {
            $templateFile = Cache::get($cacheKey);
        }
        else
        {
            $extension = explode('.', $template);
            $breakDown = explode('_', array_shift($extension));
            $extension = implode(".", $extension);
            for($i = 0; $i < count($breakDown); $i++)
            {
                $testTemplate = implode("_", array_slice($breakDown, -$i)) . ".$extension";
                foreach(TemplateEngine::getPath() as $path)
                {
                    $newTemplateFile = "$path/$testTemplate";
                    if(file_exists($newTemplateFile))
                    {
                        Cache::add($cacheKey, $newTemplateFile);
                        $templateFile = $newTemplateFile;
                        break;
                    }
                }
                if($templateFile != '') break;
            }
        }
        if($templateFile == null)
        {
            Ntentan::error("Could not find a suitable template file for the current request <b><code>{$template}</code></b>");
            die();
        }
        else
        {
            return TemplateEngine::getEngineInstance($templateFile)->generate($templateData, $view);
        }
    }

    abstract public function generate($data, $view);
}
