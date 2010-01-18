<?php

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 *
 * @todo Change all labels from DIVs to LABEL
 * @todo Change all includes to requires
 *
 * @ingroup Form_API
 *
 */
class Form extends Container
{
	protected $ajaxSubmit;
	/**
	 * The value to be printed on the submit form.
	 */
	protected $submitValue;

	/**
	 * Flag to show wether this form has a reset button.
	 */
	protected $hasReset;

	/**
	 * The value to display on the reset button.
	 */
	protected $resetValue;


	//! The name of the callback function.
	//private $callback;

	//! The callback function to be called before submitting the form data.
	//! This function can be seen as the last point of validation. If it
	//! does not exist, it is ignored.
	//private $preSaveCallback;

	//! The renderer that is used for rendering this form.
	//protected $renderer;

	protected $showSubmit = true;

	public $submitAttributes;

	public $ajaxAction = "lib/fapi/ajax.php?action=save_data";

	public $successUrl;

    public $formId;
	
	//! Constructor for initialising the forms. This constructor accepts
	//! the method of the form.
	public function __construct($id="", $method="")
	{
		parent::__construct();
		if($method=="") $method="POST";
		$this->setMethod($method);
		$this->ajax = true;
		$this->setSubmitValue("Save");
        $this->setId($id);
	}

	protected function renderForm()
	{
		$this->addAttribute("method",$this->getMethod());
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("class","fapi-form");
		if($this->getHasFile()) $this->addAttribute("enctype","multipart/form-data");

		$ret = '<form '.$this->getAttributes().'>';
		if($this->getHasFile())
		{
			$ret .= "<input type='hidden' name='MAX_FILE_SIZE' value='10485760' />";
		}
		if($this->error)
		{
			$ret .= "<div class='fapi-error'><ul>";
			foreach($this->errors as $error)
			{
				$ret .= "<li>$error</li>";
			}
			$ret .= "</ul></div>";
		}
		$ret .= $this->renderElements();

		$onclickFunction = "fapi_ajax_submit_".$this->getId()."()";
		$onclickFunction = str_replace("-","_",$onclickFunction);

		if($this->getShowSubmit())
		{
			$ret .= '<div id="fapi-submit-area">';
			$submitValue = $this->submitValue?('value="'.$this->submitValue.'"'):"";
			if($this->ajaxSubmit)
			{
				$ret .= sprintf('<input class="fapi-submit" type="button" %s onclick="%s"  />',$submitValue,$onclickFunction);
			}
			else
			{
				$ret .= sprintf('<input class="fapi-submit" type="submit" %s />',$submitValue);
			}
			$ret .= '</div>';
		}
		$ret .= '<input type="hidden" name="is_form_'.$this->getId().'_sent" value="yes" />';
		$ret .= '</form>';

		if($this->ajaxSubmit)
		{
			$elements = $this->getFields();
			$ajaxData = array();
			foreach($elements as $element)
			{
				$id = $element->getId();
				if($element->getStorable())
				{
					$ajaxData[] = "'".urlencode($id)."='+document.getElementById('$id').".($element->getType()=="Field"?"value":"checked");
				}
				$validations = $element->getJsValidations();
				$validators .= "if(!fapiValidate('$id',$validations)) error = true;\n";
			}
			$ajaxData[] = "'fapi_dt=".urlencode($this->getDatabaseTable())."'";
			$ajaxData = addcslashes(implode("+'&'+", $ajaxData),"\\");

			$ret .=
			"<script type='text/javascript'>
			function $onclickFunction
			{
				var error = false;
				$validators
				if(error == false)
				{
					\$.ajax({
						type : 'POST',
						url : '{$this->ajaxAction}',
						data : $ajaxData
					});
				}
			}
			</script>";
		}
		return $ret;
	}

	//! Display all the form elements.
	public function render()
	{
		if($this->isFormSent())
		{
			$data = $this->getData();
			$validated = $this->validate() * ($this->validatorCallback==""?1:$this->executeCallback($this->validatorCallback,$data,$this,$this->validatorCallbackData));
			if($validated==1)
			{
				$this->executeCallback($this->callback,$data,$this,$this->callbackData);
			}
		}
		return $this->renderForm();
	}

	/**
     * Sets the value that is written on the submit button.
     */
	public function setSubmitValue($submitValue)
	{
		$this->submitValue = $submitValue;
	}

	public function setShowSubmit($showSubmit)
	{
		$this->showSubmit = $showSubmit;
	}

	public function getShowSubmit()
	{
		return $this->showSubmit;
	}

	public function setShowField($show_field)
	{
		Container::setShowField($show_field);
		$this->setShowSubmit($show_field);
	}

	public function useAjax($validation=true,$submit=true)
	{
		$this->ajax = $validation;
		$this->ajaxSubmit = $submit;
	}

    public function __toString()
    {
        return $this->render();
    }

    public function setId($id)
    {
        $this->formId = $id;
        parent::setId($id);
        return $this;
    }
}
