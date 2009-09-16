<?php
class FileUploadField extends Field
{
	protected $destinationFile;
	protected $defaultFile;
	protected $destinationDirectory;
	protected $actualDirectory;

	public function __construct($label="",$name="",$description="",$value="",$destinationFile="")
	{
		Field::__construct($name,$value);
		Element::__construct($label, $description);
		$this->addAttribute("type","file");
		//$this->setStorable(false);
		$this->hasFile = true;
	}

	public function render()
	{
		$preview_file = "";
		$ret = "";
		if(is_file($this->destinationFile) && !is_dir($this->destinationFile))
		{
			$preview_file = $this->destinationFile;
		}
		if(is_file($this->destinationDirectory.$this->getValue()) && !is_dir($this->destinationDirectory.$this->getValue()))
		{
			$preview_file = $this->destinationDirectory.$this->getValue();
		}

		if($preview_file!=null)
		{
			$ret .= "<div class='fapi-description'><a href='$preview_file' target='_blank'>View current file</a></div>";
		}

		$this->addAttribute("class","fapi-textfield ".$this->getCSSClasses());
		$this->addAttribute("name",$this->getName());
		$this->addAttribute("id",$this->getId());
		$ret .= "<input {$this->getAttributes()} />";
		$ret .= "<input type='hidden' name='{$this->getName()}_orig_value' value='{$this->getValue()}' />";
		return $ret;
	}

	public function setActualDirector($directory)
	{
		$this->actualDirectory = $directory;
	}

	public function setDestinationDirectory($destinationDirectory)
	{
		$this->destinationDirectory = $destinationDirectory;
	}

	public function getDestinationDirectory()
	{
		return $this->destinationDirectory;
	}

	public function setDestinationFile($destinationFile)
	{
		$this->destinationFile = $destinationFile;
	}

	public function setDefaultFile($defaultFile)
	{
		$this->defaultFile = $defaultFile;
	}

	public function getData($storable=false)
	{
		$destination_file = $this->destinationDirectory."/".$_FILES[$this->getName()]["name"];
		if(move_uploaded_file($_FILES[$this->getName()]["tmp_name"],$destination_file))
		{
			//die("m returning $this->value");
			$this->value = $_FILES[$this->getName()]["name"];
			return array($this->getName(false) => $_FILES[$this->getName()]["name"]);
		}
		else if(is_file($this->destinationDirectory.$_POST[$this->getName()."_orig_value"]))
		{
			$this->value = $_POST[$this->getName()."_orig_value"];
			return array($this->getName(false) => $_POST[$this->getName()."_orig_value"]);
		}
		else
		{
			return array($this->getName(false) => $this->value);
		}
	}
}
?>