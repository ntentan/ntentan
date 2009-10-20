<?php
class PDFDocument extends FPDF
{
	protected $processingTable;
	protected $tableWidths;
	protected $tableHeaders;
	public $header;
	public $footer;

	public function __construct()
	{
		parent::__construct("L");
		$this->AddPage();
		$this->SetFont('Helvetica', null, 8);
		$this->SetAutoPageBreak(true, 15);
	}

	protected function getTableWidths($headers,$data)
	{
		$widths = array();
		foreach($headers as $i=>$header)
		{
			$widths[$i] = strlen($header) > $widths[$i] ? strlen($header) : $widths[$i];
		}
		foreach($data as $row)
		{
			$i = 0;
			foreach($row as $column)
			{
				$widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
				$i++;
			}
		}
		$max = array_sum($widths);
		foreach($widths as $i=>$width)
		{
			$widths[$i] = $widths[$i] / $max;
		}
		return $widths;
	}

	public function Header()
	{
		$this->SetTextColor(128,128,128);
		$this->SetFont('Helvetica','I',8);
		$this->Cell(0,0,$header,0,0,'L');
		$this->Ln(5);

		if($this->processingTable) $this->tableHeader();
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('Helvetica','I',6);
		$this->SetTextColor(128,128,128);
		$this->Cell(40,10,'Page '.$this->PageNo(),0,0);
		$this->Cell(0,10,"Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_name"],0,0,'R');
		$this->Ln();
	}

	protected function tableHeader()
	{
		$this->SetFillColor(0);
		$this->SetTextColor(255,255,255);
		$this->SetFont('Helvetica','B',8);
		$this->SetDrawColor(0);

		for($i=0;$i<count($this->tableHeaders);$i++)
		{
			$this->Cell($this->tableWidths[$i],6,$this->tableHeaders[$i],1,0,'L',true);
		}

		$this->Ln();
	}

	public function table($headers,$data)
	{
		$this->processingTable = true;
		$widths = $this->getTableWidths($headers,$data);
		$arrayWidth = 277;

		foreach($widths as $i=>$width)
		{
			$widths[$i] = $widths[$i] * $arrayWidth;
		}

		$this->tableWidths = $widths;
		$this->tableHeaders = $headers;
		$this->tableHeader();

		$this->SetFillColor(204,255,204);
		$this->SetTextColor(0);
		$this->SetFont('Helvetica',null,8);
		$this->SetDrawColor(204,255,204);
		$fill = true;

		foreach($data as $fields)
		{
			$i = 0;
			foreach($fields as $field)
			{
				$this->Cell($widths[$i],4,$field,1,0,'L',$fill);
				$i++;
			}
			$fill=!$fill;
			$this->Ln();
		}
		$this->Cell(array_sum($widths),0,'','T');
		$this->Ln();
		$this->processingTable = false;
	}
}
?>