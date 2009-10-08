<?php
class Toolbar
{
	protected $buttons = array();

	public function __construct($buttons=array())
	{
		$this->buttons = $buttons;
	}

	public function add($button)
	{
		$this->buttons[] = $button;
	}

	public function addLinkButton($label,$link,$icon=null)
	{
		$this->buttons[] = new LinkButton($label,$link,$icon);
	}

	public function render()
	{
		$ret = "<ul class='toolbar'>";
		foreach($this->buttons as $button)
		{
			$ret .= "<li class='toolbar-toolitem ".implode(" ",$button->getCssClasses())."'>".$button->render()."</li>";
		}
		$ret .= "<li style='clear:both'></li></ul>";
		return $ret;
	}
}
?>
