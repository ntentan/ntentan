<?php
/**
 * A Controller inteded to automatically show links to other controllers that
 * are found within a directory which doesn't have either a controller or a
 * model.
 *
 * @author james
 */
class PackageController extends Controller
{
	public function __construct()
	{
		$this->_showInMenu = true;
	}

	public function getContents()
	{

	}
}
?>
