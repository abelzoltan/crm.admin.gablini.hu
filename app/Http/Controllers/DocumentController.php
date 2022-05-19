<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Document;

class DocumentController extends BaseController
{	
	public $model;
	public $path;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Document";
		$this->model = new \App\Document($connectionData);
		$this->path = env("PATH_ROOT_WEB")."dokumentumok/";
	}

	public function getDocuments()
	{
		$row = $this->model->getDocuments();
		$return = [];
		foreach($row AS $item)
		{
			$return[$item->name] = $item;
		}
		
		return $return;
	}
}
