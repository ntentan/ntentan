<?php
class ButtonBar extends Element
{
	public $buttons;
	
	public function addButton($label)
	{
		$this->buttons[] = new Button($label);
	}
	
	public function render()
	{
		$ret = "";
		foreach($this->buttons as $button)
		{
			$ret .= $button->render(). " ";
		}
		return $ret;
	}
}
?>
