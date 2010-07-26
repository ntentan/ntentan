<?php
include_once "Element.php";

/**
 * A standard radio button. Can be added to the radio button group.
 * @ingroup Form_API
 */
class RadioButton extends Field
{
	protected $checked_value;

	/**
	 * The constructor of the radio button.
	 *
	 * @param $label
	 * @param $value
	 * @param $description
	 * @param $id
	 */
	public function __construct($label="", $value="", $description="", $id="")
	{
		Element::__construct($label, $description, $id );
		//Field::__construct("", $value);
		$this->setCheckedValue($value);
	}

	/**
	 * Returns the type of the
	 *
	 * @return unknown
	 */
	public function getType()
	{
		return __CLASS__;
	}

	public function getCheckedValue()
	{
		return $this->checked_value;
	}

	public function setCheckedValue($checked_value)
	{
		$this->checked_value = $checked_value;
	}

	public function render()
	{
		$ret = "<input class='fapi-radiobutton ".$this->getCSSClasses()."' ".$this->getAttributes()." type='radio' name='".$this->getName()."' value='".$this->getCheckedValue()."' ".($this->getValue()==$this->getCheckedValue()?"checked='checked'":"")."/>";
		$ret .= '<span class="fapi-radiobutton-label">'.$this->getLabel()."</span>";
		$ret .= "<div class='fapi-description'>".$this->getDescription()."</div>";
		return $ret;
	}
}
?>
