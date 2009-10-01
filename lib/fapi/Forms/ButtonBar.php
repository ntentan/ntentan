<?php
/**
 * A container for containing buttons.
 * @author james
 *
 */
class ButtonBar extends Container
{
	/**
	 * The buttons found in this contianer.
	 * @var Array
	 */
	public $buttons = array();

	/**
	 * Add a new button to this bar.
	 * @param $label The label for this button
	 * @return ButtonBar
	 */
	public function addButton($label)
	{
		$this->buttons[] = new Button($label);
		return $this;
	}

	public function render()
	{
		$ret = "";
		if(Element::getShowfield())
		{
			foreach($this->buttons as $button)
			{
				$ret .= $button->render(). " ";
			}
		}
		return $ret;
	}
}
?>
