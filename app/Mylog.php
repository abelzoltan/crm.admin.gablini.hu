<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Mylog extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"log" => $this->dbPrefix."log",
			"types" => $this->dbPrefix."log_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get type
	public function getTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "url", $url, $field, $delCheck);
	}
	
	#Log
	public function log($typeName, $datas = [], $resolution = NULL)
	{
		$return = [
			"type" => "error",
			"info" => NULL,
			"id" => NULL,
		];
		
		$type = $this->getTypeByURL($typeName);
		if(isset($type->id) AND !empty($type->id))
		{
			if($type->active)
			{
				#User
				if(defined("USERID")) { $user = USERID; }
				else { $user = NULL; }
				
				#Basic datas				
				$params = [
					"date" => date("Y-m-d H:i:s"),
					"ip" => $_SERVER["REMOTE_ADDR"],
					"browser" => $_SERVER["HTTP_USER_AGENT"],
					"referer" => $_SERVER["HTTP_REFERER"],
					"resolution" => $resolution,
					"deviceType" => DEVICE_TYPE,
					"session" => session_id(), // \Session::getId()
					"requestedURI" => $_SERVER["REQUEST_URI"],
					"type" => $type->id,
					"site" => SITE,
					"user" => $user,
				];
				
				#Dinamic datas
				foreach($datas AS $dataKey => $dataVal) { $params["_".$dataKey] = $dataVal; }
				
				#Insert and return
				$return["id"] = $this->myInsert($this->tables("log"), $params);
				$return["type"] = "success";
			}
			else { $return["info"] = "inactive-type"; }
		}
		else { $return["info"] = "unknown-type"; }
		
		return $return;
	}
}
