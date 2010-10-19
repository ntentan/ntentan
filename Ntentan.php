<?php
/**
 * File to contain the Ntentan class
 * 
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
 *
 * @package    ntentan
 * @author     James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @copyright  2010 James Ekow Abaka Ainooson
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    0.1
 * @since      0.1
 */

/**
 * Root namespace for all ntentan classes
 * @author ekow
 */
namespace ntentan;

/**
 * Include the autoloading function. This function automatically includes the 
 * source files for all classes whose source files are not found.
 */
include "autoload.php";

session_start();
date_default_timezone_set("Africa/Accra");
set_exception_handler(array("\\ntentan\\Ntentan", "exceptionHandler"));


/**
 * A utility class for the Ntentan framework. This class contains the information
 * necessary for routing. It also performs the routing of the pages and the
 * loading of the controllers. This class also has several utility methods
 * which help in the overall operation of the entire framework.
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
    public static $basePath = "ntentan/";
    
    /**
     * The directory which holds the modules of the application.
     * @var string
     */
    public static $modulesPath = "modules/";
    
    /**
     * The directory uses for storing data which needs to be cached in the file
     * cache. This path is only necessary when the file caching method is
     * used.
     * @var string
     */
    public static $cachePath = "cache/";
    
    /**
     * The directory which contains the layouts for the current application.
     * @var string
     * @see Layout
     */
    public static $layoutsPath = "layouts/";
    
    /**
     * The directory which contains the blocks for the current application.
     * @var string
     * @see Block
     */
    public static $blocksPath = "blocks/";
    
    /**
     * The directory which contains the resources used by the application.
     * Resources are public files such as images, stylesheets or javascripts
     * which are referenced from the application. Static HTML pages could also
     * be stored as resources.
     * @var string
     */
    public static $resourcesPath = "resources/";

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
    
    /**
     * The path to the file which holds the database configuration/
     * @var string
     */
    public static $dbConfigFile = "config/db.php";
    
    /**
     * Current ntentan version
     * @var string
     */
    const VERSION = "0.1";
    
	/**
	 * The main entry point of the Ntentan application. This method checks if
	 * ntentan is properly setup and then it implements the routing engine which
	 * loads the controllers to handle the request.
	 */
	public static function boot()
	{
	    // Check if the library was properly setup
	    if(!file_exists("config/ntentan.php"))
	    {
	        echo Ntentan::message(
	            "Please ensure that ntentan is properly setup. The <code>config/ntentan.php</code> file is not present."
	        );
	        die();
	    }
	    
	    // Setup the include path
	    require "config/ntentan.php";
	    Ntentan::$basePath = $ntentan_home;
	    Ntentan::$modulesPath = $modules_path;
        Ntentan::addIncludePath(
            array
            (
                Ntentan::getFilePath('controllers/'),
                Ntentan::getFilePath('models/'),
                Ntentan::getFilePath('models/datastores/'),
                Ntentan::getFilePath('models/exceptions/'),
                Ntentan::getFilePath('views/'),
                Ntentan::getFilePath('exceptions/'),
                Ntentan::getFilePath('caching/'),
                "./",
                Ntentan::$modulesPath
            )
        );
        
        // Do not go beyond this point if running in CLI mode
        if(defined('STDIN')===true)
        {
            return null;
        }
        
        // Implement the routing engine
        Ntentan::$requestedRoute = $_GET["q"];
        Ntentan::$route = $_GET["q"];
        unset($_GET["q"]);
        unset($_REQUEST["q"]);

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
		
        if(Ntentan::$route == "") {
            Ntentan::$route = Ntentan::$defaultRoute;
        }
        
		$module = controllers\Controller::load(Ntentan::$route);
	}

    /**
     * A utility method to add a path to the list of include paths.
     * @param array $paths 
     */
    public static function addIncludePath($paths)
    {
        if(is_array($paths))
        {
            foreach($paths as $path)
            {
                set_include_path(get_include_path() . PATH_SEPARATOR . $path);
            }
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
     * Returns a url which has been formatted purposedly for the application.
     * @param unknown_type $url
     */
    public static function getUrl($url)
    {
        if($url[0]!="/") return "/$url"; else return $url;
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
        $url = $absolute ? $url : Ntentan::getUrl($url);
        header("Location: $url ");
    }

    /**
     * Returns the default datastore as defined in the config/db.php 
     * configuration file.
     */
    public static function getDefaultDataStore()
    {
    	if(file_exists(Ntentan::$dbConfigFile)) {
            include Ntentan::$dbConfigFile;
            if(isset($datastores["default"])) { 
                return $datastores["default"];
            } else {
            	echo Ntentan::message("Invalid datastore specified. Please specify a default datastore");
            	die();
            }
    	} else {
    		echo Ntentan::message("Could not locate the database configuration file <code><b>".Ntentan::$dbConfigFile."</b></code>");
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
     * Returns the sigular form of any plural word which is passed to it.
     * @param string $string
     * @see Ntentan::plural
     */
    public static function singular($string)
    {
        if(substr($string,-3) == "ies")
        {
            return substr($string, 0, strlen($string) - 3) . "y";
        }
        else if(substr($string, -1) == "s")
        {
            return substr($string, 0, strlen($string) - 1);
        }
    }
    
    public static function plural($string)
    {
        if(substr($string, -1) == "y")
        {
            return substr($string, 0, strlen($string) - 1) . "ies";
        }
        elseif(substr($string, -1) != "s")
        {
            return $string . "s";
        }
    }
    
    public static function camelize($string, $delimiter=".", $baseDelimiter = "")
    {
        if($baseDelimiter == "") $baseDelimiter = $delimiter;
        $parts = explode($delimiter, $string);
        $ret = "";
        foreach($parts as $part)
        {
            $ret .= $delimiter == $baseDelimiter ? ucfirst(Ntentan::camelize($part, "_", $baseDelimiter)) : ucfirst($part);
        }
        return $ret;
    }
    
    public static function deCamelize($string)
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
        return $deCamelized;
    }
    
    public static function addRoute($source, $dest)
    {
        Ntentan::$routes[] = array($source, $dest);
    }
    
    public static function isAjax()
    {
        if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return true; else return false;
    }
    
    public static function error($message, $subTitle = null, $type = null) {
    	echo Ntentan::message($message, $subTitle);
    	die();
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
            include "templates/message-cli.tpl.php";
        }
        else
        {
            include "templates/message.tpl.php";
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
        echo Ntentan::message(
            $exception->getMessage(),
            null,
            null,
            true,
            $exception->getTrace()
        );
    }
}
