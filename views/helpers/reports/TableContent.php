<?php
class TableContent extends ReportContent
{
	protected $headers;
	protected $data;
	public $style;
	public $data_params = null;
	
	public function __construct($headers, $data, $data_params=null)
	{
		$this->headers = $headers;
		$this->data = $data;
		$this->style["decoration"] = true;
		$this->data_params = $data_params;
	}
	
	public function getTotals()
	{
		foreach($this->data as $fields)
		{
			$i = 0;
			foreach($fields as $field)
			{
				if(is_array($this->data_params['total']))
				{
					if(array_search($i,$this->data_params["total"])!==false)
					{
						$totals[$i]+=$field;
					}
				}
				$i++;
			}
		}
		return $totals;	
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	public function setData($data)
	{
		$this->data = $data;
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
