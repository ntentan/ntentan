<?php
/**
 * An implementation of an oracle model. This model is used to store the oracle
 * data.
 *
 * @author james
 *
 */
class oracle extends SQLDatabaseModel
{
	protected static $_conn = null;
	protected static $_s_conn = null;
	protected $mode = OCI_COMMIT_ON_SUCCESS;

	public static function connect($info)
	{
		$db = "{$info["host"]}/{$info["database"]}";
		oracle::$_s_conn = oci_connect($info["username"], $info["password"], $db);
	}

	public function __construct($model="",$package="",$prefix="",$conn=null)
	{
		parent::__construct($model,$package,$prefix);
		if($conn==null)
		{
			oracle::$_conn = oracle::$_s_conn;
		}
		else
		{
			oracle::$_conn = $conn;
		}
	}

	private function formatFields($row)
	{
		$n_row = array();
		$o_fields = array_keys($row);
		foreach($o_fields as $o_field)
		{
			if($o_field != "AUTOREMOVE_ORACLE_RNUM")
			{
				$n_row[strtolower($o_field)] = $row[$o_field];
			}
		}
		return $n_row;
	}

	public function beginTransaction()
	{
		$this->mode = OCI_DEFAULT;
	}

	public function endTransaction()
	{
		oci_commit(oracle::$_conn);
		$this->mode = OCI_COMMIT_ON_SUCCESS;
	}

	protected function _getModelData($params=null,$mode=model::MODE_ASSOC, $explicit_relations=false,$resolve=true)
	{
		$fields = $params["fields"];
		$conditions = $params["conditions"];
		$rows = array();
		$joined = null;

		if($params["enumerate"]===true)
		{
			$rows = $this->query('SELECT COUNT(*) AS "count" FROM '.$this->database.($conditions!=null?" WHERE ".$conditions:""));
		}
		else
		{

			// Get information about all referenced models and pull out all
			// the required information as well as build up the join parts
			// of the query.
			if($resolve)
			{
				$references = $this->getReferencedFields();
			}
			else
			{
				$references = array();
			}

			$joins = "";
			//$do_join = count($references)>0?true:false;

			$fieldList = $this->getExpandedFieldList($fields,$references,$resolve);
			$field_list = $fieldList["fields"];
			$expanded_fields = $fieldList["expandedFields"];
			$do_join = $fieldList["doJoin"];

			if(isset($params["sort_field"]))
			{
				$sorting = " ORDER BY {$expanded_fields[$params["sort_field"]]} {$params["sort_type"]}";
			}

			$joined_tables = array();
			foreach($references as $reference)
			{
				if(array_search($reference["table"],$joined_tables)===false)
				{
					$joins .= " LEFT JOIN {$reference["table"]} ON {$this->database}.{$reference["referencing_field"]} = {$reference["table"]}.{$reference["referenced_field"]} ";
					$joined_tables[] = $reference["table"];
				}
			}

			$query = sprintf("SELECT ".($params["distinct"]===true?"DISTINCT":"")." $field_list FROM %s ",$this->database).($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"").$sorting;

			if(isset($params["limit"]))
			{
				//$query = "SELECT * FROM ( $query ) where rownum <= ".($params["offset"]+$params["limit"])." and rownum >= ".($params["offset"]+0);
				$query = "select * from ( select  a.*, ROWNUM autoremove_oracle_rnum from ( $query ) a where ROWNUM <= ".($params["offset"]+$params["limit"])." ) where autoremove_oracle_rnum  >= ".($params["offset"]+0);

			}

			//print $query;
			$rows = $this->query($query,$mode);

			// Retrieve all explicitly related data
			if($explicit_relations)
			{
				foreach($this->explicitRelations as $explicitRelation)
				{
					foreach($rows as $i => $row)
					{
						$model = Model::load((string)$explicitRelation);
						$data = $model->get(array("conditions"=>$model->getDatabase().".".$this->getKeyField()."='".$row[$this->getKeyField()]."'"),SQLDatabaseModel::MODE_ASSOC,false,false);
						$rows[$i][(string)$explicitRelation] = $data;
					}
				}
				//var_dump($rows);
			}
		}

		return $rows;
	}

	public function escape($string)
	{
		return str_replace("'","''",$string);
	}

	public function getSearch($searchValue,$field)
	{
		return "instr(lower($field),lower('".$this->escape($searchValue)."'))>0";
	}

	public function concatenate($fields)
	{
		return implode(" || ' ' || ",$fields);
	}

	public function query($query,$mode = SQLDatabaseModel::MODE_ASSOC)
	{
		$rows = array();
		//print $query;
		$stmt = oci_parse(oracle::$_conn, $query);

		if($stmt===false)
		{
			throw new Exception("Invalid Query - $query");
		}

		if(oci_execute($stmt, $this->mode)===false)
		{
			throw new Exception("Invalid Query - $query");
		}

		if(oci_num_rows($stmt)==0)
		{
			switch($mode)
			{
				case SQLDatabaseModel::MODE_ASSOC:
					$o_mode = OCI_ASSOC;
					break;
				case SQLDatabaseModel::MODE_ARRAY:
					$o_mode = OCI_NUM;
					break;
			}

			while ($row = @oci_fetch_array($stmt,$o_mode + OCI_RETURN_NULLS))
			{
				unset($row["AUTOREMOVE_ORACLE_RNUM"]);
				$rows[] = $row;
			}
		}

		//var_dump($rows);

		return $rows;
	}

	public function formatField($field,$value,$alias = true,$functions=null)
	{
		$aliasValue = $field["name"];
		switch($field["type"])
		{
			case "date":
			case "datetime":
				$ret =  "INITCAP(TO_CHAR(TO_DATE('19700101000000','YYYYMMDDHH24MISS') + NUMTODSINTERVAL($value, 'SECOND'),'DDTH MONTH, YYYY'))";// as \"{$field["name"]}\"";
				
				break;
			case "enum":
				//var_dump($field["options"]);
				$query = "CASE ";
				foreach($field['options'] as $val=>$option)
				{
					$query .= "WHEN $value='$val' THEN '$option' ";
				}
				$query .= " END";// as \"{$field["name"]}\"";
				$ret = $query;
				break;

			case "number":
			case "double":
				$ret = "TRIM(TO_CHAR($value,'fm999,999,999,990.90'))";
				break;

			case "displayReference":
				$ret = $value;
				break;

			default:
				$ret = $value;
				break;
		}
		
		if(is_array($functions))
		{
			$ret = $this->applySqlFunctions($ret,$functions);
		}
		
		if($alias) $ret .=" as \"$aliasValue\"";
		return $ret;
	}
}
?>
