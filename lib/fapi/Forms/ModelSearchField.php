<?php
class ModelSearchField extends Field
{
	protected $searchFields = array();
	protected $model;
	protected $storedField;
	public $boldFirst = true;
	
	public function __construct($path=null,$value=null)
	{
		if($path!=null)
		{
			$info = model::resolvePath($path);
			if ($value=="") $value = $info["field"];
			$this->model = model::load($info["model"]);
			$field = $this->model->getFields(array($value));

			$this->setLabel($field[0]["label"]);
			$this->setDescription($field[0]["description"]);
			$this->setName($info["field"]);

			$this->addSearchField($value);
			$this->storedField = $info["field"];
		}
	}
	
	/**
	 * 
	 * @param $model
	 * @param $value
	 * @return ModelSearchField
	 */
	public function setModel($model,$value="")
	{
		$this->model = $model;
		$this->storedField = $value==""?$this->model->getKeyField():$value;
		return $this;
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
		$id = $this->getId();
		$hidden->addAttribute("id",$id);
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
		$text->addAttribute("onkeyup","fapiUpdateSearchField('$id','$path','$fields',this,".($this->boldFirst?"true":"false").")");
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
		
		$text->setId($id."_search_entry");		
		$ret .= $text->render();
		$ret .= "<div class='fapi-popup' id='{$id}_search_area'></div>";
		return $ret;
	}
}