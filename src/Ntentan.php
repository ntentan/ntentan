<?php
/**
 * Common utilities file for the Ntentan framework. This file contains a
 * collection of utility static methods which are used accross the framework.
 *
 * Ntentan Framework
 * Copyright (c) 2008-2015 James Ekow Abaka Ainooson
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */


/**
 * Root namespace for all ntentan classes
 * @author ekow
 */
namespace ntentan;

use ntentan\utils\Text;

/**
 * Include a collection of utility global functions, caching and exceptions.
 * Classes loaded here are likely to be called before the autoloader kicks in.
 */

/**
 * A utility class for the Ntentan framework. This class contains the routing
 * framework used for routing the pages. Routing involves the analysis of the
 * URL and the loading of the controllers which are requested through the URL.
 * This class also has several utility methods which help in the overall
 * operation of the entire framework.
 *
 *  @author     James Ainooson <jainooson@gmail.com>
 *  @license    MIT
 */
class Ntentan
{
    /**
     * Root namespace for entire application.
     *
     * @var string
     */
    private static $namespace;

    /**
     *
     *
     * @var string
     */
    private static $configPath = 'config/';


    private static $prefix;

    /**
     * The routing engines entry. This method analyses the URL and implements
     * the routing engine.
     */
    public static function start($namespace)
    {
        self::$namespace = $namespace;
        self::$prefix = Config::get('app.prefix');
        self::$prefix = (self::$prefix == '' ? '' : '/') . self::$prefix;
        
        spl_autoload_register(function ($class) use($namespace) {

           $prefix = "$namespace\\";
           $baseDir = 'src/';
           $len = strlen($prefix);
           
           if (strncmp($prefix, $class, $len) !== 0) {
               return;
           }

           $relativeClass = substr($class, $len);
           $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

           if (file_exists($file)) {
               require_once $file;
           }
       });        

        Session::start();
        logger\Logger::init('logs/app.log');

        honam\TemplateEngine::prependPath('views/default');
        honam\TemplateEngine::prependPath('views');
        honam\AssetsLoader::setSiteUrl(self::getUrl('public'));
        honam\AssetsLoader::appendSourceDir('assets');
        honam\AssetsLoader::setDestinationDir('public');

        Config::init(self::$configPath);
        nibii\DriverAdapter::setDefaultSettings(Config::get('db'));
        kaikai\Cache::init(Config::get('cache'));

        nibii\Nibii::setClassResolver(function($name, $context) use ($namespace){
            if($context == nibii\Relationship::BELONGS_TO) {
                $name = Text::pluralize($name);
            }
            //$namespace = Ntentan::getNamespace();
            return "\\$namespace\\modules\\" . str_replace(".", "\\", $name) . "\\" .
                Text::ucamelize(reset(explode('.', $name)));                    
        });
        
        nibii\Nibii::setModelJoiner(function($classA, $classB) use ($namespace) {
            $classBParts = explode('\\', substr(nibii\Nibii::getClassName($classB), 1));
            $classAParts = explode('\\', $classA);
            $joinerParts = [];
            
            foreach($classAParts as $i => $part) {
                if($part == $classBParts[$i]) {
                    $joinerParts[] = $part;
                } else {
                    break;
                }
            }
            
            $namespace = [Text::deCamelize(end($classAParts)), Text::deCamelize(end($classBParts))];
            sort($namespace);
            $joinerParts[] = implode('_', $namespace);
            
            $class = [end($classAParts), end($classBParts)];
            sort($class);
            $joinerParts[] = implode('', $class);
            
            return implode('\\', $joinerParts);
        });

        Router::route();
    }

    public static function getNamespace()
    {
        return self::$namespace;
    }

    public static function getUrl($url)
    {
        $prefix = Config::get('app.prefix');
        $newUrl = ($prefix == '' ? '' : '/') . $prefix;
        if($url) {
            $newUrl .= ($url[0] == '/' ? '' : '/') . "$url";
        }
        return $newUrl;
    }

    public static function redirect($url = null, $absolute = false)
    {
        $redirect = filter_input(INPUT_GET, "redirect");
        $url = $redirect == '' ? $url : $redirect;
        $url = $absolute === true ? $url : Ntentan::getUrl($url);  
        header("Location: $url");
    }
}
