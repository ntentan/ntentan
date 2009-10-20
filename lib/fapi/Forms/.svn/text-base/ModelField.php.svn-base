<?php
class ModelField extends SelectionList
{
	public function __construct($path,$value)
	{
		$info = model::resolvePath($path);
		$model = model::load($info["model"]);
		$field = $model->getFields(array($value));

		$this->setLabel($field[0]["label"]);
		$this->setDescription($field[0]["description"]);
		$this->setName($info["field"]);
		$data = $model->get(array($info["field"],$value),null,Model::MODE_ARRAY);

		$this->addOption("","");

		foreach($data as $datum)
		{
			$this->addOption($datum[1],$datum[0]);
		}
	}
}

?>
