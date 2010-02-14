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

	protected $showSubmit = true;

	public $submitAttributes;

	public $ajaxAction = "lib/fapi/ajax.php?action=save_data";

	public $successUrl;

    public $formId;
	
	//! Constructor for initialising the forms. This constructor accepts
	//! the method of the form.
	public function __construct($id="", $method="POST")
	{
		$this->setId($id);
        $this->setMethod($method);
	}

    public function addFileUploadSupport($maxFileSize = "")
    {
        $this->addAttribute("enctype", "multipart/form-data");
        $this->add(new HiddenField("MAX_FILE_SIZE", "10485760"));
    }

	public function render()
	{
		$this->addAttribute("method",$this->getMethod());
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("class","fapi-form");

		if($this->isFormSent())
		{
			$this->getData();
            $this->executeCallback();
        }

		$ret = '<form '.$this->getAttributes().'>';

		$ret .= $this->renderElements();

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

		return $ret;
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
