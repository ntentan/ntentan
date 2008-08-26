<?php
class TableLayout extends Container
{
	protected $num_rows;
	protected $num_columns;
	protected $elements = array();
	
	public function __construct($num_rows=-1, $num_columns=-1)
	{
		$this->num_rows = $num_rows;
		$this->num_columns = $num_columns;

		for($i=0; $i<$num_rows; $i++)
		{
			array_push($this->elements,array());
			for($j=0; $j<$num_columns; $j++)
			{
				array_push($this->elements[$i],array());	
			}
		}
	}
	
	public function add($element,$row=-1,$column=-1)
	{
		if($row==-1 || $column==-1)
		{
			parent::add($element);
		}
		else
		{
			array_push($this->elements[$column][$row],$element);
		}
	}
	
	/**
	 * Renders the form.
	 *
	 */
	public function render()
	{
		if($this->num_rows==-1 || $this->num_columns==-1)
		{
			foreach($this->elements as $element)
			{
				DefaultRenderer::render($element);
			}
		}
		else
		{
			print "<table>";
			//for($i=)
		}
	}
}
?>