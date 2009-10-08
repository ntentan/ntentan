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

	public function __construct($model="",$conn=null)
	{
		parent::__construct($model);
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
			$n_row[strtolower($o_field)] = $row[$o_field];
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

	public function query($query,$mode = SQLDatabaseModel::MODE_ASSOC)
	{
		$rows = array();
		$stmt = oci_parse(oracle::$_conn, $query);
  		oci_execute($stmt, $this->mode);

  		//print "<br/><b>OCI :".$query."</b><br/>";

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

			while ($row = oci_fetch_array($stmt,$o_mode + OCI_RETURN_NULLS))
			{
				$rows[] = $this->formatFields($row);
			}
  		}
  		return $rows;
	}
}
?>
