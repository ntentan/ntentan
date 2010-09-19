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
    public static $routes = array();
    public static $route;
    public static $configFile = "config.php";
    
	/**
	 * Outputs the site. This calls all the template files and outputs the
	 * final website.
	 */
	public static function boot()
	{
        Ntentan::addIncludePath(
            array
            (
                Ntentan::getFilePath('controllers/'),
                Ntentan::getFilePath('models/'),
                Ntentan::getFilePath('models/datastores/'),
                Ntentan::getFilePath('views/'),
                "./",
                Ntentan::$modulesPath
            )
        );
        
        if(isset($_GET["q"])) {
        	$query = $_GET["q"];
            unset($_GET["q"]);
            unset($_REQUEST["q"]);
			if($query == "") {
				$query = Ntentan::$defaultRoute;
			}
        } else {
        	$query = Ntentan::$defaultRoute;
        }
		
        Ntentan::$route = $query;
        $requestedRoute = $query; 
		foreach(Ntentan::$routes as $route)
		{
            if(preg_match($route[0], $query, $matches) == 1)
		    {
		        $requestedRoute = $route[1];
            }
		}		
        unset($query);
        unset($query);

		$module = controllers\Controller::load($requestedRoute);
	}

    /**
     * 
     * @param <type> $paths 
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

    public static function redirect($path, $absolute = false)
    {
        $path = isset($_GET["redirect"]) ? $_GET["redirect"] : $path;
        $path = $absolute ? $path : Ntentan::getUrl($path);
        header("Location: $path ");
    }

    public static function getDefaultDataStore()
    {
    	if(file_exists(Ntentan::$configFile)) {
            include Ntentan::$configFile;
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
        
    public static function message($message, $subTitle = null, $type = null) {
        debug_print_backtrace();
        return 
        "<html>
        <head>
            <style>
            #message
            {
                width:40%;
                margin-top:50px;
                margin-left:auto;
                margin-right:auto;
                background-color:#f0f0f0;
                padding:10px;
                border-radius:10px;
                -moz-border-radius:10px;
                font-family:sans-serif;
                color:#404040;
                box-shadow: 0px 1px 2px rgba(0, 0, 0, .5);
                -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, .5);
                -moz-box-shadow: 0px 1px 2px rgba(0, 0, 0, .5);
            }
            
            #message h1
            {
                margin:0px;
                color:black;
            }
            </style>
            <title>Ntentan Error!</title>
        </head>
        <div id='message'>
            <h1>Ntentan</h1>
            <p>$message</p>
         </div>
         </html>";   
    }
}
