<?php
abstract class DataStore
{
	public $fields;
	public $data;
	public $tempData;
	public $referencedFields;
	public $explicitRelations;
	public $storedFields;
	
	public abstract function get($params=null,$mode=Model::MODE_ASSOC,$explicit_relations=false,$resolve=true);
	public abstract function save();
	public abstract function update($field,$value);
	public abstract function delete($field,$value);
	
	public function getKeyField($type="primary")
	{
		foreach($this->fields as $name => $field)
		{
			if($field["key"]==$type) return $name;
		}
	}
	
}
?>