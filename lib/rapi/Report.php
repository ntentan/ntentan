<?php
abstract class Report
{
	const CONTENT_TEXT = "text";
	const CONTENT_TABLE = "table";
	
	protected $contents = array();
	
	public abstract function output();
	
	public function add()
	{
		//$this->contents += func_get_args();
		$this->contents = array_merge($this->contents,func_get_args());
		return $this;
	}
}
?>
