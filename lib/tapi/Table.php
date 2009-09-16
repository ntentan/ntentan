<?php
class Table
{
	protected $data = array();
	protected $headers = array();
	protected $cellRenderers = array();
	protected $operations = array();
	protected $prefix;
	
	public function __construct($prefix,$headers=null, $data=null, $operations=null)
	{
		Application::addStyleSheet("css/tapi.css");
		$this->prefix = $prefix;
		$this->headers = $headers;
		$this->data = $data;
		$this->operations = $operations;
	}
	
	public function addOperation($link,$label=null,$action=null)
	{
		$this->operations[] = 
		array
		(
			"link"=>$link,
			"label"=>$label==null?$link:$label,
			"action"=>$action==null?$this->prefix.$link."/%key%":$action
		);
	}
	
	public function render()
	{
		$table = "<table class='tapi-table'>";
		 
		//Render Headers
		$table .= "<thead><tr><td>";
		$table .= "<input type='checkbox'></td><td>";
		$table .= implode("</td><td>",$this->headers);
		$table .= "</td><td>Operations</td></tr></thead>";
		 
		//Render Data
		$table .= "<tbody>";
		foreach($this->data as $row)
		{
			$key = array_shift($row);
			$table .= "<tr><td>";
			$table .= "<input type='checkbox'></td><td>";
			$table .= implode("</td><td>",$row);
			$table .= "</td><td>";
			if($this->operations!=null)
			{
				foreach($this->operations as $operation)
				{
					$table .= sprintf('<a class="tapi-operation tapi-operation-%s" href="%s">%s</a>',$operation["link"],str_replace(array("%key%","%path%"),array($key,$this->prefix.$operation["link"]),$operation["action"]),$operation["label"]);
				}
			}
			$table .= "</td></tr>";
		}
		$table .= "</tbody>";
		 
		$table .= "</table>";
		return $table;
	}
}
?>
