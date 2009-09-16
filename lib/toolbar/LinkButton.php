<?php
class LinkButton extends ToolbarItem
{
	protected $label;
	protected $link;
	protected $icon;
	
	public function __construct($label,$link,$icon=null)
	{
		$this->label = $label;
		$this->link = $link;
		$this->icon = $icon;
	}
	
	public function render()
	{
		return "<div>".($this->icon==null?"":"<img class='toolbar-icon toolbar-linkbutton-icon' src='{$this->icon}' />")."<a href='{$this->link}'>{$this->label}</a></div>";
	}
	
	public function getCssClasses()
	{
		return array("toolbar-linkbutton-".strtolower($this->label));
	}
}
?>
