<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SiteController;
use App\Comment;

class CommentController extends BaseController
{
	public $customerCountAllCustomers = NULL;
	public $siteList = NULL;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Comment";
		$this->model = new \App\Comment($connectionData);
		
		if(!isset($GLOBALS["site"])) { $GLOBALS["site"] = new SiteController(); }
		$this->siteList = $GLOBALS["site"]->getSites("url", "id");
	}
	
	#Get comment
	public function getComment($id, $allData = true)
	{
		$row = $this->model->getComment($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			
			$return["date"] = $row->date;
			$return["dateOut"] = strftime("%Y. %B %d.", strtotime($row->date));
			
			$return["name"] = $return["title"] = $row->name;
			$return["innerText"] = $row->innerText;
			$return["publicText"] = $row->publicText;
			
			if($allData)
			{
				$return["type"] = $this->getType($row->type);
				$users = new \App\Http\Controllers\UserController;
				$return["user"] = $users->getUser($row->user, false);				
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	#Get type
	public function getType($id)
	{
		$row = $this->model->getType($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["url"] = $row->url;
			$return["innerName"] = $row->name;
			$return["publicName"] = $row->name2;
			
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getTypeByURL($url)
	{
		$id = $this->model->getTypeByURL($url, "id");
		if(!empty($id)) { $return = $this->getType($id); }
		else { $return = false; }
		return $return;
	}
}
