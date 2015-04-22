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
 * Classes loaded here are likely to be called before the autoloader kicks in.
 */
require_once "globals.php";
require_once "caching/Cache.php";
require_once "exceptions/NtentanException.php";
require_once "exceptions/FileNotFoundException.php";
require_once "exceptions/ApiIniFileNotFoundException.php";

use ntentan\caching\Cache;
use ntentan\utils\Text;

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
    public static $home;
    
    /**
     * The home of the application
     */
    public static $appHome;

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
     * The cache method to be used
     */
    public static $cacheMethod = "file";

    public static $config;
    public static $configPath = 'config/';

    public static $debug = false;

    /**
     * The directory which contains the layouts for the current application.
     * @var string
     * @see Layout
     */
    public static $viewsPath = "views/";

    /**
     * The default route to use when no route is specified in the URL.
     * @var string
     */
    public static $defaultRoute = "home";
    
    /**
     * If some routing logic is used to rewrite the route then this default route
     * should apply.
     * @var string
     */
    public static $postRoutingDefaultRoute = "";

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
    
    public static $appName;
    
    const MAX_ERROR_DEPTH = 10;
    
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

            $classFile = Ntentan::$appHome . '/' . implode("/",$fullPath) . '/' . $class . '.php';
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
     * The main entry point of the Ntentan application. This method ensures that
     * ntentan is properly setup for service. It takes the configuration
     * data as a parameter. The details of the configuration parameter are
     * extracted from the config file.
     * 
     * @param array $ntentan The configuration data for ntentan
     * @param array $app The configuration data for the application
     */
    public static function setup($ntentan, $app = false)
    {
        // setup autoloader
        spl_autoload_register("ntentan\Ntentan::autoload");
        
        $configFile = Ntentan::$configPath . 'app.ini';
        
        if($app === false && !file_exists($configFile))
        {
            throw new exceptions\ApiIniFileNotFoundException("Config file *app.ini* not found");
        }
        else
        {
            $app = $app === false ? parse_ini_file($configFile, true) : $app;        
        }
        
        // hook in the custom exception handler
        set_exception_handler(array("\\ntentan\\Ntentan", "exceptionHandler"));
                
        // setup paths
        Ntentan::$home = $ntentan['home'];
        Ntentan::$namespace = $ntentan['namespace'];
        
        Ntentan::$modulesPath = isset($ntentan['modules_path'])?
            $ntentan['modules_path']:
            $ntentan['namespace'];
        
        Ntentan::$appHome = $app['home'] == '' ? '.' : $app['home'];    
        Ntentan::$appName = $ntentan['app'];
        Ntentan::$prefix = $app['prefix'];
        Ntentan::$context = $app['context'];
        
        Ntentan::$cacheMethod = $app[Ntentan::$context]['caching'] == '' ? 
            Ntentan::$cacheMethod : 
            $app[Ntentan::$context]['caching'];
            
        Ntentan::$debug = 
            $app[Ntentan::$context]['debug'] == 'true' || 
            $app[Ntentan::$context]['debug'] == 1 ? 
            true : false;
        
        unset($app['home']);
        unset($app['plugins']);
        unset($app['prefix']);
        unset($app['context']);
        
        views\template_engines\TemplateEngine::appendPath('views');
        views\template_engines\TemplateEngine::appendPath('views/default');
        views\helpers\Helper::setBaseUrl(Ntentan::getUrl(''));
        
        Ntentan::$config = $app;

        logger\Logger::init("logs/application.log");
        
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
        
        if(!defined('STDOUT'))
        {
            sessions\Manager::start();
        }
    }

    /**
     * The routing engines entry. This method analyses the URL and implements
     * the routing engine.
     */
    public static function route()
    {
        // Implement the routing engine
        Ntentan::$requestedRoute = $_GET["q"];
        if(Ntentan::$route =='' ) Ntentan::$route = Ntentan::$requestedRoute;
        unset($_GET["q"]);
        unset($_REQUEST["q"]);

        if(Ntentan::$route == "") 
        {
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
                            $GLOBALS["ROUTE_$key"] =str_replace(array_keys($parts), $parts, $value);
                        }
                    }
                    break;
                }
            }
        }
        
        if(Ntentan::$route == "") 
        {
            Ntentan::$route = isset($route['default']) ? 
                $route['default'] : Ntentan::$postRoutingDefaultRoute;
        }        

        controllers\Controller::load(Ntentan::$route);
        
        // Store all camelisations into the cache;
        if(count(Ntentan::$camelisations) > $camelisations)
        {
            Cache::add('nt_camelisations', Ntentan::$camelisations);
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
        return __DIR__ . "/../$path";
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
    
    private static function getDatastoreConfig()
    {
        if(!isset(Ntentan::$config['db']))
        {
            if(file_exists(Ntentan::$configPath . 'db.ini'))
            {
                $db = parse_ini_file(Ntentan::$configPath . 'db.ini', true);
                Ntentan::$config['db'] = $db[Ntentan::$context];
                return true;
            }
            else
            {
                return false;
            }
        }
        elseif(isset(Ntentan::$config['db']))
        {
            return true;
        }
    }

    /**
     * Returns the default datastore as defined in the config/db.ini
     * configuration file.
     */
    public static function getDefaultDataStore($instance = false)
    {
        if(self::getDatastoreConfig())
        {
            if($instance === true)
            {
                if(!isset(Ntentan::$loadedDatastores[Ntentan::$config['db']['datastore']]))
                {
                    $dataStoreClass = "\\ntentan\\models\\datastores\\" . Text::ucamelize(Ntentan::$config['db']['datastore']);
                    if(class_exists($dataStoreClass))
                    {
                        Ntentan::$loadedDatastores[Ntentan::$config['db']['datastore']] = new $dataStoreClass(Ntentan::$config['db']);
                    }
                    else
                    {
                        throw new exceptions\DataStoreException("Datastore {$dataStoreClass} doesn't exist.");
                    }
                }
                return Ntentan::$loadedDatastores[Ntentan::$config['db']['datastore']];
            }
            else
            {
                if(!isset(Ntentan::$config['db']['datastore_class']))
                {
                    Ntentan::$config['db']['datastore_class'] ="ntentan\\models\\datastores\\" . Text::ucamelize(Ntentan::$config['db']["datastore"]);
                }
                return Ntentan::$config['db'];
            }
        }
        else
        {
            throw new models\exceptions\DataStoreException("Could not find a suitable datastore");
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
            controllers\Controller::load(Ntentan::$config[Ntentan::$context]['error_handler']);
        }
        else
        {
            ob_clean();
            echo Ntentan::message($message, $subTitle, $type, $showTrace, $trace);
        }
    }  

    public static function message($message, $subTitle = null, $type = null, $showTrace = true, $trace = false)
    {
        // Be silent in production systems at all cost
        if(Ntentan::$debug == false) return;
        
        if($showTrace === true)
        {
            $trace = is_array($trace) ? $trace : debug_backtrace();
        }
        ob_start();
        if(defined('STDERR') || ini_get('html_errors') == 'off' || ini_get('html_errors') == '0')
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
        $logged = logger\Logger::log(logger\Logger::INFO, $exception->getMessage() . "\n" . $exception->getTraceAsString());
        
        echo Ntentan::error(
            "Exception <code><b>{$class->getName()}</b></code> thrown in " .
            "<code><b>{$exception->getFile()}</b></code> on line " .
            "<code><b>{$exception->getLine()}</b></code>. " . 
             $exception->getMessage() .
             ( $logged === false ? 
                 "\n\n<p>Failed to log this exception. Please check and ensure " . 
                  "that the file [logs/application.log] exists and is " .
                  "writable.</p>" : ""
             ),
            "Exception <code>" . $class->getName() . "</code> thrown",
            null,
            true,
            $exception->getTrace()
        );
    }
}
