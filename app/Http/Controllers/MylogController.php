<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Mylog;

class MylogController extends BaseController
{	
	public $model;
	public $info;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Mylog";
		$this->model = new \App\Mylog($connectionData);
	}
	
	public function log($typeName, $datas = [], $resolution = NULL)
	{
		$this->info = $this->model->log($typeName, $datas, $resolution);
		return $this->info;
	}
	
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
