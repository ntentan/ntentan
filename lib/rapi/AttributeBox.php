<?php
class AttributeBox extends ReportContent
{
	public $data;
	public function __construct($data = null)
	{
		$this->data = $data;
	}
	
	public function getType()
	{
		return "attributes";
	}
}