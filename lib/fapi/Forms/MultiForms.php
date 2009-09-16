<?php
class MultiForms extends Container
{
	protected $template;
	private static $numForms;
	protected $index;
	public $label;
	
	public function __construct()
	{
		parent::__construct();
		MultiForms::$numForms++;
		$this->index = MultiForms::$numForms;
	}
	
	public function setTemplate($template)
	{
		$this->template = $template;
		$template->addCssClass("fapi-multiform-sub");
		$buttons = new ButtonBar();
		$buttons->addButton("Clear");
		$buttons->buttons[0]->addAttribute("onclick","fapiMultiFormRemove('--index--')");
		
		$elements = $template->getElements();
		foreach($elements as $element)
		{
			if($element->getType()=="Field")
			{
				$element->setId($element->getId()==""?$element->getName():$element->getId());
				$element->setName($template->getId().".".$element->getName()."[]");
			}
			$element->setId($element->getId()."_--index--");
		}
		$template->setId("multiform-content---index--");
		$template->add($buttons);
	}
	
	public function render()
	{
		$id = "multiform-".$this->index;
		$this->setId($id);
		$attributes = $this->getAttributes();
		if($this->template != null)
		{
			$template = $this->template->render();
		}
		
		$ret = "<div $attributes >
				<input type='hidden' id='multiform-numitems-{$this->index}' value='0'/>
					<div id='multiform-contents-{$this->index}'></div>
					<div class='fapi-multiform-bar'><span onclick='fapiMultiFormAdd({$this->index})' style='font-size:smaller;cursor:pointer'>Add New</span></div>
				</div>
				<div id='multiform-template-{$this->index}' style='display:none'>
					$template
				</div>";
		return $ret;
	}
}
?>
