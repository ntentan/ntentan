<?php
/**
 * A special container which allows you to use multiple instances of
 * a single form to collect multiple data.
 *
 * @todo Work on displaying errors if a multi form fails to validate
 * @author james
 */
class MultiForms extends Container
{
	/**
	 * An instance of a fapi container which is used as a template form.
	 * @var Container
	 */
	protected $template;

	/**
	 * The total number of forms which have been rendered so far.
	 * @var int
	 */
	private static $numForms;

	/**
	 * A unique index for this instance of the MultiForm. This number is used in
	 * the ids of the outputted HTML code so that all instances of the MultiForms
	 * on any given page would have different DOM level ids for javascript
	 * manupulation.
	 * @var int
	 */
	protected $index;

	/**
	 * The label for this multiforms instance.
	 * @var unknown_type
	 */
	public $label;
	protected $templateName;
	protected $data = array();
	protected $referenceField;
	protected $relatedField;
	public $hasRelatedData = true;

	public function saveRelatedData($data)
	{
		if($this->referenceField=="")return;
		$referenced_model_info = Model::resolvePath($this->referenceField);
		$referenced_model = Model::load($referenced_model_info["model"]);
		$relative_value = $referenced_model->get(array($this->relatedField),"{$referenced_model_info["field"]}='{$data[$referenced_model_info["field"]]}'");
		$errors = array();

		foreach($this->data as $related_data)
		{
			$related_data[$this->relatedField] = $relative_value[0]["client_id"];
			$errors[] = $this->model->setData($related_data);
			$this->model->save();
		}

		return count($errors)>0?$errors:false;
	}

	public function setRelatedData($data)
	{
		if($this->referenceField=="")return;
		$referenced_model_info = Model::resolvePath($this->referenceField);
		$referenced_model = Model::load($referenced_model_info["model"]);
		$relative_value = $referenced_model->get(array($this->relatedField),"{$referenced_model_info["field"]}='{$data[$referenced_model_info["field"]]}'");
		$errors = array();

		foreach($this->data as $related_data)
		{
			$related_data[$this->relatedField] = $relative_value[0]["client_id"];
			$ret = $this->model->setData($related_data);
			if($ret!==true) $errors[] = $ret;
		}

		return count($errors)>0?$errors:false;
	}

	public function __construct()
	{
		parent::__construct();
		MultiForms::$numForms++;
		$this->index = MultiForms::$numForms;
	}

	public function validate()
	{
		$retval = true;
		foreach($this->data as $data)
		{
			foreach($data as $key => $dat)
			{
				$data[$this->templateName.".".$key."[]"] = $dat;
			}
			$this->clearErrors();
			$this->template->setData($data);
			$retval = $this->template->validate();
		}
		return $retval;
	}

	private function _retrieveData()
	{
		if($this->isFormSent())
		{
			$fields = $this->template->getFields();
			foreach($fields as $field)
			{
				$key = str_replace(array(".","[]"),array("_",""),$field->getName());
				for($i=0; $i<count($_POST[$key])-1; $i++)
				{
					$name = str_replace(array($this->templateName.".","[]"),array("",""),$field->getName());
					$field->setValue($_POST[$key][$i]);
					$this->data[$i][$name] = $field->getValue();
				}
			}
		}

		foreach($this->data as $data)
		{
			//var_dump($data);
			$this->template->setData($data);
			//var_dump($this->template->getData());
		}

		return $this->data;
	}

	public function getData($storable=false)
	{
		/*$u_data = array();
		$data = array();
		$fields = $this->template->getFields();
		foreach($fields as $field)
		{
			$key = str_replace(array(".","[]"),array("_",""),$field->getName());
			//print $key."<br/>";

			for($i=0; $i<count($_POST[$key])-1; $i++)
			{
				$name = str_replace(array($this->templateName.".","[]"),array("",""),$field->getName());
				$u_data[$i][$name] = $_POST[$key][$i];
			}
		}

		var_dump($data);*/
		$this->_retrieveData();
		//var_dump($this->data);
		return array($this->templateName => $this->data);
	}

	public function setData($data)
	{
		//$this->_retrieveData();
		//var_dump($data);
		$this->data = $data[$this->templateName];
	}

	public function setTemplate($template)
	{
		$this->template = $template;
		$template->addCssClass("fapi-multiform-sub");
		$buttons = new ButtonBar();
		$buttons->setId("multi-form-buttons");
		$buttons->addButton("Clear");
		$buttons->buttons[0]->addAttribute("onclick","fapiMultiFormRemove('--index--')");

		$elements = $template->getFields();
		foreach($elements as $element)
		{
			if($element->getType()=="Field")
			{
				$element->setId($element->getId()==""?$element->getName():$element->getId());
				$element->setName($template->getId().".".$element->getName()."[]");
			}

			$element->setId($element->getId()."_--index--");
		}
		$this->templateName = $template->getId();
		$template->setId("multiform-content---index--");
		$template->add($buttons);
		return $this;
	}

	public function setReferenceInformation($referenceField,$relatedField=null)
	{
		$this->referenceField = $referenceField;
		$this->relatedField = $relatedField==null?$referenceField:$relatedField;
		return $this;
	}

	public function setReferenceFieldValue($value)
	{
		$this->referenceFieldValue = $value;
	}

	public function render()
	{
		$id = "multiform-".$this->index;
		$this->setId($id);
		$attributes = $this->getAttributes();

		if($this->data==null)$this->_retrieveData();

		if($this->template != null)
		{
			$this->template->clearErrors();
			$template = $this->template->render();
			$count = 0;

			foreach($this->data as $index => $data)
			{
				foreach($data as $key => $dat)
				{
					$data[$this->templateName.".".$key."[]"] = $dat;
				}

				//$this->clearErrors();
				$this->template->setData($data);
				//$retval = $this->template->validate();
				$this->template->setId("multiform-content-".$index);
				$this->template->getElementById("multi-form-buttons")->buttons[0]->addAttribute("onclick","fapiMultiFormRemove('$count')");
				$contents .= "<div id='multi-form-content-$index'>".$this->template->render()."</div>";
			}
		}

		$ret = "<div $attributes >
				<input type='hidden' id='multiform-numitems-{$this->index}' value='$count'/>
					<div id='multiform-contents-{$this->index}'>
					$contents
					</div>
					<div class='fapi-multiform-bar'><span onclick='fapiMultiFormAdd({$this->index})' style='font-size:smaller;cursor:pointer'>Add New</span></div>
				</div>
				<div id='multiform-template-{$this->index}' style='display:none'>
					$template
				</div>";
		return $ret;
	}

	public function setShowField($showField)
	{
		$this->showfield = $showField;
		$this->template->setShowField($showField);
	}
}
?>
