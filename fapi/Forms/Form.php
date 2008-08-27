<?php
include_once "Container.php";
include_once "HiddenField.php";
include_once "DefaultRenderer.php";

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 * 
 * @todo Change all labels from DIVs to LABEL
 * @todo Setup JavaScript hooks
 * @todo Remove the is_form_sent field from the value returned to the validated function
 * @todo Add styling hooks called from the various classes for CSS
 */
class Form extends Container
{	
	/**
	 * The value to be printed on the submit form.
	 *
	 * @var unknown_type
	 */
	protected $submitValue;
	
	/**
	 * Flag to show wether this form has a reset button.
	 *
	 * @var unknown_type
	 */
	protected $hasReset;
	
	/**
	 * The value to display on the reset button.
	 *
	 * @var unknown_type
	 */
	protected $resetValue;
	
	private $sendValidator;
	
	private $callback;
	
	protected $renderer;
	
	public function __construct($method="")
	{
		if($method=="") $method="POST";
		$this->setMethod($method);
		$this->sendValidator = new HiddenField("is_form_sent");
		$this->add($this->sendValidator);
		$this->renderer = "DefaultRenderer";
	}
	
	public function validate()
	{
		$form_data = $this->getData();
		if($form_data['is_form_sent']=="yes")
		{
			if(parent::validate())
			{
				$callback = $this->callback;
				$callback($form_data);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Renders the form.
	 *
	 */
	public function render()
	{
		if($this->validate()) return;
		$this->sendValidator->setValue("yes");
		print '<form method="'.$this->method.'" id="'.$this->id.'">';
		foreach($this->elements as $element)
		{
			DefaultRenderer::render($element);
		}
		print '<input type="submit" '.($this->submitValue?('value="'.$this->submitValue.'"'):"").' />';
		print '</form>';
	}
	
	/**
     * Sets the value that is written on the submit form.
     */
	public function setSubmitValue($submitValue)
	{
		$this->submitValue = $submitValue;
	}
	
	public function setCallback($callback)
	{
		$this->callback = $callback;
	}
	
}
?>