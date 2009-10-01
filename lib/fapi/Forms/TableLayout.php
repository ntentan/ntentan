<?php
/**
 * A container for laying out form elements in a tabular manner.
 * @ingroup Form_API
 * @todo Orgarnize elements array such that other elements could alse use it.
 */
class TableLayout extends Container
{
	protected $tableElements = array();
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
		parent::__construct();
		$this->num_rows = $num_rows;
		$this->num_columns = $num_columns;
		$this->setId($id);
		for($i=0; $i<$num_rows; $i++)
		{
			array_push($this->tableElements,array());
			for($j=0; $j<$num_columns; $j++)
			{
				array_push($this->tableElements[$i],array());
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
		if($element->parent!=null) throw new Exception("Element being added to table already has a parent");
		if($row==-1 || $column==-1)
		{
			parent::add($element);
		}
		else
		{
			$this->tableElements[$row][$column][] = $element;
			/*$this->elements[] = $element;
			$element->setMethod($this->getMethod());
			$element->parent = $this;*/
			parent::add($element);
		}
		return $this;
	}

	/*public function getElements()
	{
		$data = array();
		for($row=0; $row<$this->num_rows; $row++)
		{
			for($column=0;$column<$this->num_columns; $column++)
			{
				foreach($this->tableElements[$row][$column] as $element)
				{
					$data[]=$element;
				}
			}
		}
		var_dump($data);
		die();
		return $data;
	}*/

	/**
	 * Renders the table.
	 *
	 */
	public function render()
	{
		$renderer_head = $this->renderer_head;
		$renderer_foot = $this->renderer_foot;
		$renderer_element = $this->renderer_element;
		$ret = "";
		if($render_head!="") $render_head();
		if($this->num_rows==-1 || $this->num_columns==-1)
		{
			foreach($this->elements as $element)
			{
				$ret .= $renderer_element($element,$this->getShowField());
			}
		}
		else
		{
			$ret = "<table class='fapi-table ".$this->getCSSClasses()."' ".($this->getId()!=""?"id='".$this->getId()."'":"")." >";
			for($row=0; $row<$this->num_rows; $row++)
			{
				$ret .= "<tr>";
				for($column=0;$column<$this->num_columns; $column++)
				{
					$ret .= "<td>".$renderer_head();
					foreach($this->tableElements[$row][$column] as $element)
					{
						$ret .= $renderer_element($element,$this->getShowField());
					}
					$ret .= $renderer_foot()."</td>";
				}
				$ret .= "</tr>";
			}
			$ret .= "</table>";
		}
		if($render_head!="") $ret .= $render_foot();
		return $ret;
	}

	/*public function setMethod($method)
	{
		$this->method = $method;
	}*/


	/*public function getData($storable=false)
	{
		var_dump($this->tableElements);
		$data = array();
		if($this->isFormSent())
		{
			for($row=0; $row<$this->num_rows; $row++)
			{
				for($column=0;$column<$this->num_columns; $column++)
				{
					foreach($this->tableElements[$row][$column] as $element)
					{
						//if()
						//$data+=$element->getData();
						if($storable)
						{
							if($element->getStorable()==true) $data+=$element->getData($storable);
						}
						else
						{
							$data+=$element->getData();
						}
					}
				}
			}
		}
		else
		{
			for($row=0; $row<$this->num_rows; $row++)
			{
				for($column=0;$column<$this->num_columns; $column++)
				{
					foreach($this->tableElements[$row][$column] as $element)
					{
						//if()
						//$data+=$element->getData();
						if($element->getType()=="Container")
						{
							$data+=$element->getData();
						}
						else
						{
							$data+=array($element->getName(false) => $element->getValue());
						}
					}
				}
			}
		}
		return $data;
	}*/

	/*public function setData($data)
	{
		for($row=0; $row<$this->num_rows; $row++)
		{
			for($column=0;$column<$this->num_columns; $column++)
			{
				foreach($this->tableElements[$row][$column] as $element)
				{
					$element->setData($data);
				}
			}
		}
    }

	public function validate()
	{
		$retval = true;
		for($row=0; $row<$this->num_rows; $row++)
		{
			for($column=0;$column<$this->num_columns; $column++)
			{
				foreach($this->tableElements[$row][$column] as $element)
				{
					if($element->validate()==false)
					{
						$retval=false;
					}
				}
			}
		}
		return $retval;
	}*/
}
?>
