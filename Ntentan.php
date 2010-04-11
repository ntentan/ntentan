<?php
/**
 * Copyright 2008-2010 James Ainooson
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
 */

session_start();

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
    public static $packagesPath = "packages/";
    public static $cachePath = "cache/";
    public static $layoutsPath = "layouts/";
    public static $blocksPath = "blocks/";

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
                Ntentan::getFilePath('views/template_engines/'),
                Ntentan::getFilePath('blocks/'),
                "./",
                Ntentan::$packagesPath
            )
        );

		if($_GET["q"] == "")
		{
			$_GET["q"]= Ntentan::$defaultRoute;
		}
		
        Ntentan::$route = $_GET["q"];
        $requestedRoute = $_GET["q"]; 
		foreach(Ntentan::$routes as $route)
		{
            if(preg_match($route[0], $_GET["q"], $matches) == 1)
		    {
		        $requestedRoute = $route[1];
            }
		}		
        unset($_GET["q"]);
        unset($_REQUEST["q"]);
		$module = Controller::load($requestedRoute);
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
        include Ntentan::$configFile;
        return $datastores["default"];
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
    
    public static function addRoute($source, $dest)
    {
        Ntentan::$routes[] = array($source, $dest);
    }
    
    public static function isAjax()
    {
        if($_SERVER['X_REQUESTED_WITH'] == 'XMLHttpRequest') return true; else return false;
    }
        
    public static function message($message) 
    {
        return 
        "<html>
        <head>
            <style>
            #border
            {
                width:500px;
                margin-top:5px;
                margin-left:auto;
                margin-right:auto;
                border:1px solid #808080;
                background-color:#f0f0f0;
                padding:10px;
            }
            
            #border h1
            {
                margin:0px;
            }
            </style>
            <title>Ntentan Error!</title>
        </head>
        <div id='border'>
            <h1>Ntentan</h1>
            <p>$message</p>
         </div>
         </html>";   
    }
}

function __autoload($class)
{
    include "$class.php";
}