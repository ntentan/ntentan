<?php

/**
 * Main class for managing the page. The whole website runs through this class.
 * It contains mainly a list of static methods.
 *
 */
class Ntentan
{
    const PATH_RELATIVE_TO_BASE = "pathRelativeToBasepath";
    const PATH_AS_IS = "pathAsIs";

    public static $basePath = "ntentan/";
    public static $packagesPath = "packages/";
    public static $cachePath = "cache/";

    public static $defaultRoute = "home";
    public static $routes = array();

	/**
	 * Outputs the site. This calls all the template files and outputs the
	 * final website.
	 */
	public static function boot()
	{
        Ntentan::addIncludePath(array(
                Ntentan::getFilePath('controllers/'),
                Ntentan::getFilePath('models/'),
                Ntentan::getFilePath('views/'),
                Ntentan::getFilePath('views/template_engines'),
            )
        );

		if($_GET["q"] == "")
		{
			$_GET["q"]= Ntentan::$defaultRoute;
		}
        
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
}

function __autoload($class)
{
    include "$class.php";
}

?>
