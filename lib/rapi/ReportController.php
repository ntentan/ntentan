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

	public function __construct($report)
	{
		//error_reporting(E_ALL);
		$this->xml = simplexml_load_file("app/modules".$report);
		$path =  $this->xml["name"]."/generate/pdf";
		$this->name = (string)$this->xml["name"];
		$this->label = $this->xml["label"];
		$this->_showInMenu = true;
	}

	public function generate($params)
	{
		var_dump($_POST);
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
						$text->style["height"] = 6;
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
					$params = array
					(
						"fields"=>$fields,
					);
					$report->add(new TableContent($headers,Model::getMulti($params)));
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
		$baseModel = $this->xml["baseModel"];

		$baseModel = Model::load((string)$baseModel);
		$referenced_fields = array();

		foreach($baseModel->getReferencedFields() as $field)
		{
			$referenced_fields[] = $field["referencing_field"];
		}

		/// Filters and sorting.
		foreach($tables as $table)
		{
			$fields = $table->xpath("/rapi:report/rapi:table[@name='{$table["name"]}']/rapi:fields/rapi:field");
			$labels = $table->xpath("/rapi:report/rapi:table[@name='{$table["name"]}']/rapi:fields/rapi:field/@label");
			$filters = new TableLayout(count($fields), 4);

			$sortingField = new SelectionList("Sorting Field","{$table["name"]}.sorting_field");
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

					$sortingField->addOption($fieldInfo["label"],$fieldInfo["name"]);

					if(array_search($model->getKeyField(),$referenced_fields)=== false || $fieldInfo["type"]=="double")
					{
						switch($fieldInfo["type"])
						{
							case "number":
							case "double":
								$filters
								->add(Element::create("Label",(string)$labels[$key]/*$fieldInfo["label"]*/),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Equals",0)
								->addOption("Greater Than",1)
								->addOption("Less Than",2)
								->addOption("Between",3),$i,1)
								->add(Element::create("TextField","","{$table["name"]}.{$fieldInfo["name"]}_start_value")->setAsNumeric(),$i,2)
								->add(Element::create("TextField","","{$table["name"]}.{$fieldInfo["name"]}_end_value")->setAsNumeric(),$i,3);
								break;

							case "date":
								$filters
								->add(Element::create("Label",(string)$labels[$key]),$i,0)
								->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
								->addOption("Before",0)
								->addOption("After",1)
								->addOption("On",2)
								->addOption("Between",3),$i,1)
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
								->addOption("Display Only",0)
								->addOption("Dont Display",1),$i,1)
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
						//@todo Instead of pulling everything out of the database, just pull what is necessary.
						$enum_list = new SelectionList("","{$table["name"]}.{$fieldInfo["name"]}_value");
						$enum_list->setMultiple(true);
						$data = $model->get(array("fields"=>array($model->getKeyField(),$fieldInfo["name"])),SQLDatabaseModel::MODE_ARRAY);
						foreach($data as $value)
						{
							$enum_list->addOption($value[1],$value[0]);
						}
						$filters
						->add(Element::create("Label",(string)$labels[$key]),$i,0)
						->add(Element::create("SelectionList","","{$table["name"]}.{$fieldInfo["name"]}_option")
						->addOption("Display Only",0)
						->addOption("Dont Display",1),$i,1)
						->add($enum_list,$i,2);
					}
				}
				$i++;
			}
				
			$grouping1 = clone $sortingField->setName("{$table["name"]}.grouping[]")->setLabel("Grouping Field 1");
			$grouping2 = clone $sortingField->setName("{$table["name"]}.grouping[]")->setLabel("Grouping Field 2");
			$grouping3 = clone $sortingField->setName("{$table["name"]}.grouping[]")->setLabel("Grouping Field 3");
			$sortingField->setLabel("Sorting Field");

			//$container = new FieldSet($table["name"]);
			//$container->addAttribute("style","width:80%");
			$form->add(	Element::create("FieldSet","Filters")->add($filters),
			Element::create("FieldSet","Sorting")->add($sortingField,Element::create("SelectionList","Direction","{$table["name"]}.sorting_direction")->addOption("Ascending","ASC")->addOption("Descending","DESC")),
			Element::create("FieldSet","Grouping")->add($grouping1,$grouping2,$grouping3)
			);
			//$form->add($container);

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