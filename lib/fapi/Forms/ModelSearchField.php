<?php
class ModelSearchField extends Field
{
	protected $searchFields = array();
	protected $model;
	protected $storedField;
	
	public function __construct($path,$value)
	{
		$info = model::resolvePath($path);
		$this->model = model::load($info["model"]);
		$field = $this->model->getFields(array($value));

		$this->setLabel($field[0]["label"]);
		$this->setDescription($field[0]["description"]);
		$this->setName($info["field"]);

		$this->addSearchField($value);
		$this->storedField = $info["field"];
	}
	
	public function addSearchField($field)
	{
		$this->searchFields[] = $field;
		return $this;
	}
	
	public function render()
	{
		$name = $this->getName();
		$hidden = new HiddenField($name,$this->getValue());
		$hidden->addAttribute("id",$this->getId());
		$ret = $hidden->render();
		
		$this->addSearchField($this->storedField);
		
		$object = array
		(
			"model"=>$this->model->package,
			"format"=>"json",
			"fields"=>$this->searchFields,
			"limit"=>20,
			"conditions"=>""
		);
		$jsonSearchFields = array_reverse($this->searchFields);
		$object = base64_encode(serialize($object));		
		$path = Application::$prefix."lib/models/urlaccess.php?object=$object";
		$fields = urlencode(json_encode($jsonSearchFields));
		
		$text = new TextField();
		$text->addAttribute("onkeyup","fapiUpdateSearchField('$name','$path','$fields',this)");
		$text->addAttribute("autocomplete","off");
		
		if($this->getValue()!="")
		{
			$data = $this->model[$this->getValue()];
			for($i=2;$i<count($jsonSearchFields);$i++)
			{
				$val .= $data[0][$jsonSearchFields[$i]]." ";
			}
			$text->setValue($val);
		}
		
		$text->setId($name."_search_entry");		
		$ret .= $text->render();
		$ret .= "<div class='fapi-popup' id='{$name}_search_area'></div>";
		return $ret;
	}
}