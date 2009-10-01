<?php
/**
 * 
 */
class TextContent extends ReportContent
{
	protected $text;
	protected $style = array();
	
	public function __construct($text)
	{
		$this->setStyle();
		$this->text = $text;
	}
	
	public function getType()
	{
		return "text";
	}
	
	public function setText($text)
	{
		$this->text = $text;
	}
	
	public function getText()
	{
		return $this->text;
	}
	
	public function setStyle($font="Helvetica",$size=12,$bold=false,$underline=false,$italics=false)
	{
		$this->style["font"] = $font;
		$this->style["size"] = $size;
		$this->style["bold"] = $bold;
		$this->style["underline"] = $underline;
		$this->style["italics"] = $italics;
	}
	
	public function getStyle()
	{
		return $this->style;
	}
}
?>
