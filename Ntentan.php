<?php
/**
 * Utility file with lots of classes which perform simple utilities in place.
 *
 * LICENSE:
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
 */

namespace ntentan;

include "autoload.php";

session_start();
date_default_timezone_set("Africa/Accra");
set_exception_handler(array("\\ntentan\\Ntentan", "exceptionHandler"));


/**
 * A utility class for the Ntentan framework. This class initializes all the 
 * routing and is the boilerplate code on which the entire Ntentan Framework
 * application operates.
 * 
 *  @author     James Ainooson <jainooson-at-gmail-dot-com>
 *  @license    Apache License, Version 2.0
 *  @package    ntentan
 */
class Ntentan
{
    public static $basePath = "ntentan/";
    public static $modulesPath = "modules/";
    public static $cachePath = "cache/";
    public static $layoutsPath = "layouts/";
    public static $blocksPath = "blocks/";
    public static $resourcesPath = "resources/";

    public static $defaultRoute = "home";
    public static $requestedRoute;
    public static $routes = array();
    public static $route;
    public static $dbConfigFile = "config/db.php";
    
	/**
	 * The main entry point of the Ntentan application.
	 */
	public static function boot()
	{
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
		                define($key, str_replace(array_keys($parts), $parts, $value));
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
     * 
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

    public static function getFilePath($path)
    {
        return Ntentan::$basePath . $path;
    }

    public static function getUrl($url)
    {
        if($url[0]!="/") return "/$url"; else return $url;
    }

    /**
     * Write a header to redirect the request to a new location. 
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
     * Returns the default datastore used by the 
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
    		echo Ntentan::message("Could not locate <b>config.php</b> file");
    		die();
    	}
    }

    public static function getRequestUri()
    {
        return 'http'. ($_SERVER['HTTPS'] ? 's' : null) .'://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function toSentence($string)
    {
        return ucwords(str_replace("_", " ", $string));
    }

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
        include "templates/message.tpl.php";
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
