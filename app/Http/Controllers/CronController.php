<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Cron;

class CronController extends BaseController
{	
	public $model;
	public $dateTime;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->dateTime = date("Y-m-d H:i:s");
		$this->modelName = "Cron";
		$this->model = new \App\Cron($connectionData);
	}
	
	public function log($type, $return)
	{
		$params = [
			"type" => $type,
			"date" => $this->dateTime,
			"returnData" => $this->json($return),
		];
		$this->model->myInsert($this->model->tables("cron"), $params);
	}
	
	public function getLastCronByType($type)
	{
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("cron")." WHERE del = '0' AND type = :type ORDER BY id DESC LIMIT 0, 1", ["type" => $type]);
		if(count($rows) > 0) { $return = $rows[0]; }
		else { $return = false; }
		
		return $return;
	}
	
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
