<?php

include_once ("Container.php");
include_once ("HiddenField.php");
include_once ("DefaultRenderer.php");

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

	//! Not used.
	private $sendValidator;

	//! The name of the callback function.
	private $callback;

	//! The callback function to be called before submitting the form data.
	//! This function can be seen as the last point of validation. If it
	//! does not exist, it is ignored.
	private $preSaveCallback;

	//! The renderer that is used for rendering this form.
	protected $renderer;

	protected $showSubmit = true;

	public $submitAttributes;
	
	public $ajaxAction = "lib/fapi/ajax.php?action=save_data";
	
	public $successUrl;

	//! Constructor for initialising the forms. This constructor accepts
	//! the method of the form.
	public function __construct($method="")
	{
		parent::__construct();
		
		if($method=="") $method="POST";
		$this->setMethod($method);
		$this->ajax = true;
		$this->setSubmitValue("Save");
	}

	//! Validation of the form.
	public function validate($force_validation=false)
	{
		if($this->getMethod()=="POST") $sent=$_POST['is_form_'.$this->getId().'_sent'];
		if($this->getMethod()=="GET") $sent=$_GET['is_form_'.$this->getId().'_sent'];

		// Check if the form was sent or it is being forced to validate
		// some data.
		if($sent=="yes" || $force_validation == true)
		{
			// If validation is not being forced from some external
			// source then get the data from the form elements and
			// call a post save validation function to find out if the
			// data in the form was properly validated.
			//if(!$force_validation)
			//{
				$form_data = $this->getData($this->getStorable());
				if($this->preSaveCallback!="")
				{
					$preSaveCallback = $this->preSaveCallback;
					if($preSaveCallback($form_data, &$this->errors, &$this))
					{
						//print_r($this);
						$this->error = true;
						return false;
					}
				}
			/*}
			else
			{

			}*/

			// Call the parent container's validation method.
			if(parent::validate())
			{
				if($force_validation)
				{
					return true;
				}
				else
				{
					// If validation was successful but was not forced
					// call the callback method and save the data.
					$callback = $this->callback;
					$this->saveData();
					if($callback!="")
					{
						return $callback($form_data);
					}
				}
			}
		}

		// If form was not sent then retrieve the data.
		else
		{
			$data = $this->retrieveData();
			if(count($data)>0) $this->setData($data);
		}
		return false;
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
		$ret .= '<div id="fapi-submit-area">';
	
		$onclickFunction = "fapi_ajax_submit_".$this->getId()."()";
		$onclickFunction = str_replace("-","_",$onclickFunction);		
	
		if($this->getShowSubmit())
		{
			$submitValue = $this->submitValue?('value="'.$this->submitValue.'"'):"";
			if($this->ajaxSubmit)
			{
				$ret .= sprintf('<input class="fapi-submit" type="button" %s onclick="%s"  />',$submitValue,$onclickFunction);
			}
			else
			{
				$ret .= sprintf('<input class="fapi-submit" type="submit" %s />',$submitValue);
			}
		}
		$ret .= '</div>';
		$ret .= '<input type="hidden" name="is_form_'.$this->getId().'_sent" value="yes" />';
		$ret .= '<input type="hidden" name="is_form_sent" value="yes" />';
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
		if($this->store == Container::STORE_DATABASE || $this->store == Container::STORE_NONE)
		{
			$validate = $this->validate();
			if($validate===true)
			{
				return;
			}
			else if($validate!="")
			{
				return $validate;
			}
			else
			{
				return $this->renderForm();
			}
		}
		else
		{
			if($this->isFormSent())
			{
				$errors = $this->saveData();
				if($errors===true)
				{
					if($this->successUrl!="") header("Location: {$this->successUrl}");
				}
				else
				{
					$fields = array_keys($errors["errors"]);
					foreach($fields as $field)
					{
						foreach($errors["errors"][$field] as $error)
						{
							$element = $this->getElementByName($field);
							$element->addError(str_replace("%field_name%",$element->getLabel(),$error));
						}
					}
				}
			}
			$this->retrieveModelData();
			return $this->renderForm();
		}
	}

	/**
     * Sets the value that is written on the submit button.
     */
	public function setSubmitValue($submitValue)
	{
		$this->submitValue = $submitValue;
	}

	//! Set the callback function.
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	//! Set the pre save callback. This callback function is called
	//! before the form data is saved.
	public function setPreSaveCallback($callback)
	{
		$this->preSaveCallback = $callback;
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
}
?>
