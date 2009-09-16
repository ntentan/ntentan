<?php
class ModelServices
{
	protected $model;
	protected $data;
	protected $fields;
	
	public function setModel($model)
	{
		$this->model = $model;
		$this->data = $model->getData();
		$fields = $this->model->getFields();
	}
	
	public function validator_required($name,$parameters)
	{
		if($this->data[$name]!=="") 
		{
			return true;
		}
		else
		{
			return "The %field_name% field is required";
		}
	}
	
	public function validator_unique($name,$parameter)
	{
		$data = $this->model->getWithField($name,$this->data[$name]);
		if(count($data)==0)
		{
			return true;
		}
		else
		{
			return "The value of the %field_name% field must be unique.";
		}
	}
	
	public function validator_regexp($name,$parameter)
	{
		return true;
	}
}
