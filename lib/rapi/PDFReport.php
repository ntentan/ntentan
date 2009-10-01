<?php
require_once "fpdf/fpdf.php";
require_once "PDFDocument.php";

class PDFReport extends Report
{
	public $header;
	public $footer;
	public function __construct()
	{

	}

	public function output()
	{
		$pdf = new PDFDocument();

		foreach($this->contents as $content)
		{
			switch($content->getType())
			{
			case "text":
				$pdf->Cell(0,0,$content->getText());
				$pdf->Ln(5);
				break;

			case "table":
				$pdf->table($content->getHeaders(),$content->getData());
				break;
			}
		}

		$pdf->output();
		die();
	}
}

?>
