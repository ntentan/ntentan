<?php
class User
{
	public static function log($activity,$data)
	{
		$model = model::load("system.logs");
		$model->setData(array
			(
				"time"=>time(),
				"user_id"=>$_SESSION["user_id"],
				"ip_address"=>$_SERVER["REMOTE_ADDR"],
				"machine_name"=>$_SERVER["REMOTE_HOST"],
				"action_performed"=>$activity,
				"data"=>$data
			)
		);
		$model->save();
	}

	public static function getPermission($permission,$role_id=null)
	{
		$role_id = $role_id==null?$_SESSION["role_id"]:$role_id;
		if($role_id==1)
		{
			return true;
		}
		else
		{
			$model = model::load("system.permissions");
			$data = $model->get(array("value"),"role_id = $role_id AND permission='$permission'");
			return $data[0]["value"];
		}
	}

	public static function getAccess($module,$role_id=null)
	{
		$role_id = $role_id==null?$_SESSION["role_id"]:$role_id;

	}
}
?>
