<?php
/**
 * A container for laying out form elements in a tabular manner.
 *
 */
class TableLayout extends Container
{
	/**
	 * The number of rows available in the table. This value can be
	 * set to -1 if the table is to behave as an ordinary container. 
	 *
	 * @var integer
	 */
	protected $num_rows;
	
	/**
	 * The number of columns available in the table. This value can be
	 * set to -1 if the table is to behave as an ordinary container. 
	 * 
	 * @var unknown_type
	 */
	protected $num_columns;
	
	/**
	 * Setup the table.
	 *
	 * @param unknown_type $num_rows
	 * @param unknown_type $num_columns
	 */
	public function __construct($num_rows=-1, $num_columns=-1, $id="")
	{
		$this->num_rows = $num_rows;
		$this->num_columns = $num_columns;
		$this->setId($id);
		for($i=0; $i<$num_rows; $i++)
		{
			array_push($this->elements,array());
			for($j=0; $j<$num_columns; $j++)
			{
				array_push($this->elements[$i],array());	
			}
		}
	}
	
	/**
	 * Add an element to the table.
	 *
	 * @param $element The element to be added
	 * @param $row The row to add the element to. Count starts from 0.
	 * @param $column The column to add the element to. Count starts from 0.
	 */
	public function add($element,$row=-1,$column=-1)
	{
		if($row==-1 || $column==-1)
		{
			parent::add($element);
		}
		else
		{
			array_push($this->elements[$row][$column],$element);
			$element->setMethod($this->method);
		}
	}
	
	/**
	 * Renders the table.
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
			print "<table class='fapi-table ".$this->getCSSClasses()."' ".($this->getId()!=""?"id='".$this->getId()."'":"")." >";
			for($row=0; $row<$this->num_rows; $row++)
			{
				print "<tr>";
				for($column=0;$column<$this->num_columns; $column++)
				{
					print "<td>";
					foreach($this->elements[$row][$column] as $element)
					{
						DefaultRenderer::render($element);
					}
					print "</td>";
				}
				print "</tr>";
			}
			print "</table>";
		}
	}
	
	public function setMethod($method)
	{
		$this->method = $method;
	}
	
	
	public function getData()
	{
		$data = array();
		for($row=0; $row<$this->num_rows; $row++)
		{
			for($column=0;$column<$this->num_columns; $column++)
			{
				foreach($this->elements[$row][$column] as $element)
				{
					$data+=$element->getData();
				}
			}
		}
		return $data;
	}
	
	public function validate()
	{
		$retval = true;
		for($row=0; $row<$this->num_rows; $row++)
		{
			for($column=0;$column<$this->num_columns; $column++)
			{
				foreach($this->elements[$row][$column] as $element)
				{			
					if($element->validate()==false) 
					{
						$retval=false;
					}
				}
			}
		}
		return $retval;
	}
}
?>