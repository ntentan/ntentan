<?php

session_start();

/**
 * Main class for managing the page. The whole website runs through this class.
 * It contains mainly a list of static methods. 
 */
class Ntentan
{
    public static $basePath = "ntentan/";
    public static $packagesPath = "packages/";
    public static $cachePath = "cache/";

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
                "./",
                Ntentan::$packagesPath
            )
        );

		if($_GET["q"] == "")
		{
			$_GET["q"]= Ntentan::$defaultRoute;
		}
        Ntentan::$route = $_GET["q"];
		$module = Controller::load($_GET["q"]);
	}

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
        return "/$url";
    }

    public static function redirect($path, $absolute = false)
    {
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
}

function __autoload($class)
{
    include "$class.php";
}