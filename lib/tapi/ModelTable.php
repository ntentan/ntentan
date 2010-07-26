<?php
class ModelTable extends Table
{
	/**
	 * @var Model
	 */
	protected $model;
	public $useAjax;
	protected $params;
	private $numPages;
	protected $fields;
	private $searchScript;
	protected $fieldInfo;
    protected $conditions;
	
	public function __construct($prefix)
	{
		parent::__construct($prefix);
	}
	
	public function setModel($model,$params,$concatenatedLabels=null)
	{
        //var_dump($concatenatedLabels);
		$headers = $model->getLabels($params["fields"]);
		array_shift($headers);
		$this->data = $model->get($params);
		//$this->data = $model->formatData();

        if(count($concatenatedLabels)>0)
        {
            foreach($headers as $key=>$header)
            {
                if($header == "Concatenated Field")
                {
                    $headers[$key] = (string)array_shift($concatenatedLabels);
                }
            }
        }

		$this->headers = $headers;		
		$this->model = $model;
		$this->params = $params;
		$this->fields = $params["fields"];
		$this->fieldInfo = $this->model->getFields($this->fields);
		
		//var_dump($this->fields);
		array_shift($this->fields);		
		
		foreach($this->fieldInfo as $field)
		{
			switch($field["type"])
			{
				case "integer":
				case "double":
					$this->headerParams[$field["name"]]["type"] = "number";
                    if(isset($field["value"]))
                    {
                       $this->conditions.="{$field["name"]}=='".(string)$field["value"]."',";
                    }
					break;
                
                case "enum":
					$this->headerParams[$field["name"]]["type"] = "number";
                    if(isset($field["value"]))
                    {
                       $this->conditions.=$this->model->database.".{$field["name"]}=='{$field["value"]}',";
                    }
					break;
            }
		}
	}
	
	protected function renderHeader()
	{
		$searchFunction = $this->name."Search()";
		$table = "<table class='tapi-table' id='$this->name'>";
		 
		//Render Headers
		$table .= "<thead><tr><td>";
		$table .= "<input type='checkbox' onchange=\"ntentan.tapi.checkToggle('$this->name',this)\"></td>";
				//$table .= implode("</td><td>",$this->headers);
		
		foreach($this->headers as $i => $header)
		{
			$table.="<td onclick=\"ntentan.tapi.sort('".$this->name."','".$this->params["fields"][$i+1]."')\">
			$header
			</td>";
		}
		$table .= "<td>Operations</td></tr>";
		
		
		//Remder search fields
		$fields = $this->model->getFields($this->fields);
		$table .= "<tr id='tapi-$this->name-search' class='tapi-search-row' ><td></td>";
		foreach($this->headers as $i => $header)
		{
			$table.="<td>";
			switch($fields[$i]["type"])
			{
				case "string":
				case "text":
					$text = new TextField();
					$text->setId($fields[$i]["name"]);
					$text->addAttribute("onkeyup",$searchFunction);
					$table .= $text->render();
					$name = $fields[$i]["name"];
					$this->searchScript .= "if($('#$name').val()!='') condition += escape('$name='+$('#$name').val()+',');";
					break;
				case "reference":
					$text = new TextField();
					$text->setId($fields[$i]["name"]);
					$text->addAttribute("onkeyup",$searchFunction);
					$table .= $text->render();
                    $modelInfo = Model::resolvePath($fields[$i]["reference"]);
                    $model = Model::load($modelInfo["model"]);
                    $fieldName = $model->database.".".$fields[$i]["referenceValue"];
                    $this->searchScript .= "if($('#{$fields[$i]["name"]}').val()!='') condition += escape('$fieldName='+$('#{$fields[$i]["name"]}').val()+',');";
					break;
                    /*$list = new ModelSearchField($fields[$i]["reference"],$fields[$i]["referenceValue"]);
                    $list->boldFirst = false;
					$list->setId($fields[$i]["name"]);
                    $list->addAttribute("onChange",$searchFunction);
					$table .= $list->render();
                    $modelInfo = Model::resolvePath($fields[$i]["reference"]);
                    $model = Model::load($modelInfo["model"]);
                    $fieldName = $model->database.".".$field[$i]["name"];
                    $this->searchScript .= "if($('#{$field["name"]}').val()!='') condition += escape('$fieldName='+$('#{$field["name"]}').val()+',');";
					break;*/
				/*case "enum":
					$list = new SelectionList();
					foreach($fields[$i]["options"] as $value => $label)
					{
						$list->addOption($label,$value);
					}
					$list->setId($fields[$i]["name"]);
					$table.=$list->render();
					break;
				case "integer":
				case "double":
					$options = Element::create("SelectionList")->
								addOption("Equals",0)->
								addOption("Greater than",1)->
								addOption("Less than",2);
					$text = new TextField();
					$text->setId($fields[$i]["name"]);
					$table .= $options->render().$text->render();
					break;					
				case "date":
					$date = new DateField();
					$date->setId($fields[$i]["name"]);
					$table .= $date->render();
					break;
				case "boolean":
					$options = Element::create("SelectionList")->
								addOption("Yes",1)->addOption("No",0);
					$options->setId($fields[$i]["name"]);
					$table .= $options->render();
					break;*/
			}
			$table .="</td>";
		}
		$table .= "<td><input class='fapi-button' type='button' value='Search' onclick='$searchFunction'/></td></tr></thead>";
				 
		//Render Data
		$table .= "<tbody id='tbody'>";
		return $table;		
	}
	
	public function renderFooter()
	{
		$table = parent::renderFooter();
		$lastPage = $this->numPages - 1;
		for($i =0; $i < $this->numPages;$i++)
		{
			$options.="<option value='$i' ".($_REQUEST["page"]==$i?"selected='selected'":"")." >".($i+1)."</option>";
		}
		$table .= "<div id='{$this->name}Footer'>
			<ul class='table-pages'>".
				($_REQUEST["page"]>0?"<li><a onclick=\"ntentan.tapi.switchPage('$this->name',0)\">&lt;&lt; First</a></li><li><a onclick=\"ntentan.tapi.switchPage('$this->name',".($_REQUEST["page"]-1>=0?$_REQUEST["page"]-1:"").")\">&lt; Prev</a></li>":"").
				($_REQUEST["page"]<$lastPage?"<li><a onclick=\"ntentan.tapi.switchPage('$this->name',".($_REQUEST["page"]+1<=$lastPage?$_REQUEST["page"]+1:"").")\">Next &gt;</a></li><li><a onclick=\"ntentan.tapi.switchPage('$this->name',$lastPage)\">Last &gt;&gt;</a></li>":"").
				"<li>|</li>
				<li>Page <select onchange=\"ntentan.tapi.switchPage('$this->name',this.value)\">$options</select> of $this->numPages</li>
			</ul>
		</div>"; 
		
		return $table;
	}
	
	public function render($renderHeaders=true)
	{
		$pages = 10;
		//$table = parent::render($renderHeaders);
		if($renderHeaders) $table = $this->renderHeader();
		
		
		
		if($this->useAjax)
		{
			$table .= "<tr><td><img src='".Application::$prefix."images/loading-image-big.gif' /></td></tr>";
		}
		else
		{
			$table .= parent::render(false);
		}
		
		//if($renderHeaders) $table .= $this->renderFooter();
		$this->params["enumerate"] = true;
		$data = $this->model->get($this->params);
		$this->numPages = ceil($data[0]["count"]/$pages);
		if($renderHeaders) $table .= $this->renderFooter();
		
		if($this->useAjax && $renderHeaders)
		{
			$object = array
			(
				"model"=>$this->model->package,
				"format"=>"tbody",
				"fields"=>$this->params["fields"],
				"operations"=>$this->operations,
				"limit"=>$pages,
				"numPages"=>$this->numPages,
				"sortField"=>$this->params["fields"][1],
				"page"=>0,
				"id"=>$this->name,
				"conditions"=>$this->conditions
			);
			
			$path = Application::$prefix."/lib/models/urlaccess.php";
			$params = "object=".urlencode(base64_encode(serialize($object)));
			
			$object["path"] = $path;
			$object["params"]=$params;
			
			$table .= 
			"<script type='text/javascript'>
				ntentan.tapi.addTable('$this->name',(".json_encode($object)."));
				function {$this->name}Search()
				{
					var condition = '';
					{$this->searchScript}
					ntentan.tapi.tables['$this->name'].conditions = condition;
					ntentan.tapi.tables['$this->name'].page = 0;
					ntentan.tapi.render(ntentan.tapi.tables['$this->name']);
				}
			</script>";
		}
		
		return $table;
	}
}
