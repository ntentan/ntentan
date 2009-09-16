<?php
class ColumnContainer extends TableLayout
{
	public function __construct($num_columns=1)
	{
		parent::__construct(1,$num_columns);
	}
	
	public function render()
	{
		$elements_per_col = ceil(count($this->elements)/$this->num_columns);
		for($j = 0, $k=0; $j < $this->num_columns; $j++)
		{
			for($i=0;$i<$elements_per_col;$i++,$k++)
			{
				
				if($k<count($this->elements))
				{
					$this->elements[$k]->parent = null;
					parent::add($this->elements[$k],0,$j);
				}
				else
				{
					break;
				}
				//print $jl
			}
		}
		
		return parent::render();
	}
}
?>
