<?php
class ModelField extends SelectionList
{
    protected $model;
    protected $valueField;
	public function __construct($path,$value)
	{
		$info = model::resolvePath($path);
		$this->model = model::load($info["model"]);
        $this->valueField=$value;
		$field = $this->model->getFields(array($value));

		$this->setLabel($field[0]["label"]);
		$this->setDescription($field[0]["description"]);
		$this->setName($info["field"]);

		$data = $this->model->get(array("fields"=>array($info["field"],$value)),Model::MODE_ARRAY);

		$this->addOption("","");

		foreach($data as $datum)
		{
			$this->addOption($datum[1],$datum[0]);
		}
	}
}

?>
