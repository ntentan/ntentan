<?php
require_once "fpdf/fpdf.php";
require_once "PDFDocument.php";

/**
 * 
 * @author james
 *
 */
class PDFReport extends Report
{
	public $header;
	public $footer;
	protected $orientation;
	protected $paper;
	
	public function __construct($orientation="L",$paper="A4")
	{
		$this->orientation = $orientation;
		$this->paper = $paper;
	}

	public function output()
	{
		$pdf = new PDFDocument($this->orientation,$this->paper);

		foreach($this->contents as $content)
		{
			switch($content->getType())
			{
			case "text":
				$pdf->SetFont(
					$content->style["font"],
					
					($content->style["bold"]?"B":"").
					($content->style["underline"]?"U":"").
					($content->style["italics"]?"I":""),
					
					$content->style["size"]
				);
				$pdf->Cell(0,isset($content->style["height"])?$content->style["height"]:0,$content->getText(),0,0,$content->style["align"]);
				$pdf->Ln(!isset($content->style["height"])?3:0);//$content->style["height"]:3);
				break;

			case "table":
				$pdf->table($content->getHeaders(),$content->getData(),$content->style);
				break;
			case "logo":
				$pdf->image($content->image,null,null,8,8);
				$pdf->sety(10);
				$pdf->SetFont("Times","B","18");
				$pdf->cell(9);$pdf->cell(0,8,$content->title);
				
				$pdf->SetFont("Arial",null,7);
				$pdf->sety(10);
				foreach($content->address as $address)
				{
					$pdf->setx(($pdf->GetStringWidth($address)+10) * -1);
					$pdf->cell(0,3,$address);
					$pdf->Ln();
				}
				
				$pdf->Ln(5);
			}
		}

		$pdf->Output();
		die();
	}
}

?>
