<?php

include "controllers/Controller.php";

/**
 * Main class for managing the page. The whole website runs through this class.
 * It contains mainly a list of static methods.
 *
 */

class Application
{
	const TYPE_MODULE = "type_module";
	const TYPE_MODEL = "type_model";

	//! Initial template
	public static $template;

	//! The title of the page
	public static $title;

	//! The name of the site
	public static $site_name;

	//! An array containing all the stylesheets on the site.
	private static $stylesheets = array();

	//! An array containing all the javascripts on the site.
	private static $javascripts = array();

	//! The prefix of the website
	public static $prefix;

	//! An array of all the menus on the website.
	public static $menus = array();

    public static  $packagesPath;


	/**
	 * A method to add a stylesheet to the list of stylesheets
	 *
	 * @param string $href A path to the stylesheet
	 * @param string $media The media of the stylesheet. Defaults to all.
	 */
	public static function addStylesheet($href,$media="all")
	{
		if(array_search(array("href"=>$href,"media"=>$media),Application::$stylesheets)===false)
		{
			Application::$stylesheets[] = array("href"=>$href,"media"=>$media);
		}
	}

	public static function getLink($path)
	{
		return Application::$prefix.$path;
	}

	/**
	 * A method to add a javascript to the list of javascripts.
	 *
	 * @param string $href A path to the javascript.
	 */
	public static function addJavascript($href)
	{
		Application::$javascripts[] = $href;
	}

	/**
	 * Sets the title of the page. This method appends the title set to
	 * the name of the site.
	 *
	 * @param string $title
	 */
	public static function setTitle($title="")
	{
		if($title=="")
		{
			Application::$title = Application::$site_name;
		}
		else
		{
			Application::$title = $title . " | ". Application::$site_name;
		}
	}

	/**
	 * Outputs the site. This calls all the template files and outputs the
	 * final website.
	 *
	 */
	public static function render()
	{
		$t = new template_engine();
		if($_GET["q"]=="")
		{
			$_GET["q"]= "system/home";
		}
		$path = explode("/",$_GET["q"]);
		$mod_prefix = "";
		Application::$template = "main.tpl";

		require "app/bootstrap.php";
		$t->assign('prefix',Application::$prefix);

		Application::setTitle();

		$module = Controller::load($path);

		$t->assign('content',$module->content);
		$t->assign('module_name', $module->label);
		$t->assign('module_description',$module->description);

		foreach(array_keys(Application::$menus) as $key)
		{
			$t->assign($key, Menu::getContents($key));
		}

		$t->assign('stylesheets',Application::$stylesheets);
		$t->assign('styles',$t->fetch('stylesheets.tpl'));
		$t->assign('javascripts',Application::$javascripts);
		$t->assign('scripts',$t->fetch('javascripts.tpl'));
		$t->assign('title', Application::$title);
		$t->display(Application::$template);
	}
}

?>
