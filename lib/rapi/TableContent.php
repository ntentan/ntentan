<?php
class TableContent extends ReportContent
{
	protected $headers;
	protected $data;
	
	public function __construct($headers, $data)
	{
		$this->headers = $headers;
		$this->data = $data;
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getType()
	{
		return "table";
	}
}
?>
