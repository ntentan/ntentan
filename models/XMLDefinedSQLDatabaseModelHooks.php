<?php
class XMLDefinedSQLDatabaseModelHooks
{
	/**
	 * @var Model
	 */
	protected $model;
	protected $data;
	protected $fields;

	public function setModel($model)
	{
		$this->model = $model;
		$this->data = $model->getData();
		$fields = $this->model->getFields();
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function validate()
	{
		return false;
	}

	public function preAdd()
	{
		
	}
	
	public function postAdd($primaryKeyValue,$data)
	{
		
	}
	
	public function preUpdate()
	{
		
	}
	
	public function postUpdate()
	{
		
	}

    public function preValidate()
    {
        
    }

    public function postValidate($errors)
    {
        //var_dump($errors);
        //die();
    }
}
