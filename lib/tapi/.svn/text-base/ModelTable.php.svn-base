<?php
class ModelTable extends Table
{
	protected $model;
	
	public function __construct($prefix)
	{
		parent::__construct($prefix);
	}
	
	public function setModel($model,$fields=null)
	{
		$headers = $model->getLabels($fields);		
		array_shift($headers);
		$model->get($fields);
		$this->data = $model->formatData();
		$this->headers = $headers;				
	}
}
?>
