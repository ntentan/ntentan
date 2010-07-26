<?php
/**
 * The ReportController reads in reports from the XML description and it
 * automatically generates reports in various supported formats based on the
 * descriptions found within.
 *
 * @author james
 */
class ReportController extends Controller
{
	/**
	 * An XML datafile which contains a description of the report and it's
	 * fields.
	 * @var SimpleXMLElement
	 */
	protected $xml;
	public $referencedFields;
	private $widths;
	private $types = array();
	private $headings=array();
	private $tableRendered;

	public function __construct($report)
	{
		//error_reporting(E_ALL);
		$this->xml = simplexml_load_file("app/modules".$report);
		$path =  $this->xml["name"]."/generate/pdf";
		$this->name = (string)$this->xml["name"];
		$this->label = $this->xml["label"];
		$this->_showInMenu = true;

		$baseModel = $this->xml["baseModel"];
		try
		{
			$baseModel = Model::load((string)$baseModel);
		}
		catch(Exception $e)
		{
			throw new Exception("Base model could not be loaded");
		}
		$this->referencedFields = array();

		foreach($baseModel->getReferencedFields() as $field)
		{
			$this->referencedFields[] = $field["referencing_field"];
		}
	}

	protected function generateTable(&$params)
	{
		$field = $params["grouping_fields"][$params["grouping_level"]];
		$multipleFields = explode(",",$field);
		if(count($multipleFields)>1)
		{
			$data = Model::getMulti(
				array(
					"fields"=>array($params["grouping_fields"][$params["grouping_level"]],
				)
			),SQLDatabaseModel::MODE_ARRAY);
			$g1Model = $params["models"][array_rand($params["models"])];
			$fields = array();
			foreach($multipleFields as $mField)
			{
				$modelInfo = Model::resolvePath($mField);
				$fields[] = $params["models"][$modelInfo["model"]]->getDatabase().".".$modelInfo["field"];
			}
			$conditionField = $g1Model->concatenate($fields);
		}
		else
		{
			$g1ModelInfo = Model::resolvePath($field);
			$g1Model = $params["models"][$g1ModelInfo["model"]];
			$data = $g1Model->get(array("distinct"=>true,"sort_field"=>$g1ModelInfo["field"],"fields"=>array($g1ModelInfo["field"])),SQLDatabaseModel::MODE_ARRAY,false,false);
			$g1FieldInfo = $g1Model->getFields(array($g1ModelInfo["field"]));
			$conditionField = $g1Model->getDatabase().".".$g1ModelInfo["field"];
		}

		$ignore_index = array_search($field,$params["fields"]);
		if($ignore_index!==false)
		{
			array_splice($params["fields"],$ignore_index,1);
			array_splice($params["headers"],$ignore_index,1);
		}

		$dataParams = $params["data_params"];
		foreach($dataParams["total"] as $index=>$total)
		{
			$dataParams["total"][$index] = array_search($total,$params["fields"]);
		}

		foreach($data as $key=>$dat)
		{
			$filters_copy = $params["filters"];
			$filters_copy[] = $conditionField."='".$g1Model->escape($dat[0])."'";
			
			switch($g1FieldInfo[0]["type"])
			{
				case "enum":
					$dat[0] = $g1FieldInfo[0]["options"][$dat[0]];
					break;
				case "date":
					$dat[0] = date("l jS F, Y",$dat[0]);
					break;
			}

			$query_params = array
			(
				"fields"=>$params["fields"],
				"conditions"=>implode(" AND ",$filters_copy)
			);

			$heading = new TextContent();
			$heading->style["size"] = 12 * (( 3 - $params["grouping_level"])/3*.5+.5);
			$heading->style["bold"] = true;
			$heading->style["top_margin"] = 5 * (( 3 - $params["grouping_level"])/3);
			$heading->setText($dat[0]);

			$totalTable = new TableContent(null,null);//,array("widths"=>$this->widths));
			$totalTable->style["totalsBox"] = true;
			$totalTitle = new TextContent("");
			$totalTitle->style["bold"] = true;
			$totalTitle->style["size"] = 2;
			$totalTitle->style["top_margin"]=1;	

			if($params["grouping_fields"][$params["grouping_level"]+1]=="")
			{
				$data = Model::getMulti($query_params);

				if(!is_array($this->widths))
				{
					$params["global_functions"] = array("MAX","LENGTH");
					$params["global_functions_set"] = true;

					$this->widths = Model::getMulti($params,SQLDatabaseModel::MODE_ARRAY);
					$this->widths = $this->widths[0];

					foreach($params["headers"] as $i=>$header)
					{
						if(strlen($header)>$this->widths[$i])
						{
							$this->widths[$i] = strlen($header);
						}
						if($params["field_infos"][$params["fields"][$i]]["type"]=="integer" ||$params["field_infos"][$params["fields"][$i]]["type"]=="double")
						{
							$this->types[$i] = "number";
						}
					}

					$tWidths = array_sum($this->widths);
					
					foreach($this->widths as $i => $width)
					{
						$this->widths[$i] = $width/$tWidths;
					}
				}				

				if(count($data)>0)
				{
					$dataParams["widths"] = $this->widths;
					$dataParams["type"] = $this->types;
					$table = new TableContent($params["headers"],$data,$dataParams);
					foreach($this->headings as $previous_heading)
					{
						$params["report"]->add($previous_heading);
					}
					$this->headings = array();
					$params["report"]->add($heading);
					$params["report"]->add($table);
					$total = $table->getTotals();

					foreach($total as $i=>$value)
					{
						$params["totals"][$params["grouping_level"]][$i]+=$value;
					}

					$totalTable->data_params = array("widths"=>$this->widths);
					$total[0] = $dat[0]." Totals";
					$totalTable->setData($total);
					$params["report"]->add($totalTable);
				}
				$this->tableRendered = true;
			}
			else
			{				
				if($this->tableRendered===true && ($params["grouping_level"]-1)<count($this->headings))
				{
					$this->tableRendered = false;
					array_pop($this->headings);
				}

				$this->headings[]=$heading;
				$params_copy = $params;
				$params_copy["grouping_level"]++;
				$params_copy["filters"] = $filters_copy;
				$total = $this->generateTable($params_copy);

				if(is_array($total))
				{
					foreach($total as $i=>$value)
					{
						$params["totals"][$params["grouping_level"]][$i]+=$value;
					}
				}

				if($total!=null)
				{
					$total[0] = $dat[0]." Totals";
					$totalTable->setData($total);
					$totalTable->data_params = array("widths"=>$this->widths);
					$params["report"]->add($totalTitle);
					$params["report"]->add($totalTable);
				}	
			}
					
		}
		return $params["totals"][$params["grouping_level"]];
	}

	public function generate($params)
	{
		switch($params[0])
		{
			case "html":
				$report = new HTMLReport();
				break;
			default:
				$report = new PDFReport();
				break;
		}

		$reader  = new XMLReader();
		$reader->XML($this->xml->asXML());

		while($reader->read())
		{
			if($reader->nodeType !== XMLReader::ELEMENT) continue;
			switch($reader->name)
			{
				case "rapi:logo":
					$reader->moveToAttribute("class");
					$class = $reader->value;
					$logo = $class==''?new LogoContent():new $class();
					//@todo implement the setting of the address and a custom image
					$report->add($logo);
					break;

				case "rapi:text";
					$text = new TextContent();
					$reader->moveToAttribute("style");
					switch($reader->value)
					{
						case "heading":
							$text->style["size"] = 16;
							$text->style["font"] = "Helvetica";
							$text->style["bold"] = true;
							break;
							
						default:
							$text->style["size"] = $reader->moveToAttribute("size")?$reader->value:12;
							$text->style["font"] = $reader->value?$reader->moveToAttribute("font"):"Helvetica";
							break;
					}
					$reader->read();
					$text->setText($reader->value);
					$report->add($text);
					break;
					
				case "rapi:table":
					$reader->moveToAttribute("name");
					$name = $reader->value;

					$fields = $this->xml->xpath("/rapi:report/rapi:table[@name='$name']/rapi:fields/rapi:field");
					$headers = $this->xml->xpath("/rapi:report/rapi:table[@name='$name']/rapi:fields/rapi:field/@label");
					$dataParams["total"] = array();

					$models = array();
					$fieldInfos = array();

					// Generate filter conditions
					$filters = array();
					foreach($fields as $key=>$field)
					{
						if($field["total"]=="true")
						{
							$dataParams["total"][] = (string)$field;
						}
						$fields[$key] = (string)$field;
						$headers[$key] = (string)$headers[$key];
						$field = (string)$field;
						$modelInfo = Model::resolvePath($field);
						if(count(explode(",",$field))==1)
						{
							if(array_search($modelInfo["model"],array_keys($models))===false)
							{
								$models[$modelInfo["model"]] = Model::load($modelInfo["model"]);
							}
							$model = $models[$modelInfo["model"]];
							$fieldInfo = $models[$modelInfo["model"]]->getFields(array($modelInfo["field"]));
							$fieldInfo = $fieldInfo[0];
							$fieldInfos[$field] = $fieldInfo;
							
							if(array_search($model->getKeyField(),$this->referencedFields)=== false || $fieldInfo["type"]=="double")
							{
								switch($fieldInfo["type"])
								{
									case "string":
										break;
											
									case "integer":
									case "double":
											
										switch($_POST[$name."_".$fieldInfo["name"]."_option"])
										{
											case "EQUALS":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}='".$models[$modelInfo["model"]]->escape($_POST[$name."_".$fieldInfo["name"]."_start_value"])."'";
												break;
											case "GREATER":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}>'".$models[$modelInfo["model"]]->escape($_POST[$name."_".$fieldInfo["name"]."_start_value"])."'";
												break;
											case "LESS":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}<'".$models[$modelInfo["model"]]->escape($_POST[$name."_".$fieldInfo["name"]."_start_value"])."'";
												break;
											case "BETWEEN":
												$filters[] = "({$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}>='".$models[$modelInfo["model"]]->escape($_POST[$name."_".$fieldInfo["name"]."_start_value"])."' AND {$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}<='".$models[$modelInfo["model"]]->escape($_POST[$name."_".$fieldInfo["name"]."_end_value"])."')";
												break;
										}
										break;
											
									case "date":
										switch($_POST[$name."_".$fieldInfo["name"]."_option"])
										{
											case "EQUALS":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}='".$models[$modelInfo["model"]]->escape(strtotime($_POST[$name."_".$fieldInfo["name"]."_start_date"]))."'";
												break;
											case "GREATER":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}>'".$models[$modelInfo["model"]]->escape(strtotime($_POST[$name."_".$fieldInfo["name"]."_start_date"]))."'";
												break;
											case "LESS":
												$filters[] = "{$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}<'".$models[$modelInfo["model"]]->escape(strtotime($_POST[$name."_".$fieldInfo["name"]."_start_date"]))."'";
												break;
											case "BETWEEN":
												$filters[] = "({$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}>='".$models[$modelInfo["model"]]->escape(strtotime($_POST[$name."_".$fieldInfo["name"]."_start_date"]))."' AND {$models[$modelInfo["model"]]->getDatabase()}.{$fieldInfo["name"]}<='".$models[$modelInfo["model"]]->escape(strtotime($_POST[$name."_".$fieldInfo["name"]."_end_date"]))."')";
												break;
										}
										break;

									case "enum":
										$m = $models[$modelInfo["model"]];
										if($_POST[$name."_".$fieldInfo["name"]."_option"]=="INCLUDE")
										{
											$condition = array();
											foreach($_POST[$name."_".$fieldInfo["name"]."_value"] as $value)
											{
												if($value!="") $condition[]="{$m->getDatabase()}.{$fieldInfo["name"]}='".$m->escape($value)."'";
											}
										}
										else if($_POST[$name."_".$fieldInfo["name"]."_option"]=="EXCLUDE")
										{
											$condition = array();
											foreach($_POST[$name."_".$fieldInfo["name"]."_value"] as $value)
											{
												if($value!="") $condition[]="{$m->getDatabase()}.{$fieldInfo["name"]}<>'".$m->escape($value)."'";
											}
										}
										if(count($condition)>0) $filters[] = "(".implode(" OR ",$condition).")";
										break;
								}
							}
							else
							{
								$condition = array();
								foreach($_POST[$name."_".$fieldInfo["name"]."_value"] as $value)
								{
									if($value!="") $condition[]="{$model->getDatabase()}.{$fieldInfo["name"]}='".$model->escape($value)."'";
								}
								if(count($condition)>0) $filters[] = "(".implode(" OR ",$condition).")";
							}
						}
					}

					// Generate the various tables taking into consideration grouping
					if(isset($_POST[$name."_sorting"]))
					{
						$params["sort_field"] = $_POST[$name."_sorting"];
						$params["sort_type"] = $_POST[$name."_sorting_direction"];
					}

					if($_POST[$name."_grouping"][0]=="")
					{
						$params = array
						(
							"fields"=>$fields,
							"conditions"=>implode(" AND ",$filters)
						);
						foreach($dataParams["total"] as $index=>$total)
						{
							$dataParams["total"][$index] = array_search($total,$params["fields"]);
						}
						
						$wparams = $params;
						$wparams["global_functions"] = array("MAX","LENGTH");
						$wparams["global_functions_set"] = true;
						$this->widths = Model::getMulti($wparams,SQLDatabaseModel::MODE_ARRAY);
						$this->widths = $this->widths[0];
						
						foreach($headers as $i=>$header)
						{
							if(strlen($header)>$this->widths[$i])
							{
								$this->widths[$i] = strlen($header);
							}
							if($fieldInfos[$fields[$i]]["type"]=="integer" ||$fieldInfos[$fields[$i]]["type"]=="double")
							{
								$dataParams["type"][$i] = "number";
							}
						}						
						
						$tWidths = array_sum($this->widths);
						foreach($this->widths as $i => $width)
						{
							$this->widths[$i] = $width/$tWidths;
						}

						$dataParams["widths"] = $this->widths;
											
						$table = new TableContent($headers,Model::getMulti($params),$dataParams);
						$total = $table->getTotals();
						$report->add($table);
					}
					else
					{
						$params = array
						(
								"headers"=>$headers,
								"fields"=>$fields,
								"grouping_fields"=>$_POST[$name."_grouping"],
								"grouping_level"=>0,
								"models"=>$models,
								"filters"=>$filters,
								"report"=>$report,
								"data_params"=>$dataParams,
								"totals"=>array(),
								"field_infos"=>$fieldInfos
						);
						$total = $this->generateTable($params);
					}
					
					if(count($total)>0)
					{
						$total[0] = "Overall Total";
						$totalTable = new TableContent(null,$total,array("widths"=>$this->widths));
						$totalTable->style["totalsBox"] = true;

						$totalTitle = new TextContent("");
						$totalTitle->style["bold"] = true;
						$totalTitle->style["italics"] = true;
						$totalTitle->style["size"] = 4;
						$totalTitle->style["top_margin"]=3;
						$report->add($totalTitle);
						$report->add($totalTable);
					}
					break;
			}
		}
		$report->output();
	}

	public function getContents()
	{
		$filters = array();
		$fieldInfos = array();
		$tables = $this->xml->xpath("/rapi:report/rapi:table");

		/// Filters and sorting.
		foreach($tables as $table)
		{
			$fields = $table->xpath("/rapi:report/rapi:table[@name='{$table["name"]}']/rapi:fields/rapi:field");
			$labels = $table->xpath("/rapi:report/rapi:table[@name='{$table["name"]}']/rapi:fields/rapi:field/@label");
			$filters = new TableLayout(count($fields), 4);

			$sortingField = new SelectionList("Sorting Field","{$table["name"]}_sorting_field");
			$grouping1 = new SelectionList();
			$form = new Form();

			$i = 0;
			foreach($fields as $key=>$field)
			{
				if(count(explode(",",(string)$field))==1)
				{
					$fieldInfo = Model::resolvePath((string)$field);
					$model = Model::load($fieldInfo["model"]);
					$fieldName = $fieldInfo["field"];
					$fieldInfo = $model->getFields(array($fieldName));
					$fieldInfo = $fieldInfo[0];
					$fields[$key] = (string)$field;

					$sortingField->addOption($fieldInfo["label"],$model->getDatabase().".".$fieldInfo["name"]);
					$grouping1->addOption($labels[$key], $field);

					if(array_search($model->getKeyField(),$this->referencedFields)=== false || $fieldInfo["type"]=="double")
					{
						switch($fieldInfo["type"])
						{
							case "integer":
							case "double":
								$filters
								->add(Element::create("Label",(string)$labels[$key]/*$fieldInfo["label"]*/),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Equals","EQUALS")
								->addOption("Greater Than","GREATER")
								->addOption("Less Than","LESS")
								->addOption("Between","BETWEEN"),$i,1)
								->add(Element::create("TextField","","{$table["name"]}.{$fieldInfo["name"]}_start_value")->setAsNumeric(),$i,2)
								->add(Element::create("TextField","","{$table["name"]}.{$fieldInfo["name"]}_end_value")->setAsNumeric(),$i,3);
								break;

							case "date":
								$filters
								->add(Element::create("Label",(string)$labels[$key]),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Before","LESS")
								->addOption("After","GREATER")
								->addOption("On","EQUALS")
								->addOption("Between","BETWEEN"),$i,1)
								->add(Element::create("DateField","","{$table["name"]}.{$fieldInfo["name"]}_start_date")->setId("{$table["name"]}_{$fieldInfo["name"]}_start_date"),$i,2)
								->add(Element::create("DateField","","{$table["name"]}.{$fieldInfo["name"]}_end_date")->setId("{$table["name"]}_{$fieldInfo["name"]}_end_date"),$i,3);
								break;

							case "enum":
								$enum_list = new SelectionList("","{$table["name"]}.{$fieldInfo["name"]}_value");
								$enum_list->setMultiple(true);
								foreach($fieldInfo["options"] as $value =>$label)
								{
									$enum_list->addOption($label,$value);
								}
								$filters
								->add(Element::create("Label",(string)$labels[$key]),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Is any of","INCLUDE")
								->addOption("Is none of","EXCLUDE"),$i,1)
								->add($enum_list,$i,2);
								break;

							case "string":
								$filters
								->add(Element::create("Label",(string)$labels[$key]),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Exactly",0)
								->addOption("Contains",1),$i,1)
								->add(Element::create("TextField","","{$table["name"]}.{$fieldInfo["name"]}_value"),$i,2);
								break;
						}
					}
					else
					{
						$enum_list = new ModelSearchField();
						$enum_list->setName("{$table["name"]}.{$fieldInfo["name"]}_value");
						$enum_list->setModel($model,$fieldInfo["name"]);
						$enum_list->addSearchField($fieldInfo["name"]);
						$enum_list->boldFirst = false;
						$filters
						->add(Element::create("Label",(string)$labels[$key]),$i,0)
						->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
						->addOption("Is any of","IS_ANY_OF")
						->addOption("Is none of","IS_NONE_OF"),$i,1)
						->add(Element::create("MultiFields")->setTemplate($enum_list),$i,2);
						
					}
				}
				else
				{
					$grouping1->addOption($labels[$key], $field);
				}
				$i++;
			}

			//$grouping1 = clone $sortingField;
			$grouping1->setName("{$table["name"]}_grouping[]")->setLabel("Grouping Field 1");
			$g1Paging = new CheckBox("Start on a new page","grouping_1_newpage","","1");
			$g1Logo = new CheckBox("Repeat Logos","grouping_1_logo","","1");

			$grouping2 = clone $grouping1;
			$grouping2->setName("{$table["name"]}_grouping[]")->setLabel("Grouping Field 2");
			$g2Paging = new CheckBox("Start on a new page","grouping_1_newpage","","1");
			$g2Logo = new CheckBox("Repeat Logos","grouping_2_logo","","1");

			$grouping3 = clone $grouping1;
			$grouping3->setName("{$table["name"]}_grouping[]")->setLabel("Grouping Field 3");
			$g3Paging = new CheckBox("Start on a new page","grouping_1_newpage","","1");
			$g3Logo = new CheckBox("Repeat Logos","grouping_3_logo","","1");

			$sortingField->setLabel("Sorting Field");
			$sortingField->setName($table["name"]."_sorting");

			$groupingTable = new TableLayout(3,3);

			$groupingTable->add($grouping1,0,0);
			$groupingTable->add($grouping2,1,0);
			$groupingTable->add($grouping3,2,0);
			/*$groupingTable->add($g1Paging,0,1);
			 $groupingTable->add($g2Paging,1,1);
			 $groupingTable->add($g3Paging,2,1);
			 $groupingTable->add($g1Logo,0,2);
			 $groupingTable->add($g2Logo,1,2);
			 $groupingTable->add($g3Logo,2,2);*/

			$container = new FieldSet($table["name"]);
			//$container->addAttribute("style","width:60%");
			$container->add(
			Element::create("FieldSet","Filters")->add($filters),
			Element::create("FieldSet","Sorting")->add($sortingField,Element::create("SelectionList","Direction","{$table["name"]}.sorting_direction")->addOption("Ascending","ASC")->addOption("Descending","DESC")),
			Element::create("FieldSet","Grouping")->add($groupingTable)
			);
			$sortingField->setName($table["name"]."_sorting");
			$form->add($container);
		}

		$form->setSubmitValue("Generate");
		$form->addAttribute("action",Application::getLink($this->path."/generate"));
		$form->addAttribute("target","blank");

		//$form->useAjax(true,true);

		$data = array
		(
			"script"=>$this->script,
			"filters"=>$form->render()
		);

		return array("template"=>"file:".getcwd()."/lib/rapi/reports.tpl","data"=>$data);
	}

	public function getPermissions()
	{
		return array
		(
		array("label"=>"Can view","name"=>$this->name."_can_view"),
		);
	}
}
?>