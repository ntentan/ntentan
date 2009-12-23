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

	/**
	 * Outputs the site. This calls all the template files and outputs the
	 * final website.
	 */
	public static function boot()
	{
        Ntentan::addIncludePath(Ntentan::getFilePath('controllers/'));
        Ntentan::addIncludePath(Ntentan::getFilePath('models/'));
        Ntentan::addIncludePath(Ntentan::getFilePath('views/'));

		if($_GET["q"] == "")
		{
			$_GET["q"]= "home";
		}
		$module = Controller::load($_GET["q"]);
	}

    public static function addIncludePath($path)
    {
        set_include_path(get_include_path(). PATH_SEPARATOR . $path);
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
