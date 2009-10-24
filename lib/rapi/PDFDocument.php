<?php
class PDFDocument extends FPDF
{
	protected $processingTable;
	protected $tableWidths;
	protected $tableHeaders;
	public $header;
	public $footer;
	public $style;
	public $twidth;

	const ORIENTATION_PORTRAIT = "P";
	const ORIENTATION_LANDSCAPE = "L";

	public function __construct($orientation="L",$paper="A4")
	{
		parent::__construct($orientation,"mm",$paper);

		if(is_string($paper))
		{
			switch($paper)
			{
				case "A4":
					if($orientation=="L")
					$this->twidth = 277;
					else
					$this->twidth = 190;
					break;

				case "A5":
					if($orientation=="L")
					$this->twidth = 190;
					else
					$this->twidth = 128;
					break;
			}
		}

		$this->AddPage();
		$this->SetFont('Helvetica', null, 8);
		$this->SetAutoPageBreak(true, 15);
	}

	public function getTableWidths($headers=array(),$data=array())
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
		//$this->SetTextColor(128,128,128);
		$this->SetFont('Helvetica','I',8);
		if(strlen(trim($header))>0)
		{
			$this->Cell(0,0,$header,0,0,'L');
			$this->Ln(5);
		}

		if($this->processingTable) $this->tableHeader();
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('Helvetica','I',6);
		//$this->SetTextColor(128,128,128);
		$this->Cell(40,10,'Page '.$this->PageNo(),0,0);
		$this->Cell(0,10,"Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_name"],0,0,'R');
		$this->Ln();
	}

	protected function tableHeader()
	{
		$fill = false;
		$borders = 0;
		if($this->style["decoration"]===true)
		{
			$this->SetFillColor(102,128,102);
			$this->SetTextColor(255,255,255);
			$this->SetDrawColor(102,128,102);
			$fill = true;
			$borders = 1;
			$headingStyle = 'B';
		}

		//$this->SetFont('Helvetica',$headingStyle,8);
		$this->SetFont
		(	isset($this->style["font"])?$this->style["font"]:"Helvetica",
			"B", //($this->style["bold"]?"B":"").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
		isset($this->style["font_size"])?$this->style["font_size"]:8
		);

		for($i=0;$i<count($this->tableHeaders);$i++)
		{
			$this->Cell($this->tableWidths[$i],6,$this->tableHeaders[$i],$borders,0,'L',$fill);
		}

		$this->Ln();
	}

	public function totalsBox($totals,$params)
	{
		$this->SetFont
		(	isset($this->style["font"])?$this->style["font"]:"Helvetica",
		($this->style["bold"]?"B":"B").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
		isset($this->style["font_size"])?$this->style["font_size"]:8
		);
				
		$arrayWidth = $this->twidth * (isset($this->style["width"])?$this->style["width"]:1);
			
		foreach($params["widths"] as $i=>$width)
		{
			$params["widths"][$i] = $params["widths"][$i] * $arrayWidth;
		}
		
				
		/*if($this->style["decoration"]===true)
		{
			$this->SetDrawColor(102,128,102);
			$this->Cell(array_sum($params["widths"]),0,'','T');
			$this->Ln();
		}	*/	
		
		$this->SetDrawColor(204,255,204);
		for($i=0;$i<count($params["widths"]);$i++)
		{
			//if(isset($totals[$i])) $borders = "LR"; else $borders = 0;
			$this->Cell($params["widths"][$i],$this->style["cell_height"],$totals[$i],$borders,0,'L');
		}
		$this->Ln();

		if($this->style["decoration"]===true)
		{
			$this->SetDrawColor(102,128,102);
			$this->Cell(array_sum($params["widths"]),0,'','T');
			$this->Ln(0.4);
			$this->Cell(array_sum($params["widths"]),0,'','T');
			$this->Ln();
		}
	}

	public function table($headers,$data,$style=null,$params=null)
	{

		$this->style = $style!=null?$style:$this->style;

		if(isset($params["widths"]))
		{
			$widths = $params["widths"];
		}
		else
		{
			$widths = $this->getTableWidths($headers,$data);
		}

		$arrayWidth = $this->twidth * (isset($this->style["width"])?$this->style["width"]:1);
		$this->style["cell_height"] = isset($this->style["cell_height"])?$this->style["cell_height"]:4;


		foreach($widths as $i=>$width)
		{
			$widths[$i] = $widths[$i] * $arrayWidth;
		}
		
		

		$this->tableWidths = $widths;
		$this->tableHeaders = $headers;
		$this->tableHeader();
		
		$this->processingTable = true;
		
		$this->SetFillColor(204,255,204);
		$this->SetTextColor(0);
		$this->SetFont
		(	isset($this->style["font"])?$this->style["font"]:"Helvetica",
		($this->style["bold"]?"B":"").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
		isset($this->style["font_size"])?$this->style["font_size"]:8
		);

		if($this->style["decoration"]===true)
		{
			$this->SetDrawColor(204,255,204);
			$fill = true;
			$border = 1;
		}
		else
		{
			$fill = false;
			$border = 0;
		}

		foreach($data as $fields)
		{
			$i = 0;
			foreach($fields as $field)
			{
				$this->Cell($widths[$i],$this->style["cell_height"],$field,$border,0,'L',$fill);
				if(is_array($params['total']))
				{
					if(array_search($i,$params["total"])!==false)
					{
						$totals[$i]+=$field;
					}
				}
				$i++;
			}
			if($this->style["decoration"]===true) $fill=!$fill;
			$this->Ln();
		}

		if($this->style["decoration"]===true)
		{
			$this->SetDrawColor(102,128,102);
			$this->Cell(array_sum($widths),0,'','T');
		}

		/*if(count($totals)>0)
		{
			$this->totalsBox($totals,$params);
						$this->Ln(0.5);
			 $this->SetDrawColor(204,255,204);
			 for($i=0;$i<count($widths);$i++)
			 {
				$this->Cell($widths[$i],$this->style["cell_height"],$totals[$i],"LR",0,'L');
				}
				$this->Ln();

				if($this->style["decoration"]===true)
				{
				$this->SetDrawColor(102,128,102);
				$this->Cell(array_sum($widths),0,'','T');
				$this->Ln(0.4);
				$this->Cell(array_sum($widths),0,'','T');
				}
		}*/

		$this->Ln();
		$this->processingTable = false;
	}
}
?>