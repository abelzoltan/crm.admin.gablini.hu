<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class AppNotification extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"notifications" => $this->dbPrefix."app_notifications",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get row
	public function getNotification($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("notifications"), "id", $id, $field, $delCheck);
	}
	
	#List
	public function getNotifications($sendDateMax = NULL, $isSent = NULL, $deleted = 0)
	{
		$query = "SELECT * FROM ".$this->tables("notifications")." WHERE id != '0'";
		$params = [];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if($sendDateMax !== NULL)
		{
			$query .= " AND sendDate <= :sendDateMax";
			$params["sendDateMax"] = $sendDateMax;
		}
		
		if($isSent !== NULL)
		{
			$query .= " AND isSent = :isSent";
			$params["isSent"] = $isSent;
		}
		
		$query .= " ORDER BY id DESC";
		
		return $this->select($query, $params);
	}
}
