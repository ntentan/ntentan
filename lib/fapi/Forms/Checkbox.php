<?php
include_once "Field.php";

/**
 * A regular checkbox with a label.
 */
class Checkbox extends Field
{
	/**
	 * The value that this field should contain if this checkbox is checked.
	 */
	protected $checkedValue;

	/**
	 * Constructor for the checkbox.
	 *
	 * @param $label The label of the checkbox.
	 * @param $name The name of the checkbox used for the name='' attribute of the HTML output
	 * @param $description A description of the field.
	 * @param $value A value to assign to this checkbox.
	 */
	public function __construct($label="", $name="", $description="", $value="")
	{
		Element::__construct($label, $description);
		parent::__construct($name);
		$this->setCheckedValue($value);
	}

	/**
	 * Sets the value that should be assigned as the checked value for
	 * this check box.
	 * @param $checkedValue The value to be assigned.
	 * @return Checkbox
	 */
	public function setCheckedValue($checkedValue)
	{
		$this->checkedValue = $checkedValue;
		return $this;
    }

	/**
	 * Gets and returns the checkedValue for the check box.
	 * @return string
	 */
	public function getCheckedValue()
	{
		return $this->checkedValue;
    }

	public function render()
	{
		$ret = "";
		$ret .= '<input class="fapi-checkbox" type="checkbox" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getCheckedValue().'" '.
		      (($this->getValue()==$this->getCheckedValue())?"checked='checked'":"").' '.$this->getAttributes().' />';

		/*$ret .= '<span class="fapi-label">'.$this->getLabel()."</span>";*/
		return $ret;
	}

	public function getData($storable = false)
	{
		if(isset($_POST[$this->getName()]))
		{
			return parent::getData();
		}
		else
		{
			return array($this->getName(false) => null);
		}
	}

	public function getRequired()
	{
		return false;
	}

	/*public function getType()
	{
		return __CLASS__;
	}*/
}

?>
