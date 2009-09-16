<?php
include "lib/smarty/libs/Smarty.class.php";

/**
 * Template engine subclass which contains all the initial settings
 * that the smarty engine needs to work.
 */
class template_engine extends Smarty
{
	function __construct()
	{
		parent::__construct();
		$this->template_dir = 'app/templates/';
		$this->compile_dir = 'app/templates_c/';
		$this->config_dir = 'config/smarty/';
		$this->cache_dir = 'app/cache/';
		$this->caching = false;
		//$this->assign('prefix',IndexPage::$prefix);
		$this->assign('host',$_SERVER["HTTP_HOST"]);
	}
}
?>
