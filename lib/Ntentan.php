<?php
/**
 * Common utilities file for the Ntentan framework. This file contains a 
 * collection of utility static methods which are used accross the framework.
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
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

/**
 * Include a collection of utility global functions, caching and exceptions. 
 */
require "globals.php";
require "caching/Cache.php";
require "exceptions/NtentanException.php";
require "exceptions/FileNotFoundException.php";

//@todo Find a better way of handling exceptions
//set_exception_handler(array("\\ntentan\\Ntentan", "exceptionHandler"));

use ntentan\caching\Cache;

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
     * The home of the ntentan framework. The directory in which the code for
     * the ntentan framework resides.
     * @var string
     */
    public static $basePath;

    /**
     * The namespace which holds the modules of the application.
     * @var string
     */
    public static $namespace;
    
    /**
     * The directory in which the code for the modules are stored
     * @var string
     */
    public static $modulesPath;

    /**
     *
     */
    public static $pluginsPath = "plugins/";

    /**
     * The directory uses for storing data which needs to be cached in the file
     * cache. This path is only necessary when the file caching method is
     * used.
     * @var string
     */
    public static $cachePath = "cache/";

    /**
     *
     */
    public static $cacheMethod = "file";

    public static $config;

    public static $debug = false;

    /**
     * The directory which contains the layouts for the current application.
     * @var string
     * @see Layout
     */
    public static $layoutsPath = "layouts/";

    /**
     * The default route to use when no route is specified in the URL.
     * @var unknown_type
     */
    public static $defaultRoute = "home";

    /**
     * The route which was requested through the URL. In cases where the route
     * is altered by the routing engine, this route still remains the same as
     * what was requested through the URL. The altered route can always be found
     * in the Ntentan::$route property.
     * @var string
     */
    public static $requestedRoute;

    /**
     * The routing table. An array of regular expressions and associated
     * operations. If a particular request sent in through the URL matches a
     * regular expression in the table, the associated operations are executed.
     *
     * @var array
     */
    public static $routes = array();

    /**
     * The route which is currently being executed. If the routing engine has
     * modified the requested route, this property would hold the value of the
     * new route.
     * @var string
     */
    public static $route;
    
    public static $prefix;
    
    /**
     * A runtime cache for singulars
     * @var array
     */
    private static $singulars = array();
    
    /**
     * A runtime cache for plurals
     * @var array
     */
    private static $plurals = array();
    
    /**
     * A runtime cache for camelisations
     * @var array
     */
    private static $camelisations = array();
    
    /**
     * A runtime cache for de-camelisation
     * @var array
     */
    private static $deCamelisations = array();
    
    /**
     * A runtime cache for loaded datastores
     * @var array
     */
    private static $loadedDatastores = array();
    
    public static $context;
    
    private static $errorDepth;
    
    const MAX_ERROR_DEPTH = 30;
    
    public static function getClassFile($class)
    {
        $key = "file_$class";
        if(Cache::exists($key))
        {
            $classFile = Cache::get($key);
        }
        else
        {
            $fullPath = explode("\\", $class);

            //Get rid of any initial empty class name
            if($fullPath[0] == "") array_shift ($fullPath);
            $class = array_pop($fullPath);


            if($fullPath[0] == \ntentan\Ntentan::$namespace)
            {
                $basePath = implode("/",$fullPath);
            }
            else if($fullPath[0] == 'ntentan' && $fullPath[1] == "plugins")
            {
                array_shift($fullPath);
                array_shift($fullPath);
                $basePath = \ntentan\Ntentan::getPluginPath(implode("/",$fullPath));
            }
            else if($fullPath[0] == 'ntentan' && $fullPath[1] == "dev")
            {
                array_shift($fullPath);
                array_shift($fullPath);
                $basePath = NTENTAN_DEV_HOME . '/' . implode("/",$fullPath);
            }
            else if($fullPath[0] == 'ntentan')
            {
                array_shift($fullPath);
                $basePath = \ntentan\Ntentan::getFilePath('lib/' . implode("/",$fullPath));
            }

            $classFile = $basePath . '/' . $class . '.php';
            Cache::add($key, $classFile);
        }
        return $classFile;
    }
    
    public static function autoload($class)
    {
        $classFile = self::getClassFile($class);
        if(file_exists($classFile))
        {
            require_once $classFile;
        }        
    }

    
    /**
     * A utility function which calls both the Ntentan::setup() and Ntentan::route()
     * methods at once. 
     * 
     * @see Ntentan::setup()
     * @see Ntentan::route()
     * @param array $config
     */
    public static function boot($config)
    {
        Ntentan::setup($config);
        
                
        if(!defined('STDOUT'))
        {
            Ntentan::route();
        }
    }

    /**
     * The main entry point of the Ntentan application. This method ensures that
     * ntentan is properly setup for service. It takes the configuration
     * data as a parameter. The details of the configuration parameter are
     * extracted from the config file.
     * 
     * @param array $config The configuration data.
     */
    public static function setup($config)
    {
        // setup autoloader
        spl_autoload_register("ntentan\Ntentan::autoload");
        
        // setup paths
        Ntentan::$basePath = $config['application']['ntentan_home'];
        Ntentan::$namespace = $config['application']['namespace'];
        Ntentan::$modulesPath = isset($config['application']['modules_path'])?
            $config['application']['modules_path']:
            $config['application']['namespace'];
        
        Ntentan::$prefix = $config['application']['prefix'];
        Ntentan::$context = $config['application']['context'];

        Ntentan::$cacheMethod = $config[Ntentan::$context]['caching'] == '' ? Ntentan::$cacheMethod : $config[Ntentan::$context]['caching'];
        Ntentan::$pluginsPath = $config[Ntentan::$context]['plugins'] == '' ? 'plugins/' : $config[Ntentan::$context]['plugins'];
        Ntentan::$debug = $config[Ntentan::$context]['debug'];
        Ntentan::$config = $config;

        // setup include paths
        Ntentan::addIncludePath(
            array
            (
                'lib/controllers/',
                'lib/models/',
                'lib/models/datastores/',
                'lib/models/exceptions/',
                'lib/views/',
                'lib/views/template_engines/',
                'lib/views/widgets/',
                'lib/exceptions/',
                'lib/caching/',
                'lib/sessions',
                '/',
                "./",
                Ntentan::$namespace,
                Ntentan::$layoutsPath,
            ),
            Ntentan::$basePath
        );
        
        // load cached items
        if(Cache::exists('nt_camelisations'))
        {
            Ntentan::$camelisations = Cache::get('nt_camelisations');
        }
        else
        {
            Ntentan::$camelisations = array();
        }
        $camelisations = count(Ntentan::$camelisations);        
        
        sessions\Manager::start();
    }

    /**
     * The routing engines entry. This method analyses the URL and implements
     * the routing engine.
     */
    public static function route()
    {
        // Implement the routing engine
        Ntentan::$requestedRoute = $_GET["q"];
        if(Ntentan::$route =='' ) Ntentan::$route = $_GET["q"];
        unset($_GET["q"]);
        unset($_REQUEST["q"]);

        if(Ntentan::$route == "") {
            Ntentan::$route = Ntentan::$defaultRoute;
        }
        else
        {
            foreach(Ntentan::$routes as $route)
            {
                if(preg_match($route["pattern"], Ntentan::$route, $matches) == 1)
                {
                    $parts = array();
                    if(isset($route["route"]))
                    {
                        $newRoute = $route["route"];
                        foreach($matches as $key => $value)
                        {
                            $newRoute = str_replace("::$key", $value, $newRoute);
                            $parts["::$key"] = $value;
                        }
                        Ntentan::$route = $newRoute;
                    }
                    if(is_array($route["globals"]))
                    {
                        foreach($route["globals"] as $key => $value)
                        {
                            define(
                                $key,
                                str_replace(array_keys($parts), $parts, $value)
                            );
                        }
                    }
                    break;
                }
            }
        }

        $module = controllers\Controller::load(Ntentan::$route);
        
        // Store all camelisations into the cache;
        if(count(Ntentan::$camelisations) > $camelisations)
        {
            Cache::add('nt_camelisations', Ntentan::$camelisations);
        }        
    }

    /**
     * A utility method to add a path to the list of include paths.
     * @param array $paths
     */
    public static function addIncludePath($paths, $prefix = null)
    {
        if(is_array($paths))
        {
            set_include_path(get_include_path() . PATH_SEPARATOR . $prefix . implode(PATH_SEPARATOR . $prefix, $paths));
        }
        else
        {
            set_include_path(get_include_path() . PATH_SEPARATOR . $paths);
        }
    }

    /**
     * Returns the path of a file which is supposed to be located within the
     * ntentan framework's directory. This method is mostly used internally
     * within the ntentan framework.
     * @param string $path
     */
    public static function getFilePath($path)
    {
        return Ntentan::$basePath . $path;
    }

    /**
     * Returns the path of a while which is supposed to be located within the
     * plugins directory. This method is mostle used internally within the
     * ntentan framework.
     * @param string $path
     */
    public static function getPluginPath($path)
    {
        return Ntentan::$pluginsPath . $path;
    }

    /**
     * Returns a url which has been formatted purposedly for the application.
     * @param unknown_type $url
     */
    public static function getUrl($url)
    {
        return (Ntentan::$prefix == '' ? '' : '/') . Ntentan::$prefix . ($url[0]!="/" ? "/$url" : $url);
    }
    
    public static function getRouteKey()
    {
         return str_replace('/', '_', Ntentan::$route);
    }

    /**
     * Write a header to redirect the request to a new location. In cases where
     * a redirect parameter exists in the request, the $url parameter of this
     * method is totally ignored.
     *
     * @param string $url The url to redirect to. This could be a full URL or a
     *                    route to an Ntentan controller.
     * @param unknown_type $absolute
     */
    public static function redirect($url, $absolute = false)
    {
        $url = isset($_GET["redirect"]) ? $_GET["redirect"] : $url;
        $url = $absolute === true ? $url : Ntentan::getUrl($url);
        header("Location: $url ");
    }

    /**
     * Returns the default datastore as defined in the config/db.php
     * configuration file.
     */
    public static function getDefaultDataStore($instance = false)
    {
        if(isset(Ntentan::$config[Ntentan::$context]['datastore']))
        {
            if($instance === true)
            {
                if(!isset(Ntentan::$loadedDatastores[Ntentan::$config[Ntentan::$context]['datastore']]))
                {
                    $dataStoreClass = "\\ntentan\\models\\datastores\\" . Ntentan::camelize(Ntentan::$config[Ntentan::$context]['datastore']);
                    if(class_exists($dataStoreClass))
                    {
                        Ntentan::$loadedDatastores[Ntentan::$config[Ntentan::$context]['datastore']] = new $dataStoreClass(Ntentan::$config[Ntentan::$context]);
                    }
                    else
                    {
                        throw new exceptions\DataStoreException("Datastore {$dataStoreClass} doesn't exist.");
                    }
                }
                return Ntentan::$loadedDatastores[Ntentan::$config[Ntentan::$context]['datastore']];
            }
            else
            {
                if(!isset(Ntentan::$config[Ntentan::$context]['datastore_class']))
                {
                    Ntentan::$config[Ntentan::$context]['datastore_class'] ="ntentan\\models\\datastores\\" . Ntentan::camelize(Ntentan::$config[Ntentan::$context]["datastore"]);
                }
                return Ntentan::$config[Ntentan::$context];
            }
        }
        else
        {
            echo Ntentan::message("Invalid datastore specified. Please specify a default datastore by providing a valid database configuration.");
            die();
        }
    }

    /**
     * Get the full URI which was sent in.
     */
    public static function getRequestUri()
    {
        return 'http'. ($_SERVER['HTTPS'] ? 's' : null) .'://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Converts an underscore seperated string into a sentence by replacing the
     * underscores with spaces and capitalizing the first character of all the
     * new words which are formed.
     * 
     * @param unknown_type $string
     */
    public static function toSentence($string)
    {
        return ucwords(str_replace("_", " ", $string));
    }

    /**
     * Returns the sigular form of any plural english word which is passed to it.
     * 
     * @param string $word
     * @see Ntentan::plural
     */
    public static function singular($word)
    {
        $singular = array_search($word, Ntentan::$singulars);
        if($singular == false)
        {
            if(substr($word, -3) == "ses")
            {
                $singular = substr($word, 0, strlen($word) - 2);
            }
            elseif(substr($word, -3) == "ies")
            {
                $singular = substr($word, 0, strlen($word) - 3) . "y";
            }
            elseif(strtolower($word) == "indices")
            {
                $singular = "index";
            }
            else if(substr(strtolower($word), -4) == 'news')
            {
                $singular = $word;
            }
            else if(substr(strtolower($word), -8) == 'branches')
            {
                $singular = substr($word, 0, strlen($word) - 2);
            }
            else if(substr($word, -1) == "s")
            {
                $singular = substr($word, 0, strlen($word) - 1);
            }
            else
            {
                $singular = $word;
            }
            Ntentan::$singulars[$singular] = $word;
        }
        return $singular;
    }

    /**
     * Returns the plural form of any singular english word which is passed to it.
     * 
     * @param string $word
     */
    public static function plural($word)
    {
        $plural = array_search($word, Ntentan::$plurals);
        if($plural === false)
        {
            if(substr($word, -1) == "y")
            {
                $plural = substr($word, 0, strlen($word) - 1) . "ies";
            }
            elseif(strtolower($word) == "index")
            {
                $plural = "indices";
            }            
            elseif(substr($word, -2) == "us")
            {
                $plural = $word . "es";
            } 
            elseif(substr($word, -2) == "ss")
            {
                $plural = $word . "es";
            }
            elseif(substr($word, -1) != "s")
            {
                $plural = $word . "s";
            }
            else
            {
				throw new exceptions\UnknownPluralException("Could not determine the plural for $word");
			}
            Ntentan::$plurals[$plural] = $word;
        }
        return $plural;
    }

    /**
     * Converts a dot separeted string or under-score separated string into
     * a camelcase format.
     * 
     * @param string $string The string to be converted.
     * @param string $delimiter The delimiter to be used as the trigger for capitalisation
     * @param string $baseDelimiter Another delimiter to be used as a second trigger for capitalisation
     * @param string $firstPartLowercase When set to true, the first letter of the camelcase returned is a lowecase character
     */
    public static function camelize($string, $delimiter=".", $baseDelimiter = "", $firstPartLowercase = false)
    {
        $key = $string . $delimiter . $baseDelimiter . ($firstPartLowercase?"1":"0") . "_camel";
        $camelized = array_search($key, Ntentan::$camelisations);
        if($camelized === false)
        {
            if($baseDelimiter == "") $baseDelimiter = $delimiter;
            $parts = explode($delimiter, $string);
            $camelized = "";
            foreach($parts as $i => $part)
            {
                $part = $delimiter == $baseDelimiter ? ucfirst(Ntentan::camelize($part, "_", $baseDelimiter)) : ucfirst($part);
                $camelized .= $firstPartLowercase === true ? lcfirst($part) : $part;
            }
            Ntentan::$camelisations[$camelized] = $key;
        }
        return $camelized;
    }
    
    public static function camelizeAndLowerFirst($string)
    {
        return Ntentan::camelize($string, '.', '', true);
    }

    /**
     * Converts a camel case string to an underscore separated string.
     * 
     * @param unknown_type $string
     */
    public static function deCamelize($string)
    {
        $deCamelized = array_search($string, Ntentan::$deCamelisations);
        if($deCamelized === false)
        {
            $deCamelized = "";
            for($i = 0; $i < strlen($string); $i++)
            {
                $char = substr($string, $i, 1);
                if(ctype_upper($char) && $i > 0)
                {
                    $deCamelized .= "_";
                }
                $deCamelized .= strtolower($char);
            }
            Ntentan::$deCamelisations[$deCamelized] = $string;
        }
        return $deCamelized;
    }

    /**
     * Adds a route to the routing engine of the system.
     * 
     * @param string $source
     * @param string $dest
     */
    public static function addRoute($source, $dest)
    {
        Ntentan::$routes[] = array($source, $dest);
    }

    /**
     * Returns true if the request is an AJAX request.
     * 
     * @return boolean
     */
    public static function isAjax()
    {
        if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true; else return false;
    }

    public static function error($message, $subTitle = null, $type = null, $showTrace = true, $trace = false)
    {
        Ntentan::$errorDepth++;
        if(isset(Ntentan::$config[Ntentan::$context]['error_handler']) && Ntentan::$debug === false && Ntentan::$errorDepth < Ntentan::MAX_ERROR_DEPTH)
        {
            $_GET['q'] = Ntentan::$config[Ntentan::$context]['error_handler'];
            Ntentan::route();
            Ntentan::$errorDepth--;
        }
        else
        {
            echo Ntentan::message($message, $subTitle);
            die();
        }
    }

    public static function message($message, $subTitle = null, $type = null, $showTrace = true, $trace = false)
    {
        if($showTrace === true)
        {
            $trace = is_array($trace) ? $trace : debug_backtrace();
        }
        ob_start();
        if(defined('STDIN'))
        {
            include Ntentan::getFilePath("templates/message-cli.tpl.php");
        }
        else
        {
            include Ntentan::getFilePath("templates/message.tpl.php");
        }
        $message = ob_get_clean();
        return $message;
    }

    /**
     * Default call back for displaying exceptions.
     * @param Exception $exception
     */
    public static function exceptionHandler($exception)
    {
        $class = new \ReflectionObject($exception);
        $logged = utils\Logger::log($exception->getMessage() . "\n" . $exception->getTraceAsString(), "logs/application.log");
        echo Ntentan::error(
            "Exception <code><b>{$class->getName()}</b></code> thrown in " .
            "<code><b>{$exception->getFile()}</b></code> on line " .
            "<code><b>{$exception->getLine()}</b></code>. " . 
             $exception->getMessage() .
             ( $logged === false ? 
                 "\n\n<p>Failed to log this exception. Please check and ensure" . 
                  "that the file [logs/application.log] exists and is" .
                  "writable.</p>" : ""
             ),
            "Exception <code>" . $class->getName() . "</code> thrown",
            null,
            true,
            $exception->getTrace()
        );
    }
}
