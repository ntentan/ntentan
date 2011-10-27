<?php
/*
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * Root namespace for all ntentan classes
 * @author ekow
 */
namespace ntentan;

session_start();


/**
 * Include the autoloading function. This function automatically includes the
 * source files for all classes whose source files are not found.
 */
include "autoload.php";

/**
 * Include a collection of utility global functions.
 */
include "globals.php";

date_default_timezone_set("Africa/Accra");
set_exception_handler(array("\\ntentan\\Ntentan", "exceptionHandler"));

include "caching/Cache.php";
use ntentan\caching\Cache;

/**
 * A utility class for the Ntentan framework. This class contains the routing
 * framework used for routing the pages. Routing involves the analysis of the
 * URL and the loading of the controllers which are requested through the URL.
 * This class also has several utility methods which help in the overall
 * operation of the entire framework.
 *
 *  @author     James Ainooson <jainooson@gmail.com>
 *  @license    Apache License, Version 2.0
 *  @package    ntentan
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
     * The directory which holds the modules of the application.
     * @var string
     */
    public static $modulesPath = "modules/";

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
    private static $singulars = array();
    private static $plurals = array();
    private static $camelisations = array();
    private static $deCamelisations = array();
    private static $loadedDatastores = array();

    /**
     * Current ntentan version
     * @var string
     */
    const VERSION = "0.5-rc1";

    /**
     * The main entry point of the Ntentan application. This method checks if
     * ntentan is properly setup and then it implements the routing engine which
     * loads the controllers responsoble for handling the request
     */
    public static function boot($config, $caching = null)
    {
        if(Cache::exists('nt_camelisations'))
        {
            Ntentan::$camelisations = Cache::get('nt_camelisations');
        }
        else
        {
            Ntentan::$camelisations = array();
        }
        $camelisations = count(Ntentan::$camelisations);
        
        Ntentan::setup($config);
        Ntentan::route();
        if(count(Ntentan::$camelisations) > $camelisations)
        {
            Cache::add('nt_camelisations', Ntentan::$camelisations);
        }
    }

    public static function setup($config)
    {
        Ntentan::$basePath = $config['application']['ntentan_home'];
        Ntentan::$modulesPath = $config['application']['modules_path'];
        Ntentan::$prefix = $config['application']['prefix'];
        define('CONTEXT', $config['application']['context']);

        Ntentan::$cacheMethod = $config[CONTEXT]['caching'] == '' ? Ntentan::$cacheMethod : $config[CONTEXT]['caching'];
        Ntentan::$pluginsPath = $config[CONTEXT]['plugins'] == '' ? 'plugins/' : $config[CONTEXT]['plugins'];
        Ntentan::$debug = $config[CONTEXT]['debug'];
        Ntentan::$config = $config;

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
                '/',
                "./",
                Ntentan::$modulesPath,
                Ntentan::$layoutsPath,
            ),
            Ntentan::$basePath
        );
    }

    public static function route()
    {
        // Implement the routing engine
        Ntentan::$requestedRoute = $_GET["q"];
        Ntentan::$route = $_GET["q"];
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
        return Ntentan::$prefix . ($url[0]!="/" ? "/$url" : $url);
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
        if(isset(Ntentan::$config[CONTEXT]['datastore']))
        {
            if($instance === true)
            {

                if(!isset(Ntentan::$loadedDatastores[Ntentan::$config[CONTEXT]['datastore']]))
                {
                    $dataStoreClass = "\\ntentan\\models\\datastores\\" . Ntentan::camelize(Ntentan::$config[CONTEXT]['datastore']);
                    if(class_exists($dataStoreClass))
                    {
                        Ntentan::$loadedDatastores[Ntentan::$config[CONTEXT]['datastore']] = new $dataStoreClass(Ntentan::$config[CONTEXT]);
                    }
                    else
                    {
                        throw new exceptions\DataStoreException("Datastore {$dataStoreClass} doesn't exist.");
                    }
                }
                return Ntentan::$loadedDatastores[Ntentan::$config[CONTEXT]['datastore']];
            }
            else
            {
                return Ntentan::$config[CONTEXT];
            }
        }
        else
        {
            echo Ntentan::message("Invalid datastore specified. Please specify a default datastore");
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
     * @param unknown_type $string
     */
    public static function toSentence($string)
    {
        return ucwords(str_replace("_", " ", $string));
    }

    /**
     * Returns the sigular form of any plural english word which is passed to it.
     * @param string $word
     * @see Ntentan::plural
     */
    public static function singular($word)
    {
        $singular = array_search($word, Ntentan::$singulars);
        if($singular === false)
        {
            if(substr($word,-3) == "ies")
            {
                $singular = substr($word, 0, strlen($word) - 3) . "y";
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
            elseif(substr($word, -1) != "s")
            {
                $plural = $word . "s";
            }
            Ntentan::$plurals[$plural] = $word;
        }
        return $plural;
    }

    /**
     * Converts a dot separeted string or under-score separated string into
     * a camelcase format.
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

    /**
     * Converts a camel case string to an underscore separated string
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
     * @param string $source
     * @param string $dest
     */
    public static function addRoute($source, $dest)
    {
        Ntentan::$routes[] = array($source, $dest);
    }

    /**
     * Returns true if the request is an AJAX request.
     * @return boolean
     */
    public static function isAjax()
    {
        if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true; else return false;
    }

    public static function error($message, $subTitle = null, $type = null)
    {
        if(isset(Ntentan::$config[CONTEXT]['error_handler']) && Ntentan::$debug === false)
        {
            $_GET['q'] = Ntentan::$config[CONTEXT]['error_handler'];
            Ntentan::route();
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
    
    private static function getLogFile($file = Ntentan::DEFAULT_LOG_FILE)
    {
    	fopen($file, 'a');
    }

    /**
     * Default call back for displaying exceptions.
     * @param Exception $exception
     */
    public static function exceptionHandler($exception)
    {
        $class = new \ReflectionObject($exception);
        if(is_writable("logs/exceptions.log"))
        {
            $logFile = fopen("logs/exceptions.log", "a");
            fputs($logFile, "[" . date("Y-m-d H:i:s") . "] [exception] " . $exception->getMessage() . "\n");
            fputs($logFile, "[" . date("Y-m-d H:i:s") . "] [exception] " . $exception->getTraceAsString() . "\n");
            fclose($logFile);
        }
        else
        {
            $logged = false;
        }
        echo Ntentan::message(
            "Exception <code><b>{$class->getName()}</b></code> thrown in 
             <code><b>{$exception->getFile()}</b></code> on line 
             <code><b>{$exception->getLine()}</b></code>. " . 
             $exception->getMessage() .
             ( $logged === false ? 
                 "<p>Failed to log this exception. Please check and ensure 
                  that the file [logs/exceptions.log] exists and is 
                  writable.</p>" : ""
             ),
            "Exception <code>" . $class->getName() . "</code> thrown",
            null,
            true,
            $exception->getTrace()
        );
    }
}
