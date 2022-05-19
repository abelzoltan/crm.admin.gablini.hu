<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Career;

class CareerController extends BaseController
{	
	public $model;
	
	public $place = "Munkavégzés helye: ";
	public $textWork = "Mi lesz a feladatod?";
	public $textRequire = "Mi szükséges a munkakör betöltéséhez?";
	public $textProvide = "Amire biztosan számíthatsz:";
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Career";
		$this->model = new \App\Career($connectionData);
	}

	#Get from
	public function getFrom($id)
	{
		return $this->model->getFrom($id);
	}
	
	public function getFromList($deleted = 0, $key = "id")
	{
		$return = [];
		$rows = $this->model->getFromList($deleted);		
		foreach($rows AS $row) { $return[$row->$key] = $this->getFrom($row->id); }		
		return $return;
	}
	
	public function updateFrom($params, $id)
	{
		return $this->model->myUpdate($this->model->tables("from"), $params, $id);
	}
	
	public function insertFrom($params)
	{
		return $this->model->myInsert($this->model->tables("from"), $params);
	}
	
	#Get category
	public function getCategory($id)
	{
		return $this->model->getCategory($id);
	}
	
	public function getCategories($deleted = 0, $key = "id")
	{
		$return = [];
		$rows = $this->model->getCategories($deleted);		
		foreach($rows AS $row) { $return[$row->$key] = $this->getCategory($row->id); }		
		return $return;
	}
	
	#Get job
	public function getJob($id, $allData = true)
	{
		$return = []; 
		$return["data"] = $job = $this->model->getJob($id);
		$return["id"] = $job->id;
		$return["active"] = $job->active;
		
		if($allData)
		{
			$return["text"] = "";
			if(!empty($job->place)) { $return["text"] .= "<div><strong>".$this->place."</strong>".$job->place."<p>&nbsp;</p></div>"; }
			if(!empty($job->textWork)) { $return["text"] .= "<div><p><strong>".$this->textWork."</strong></p>".$job->textWork."<p>&nbsp;</p></div>"; }
			if(!empty($job->textRequire)) { $return["text"] .= "<div><p><strong>".$this->textRequire."</strong></p>".$job->textRequire."<p>&nbsp;</p></div>"; }
			if(!empty($job->textProvide)) { $return["text"] .= "<div><p><strong>".$this->textProvide."</strong></p>".$job->textProvide."<p>&nbsp;</p></div>"; }
			if(!empty($job->text)) { $return["text"] .= $job->text; }
			
			
			$file = new \App\Http\Controllers\FileController;
			if(!empty($job->pic)) { $return["pic"] = $file->getFile($job->pic); }
			if(!empty($job->picDetails1)) { $return["picDetails1"] = $file->getFile($job->picDetails1); }
			if(!empty($job->picDetails2)) { $return["picDetails2"] = $file->getFile($job->picDetails2); }
			if(!empty($job->picDetails3)) { $return["picDetails3"] = $file->getFile($job->picDetails3); }
		}
		return $return;
	}
	
	public function getJobByURL($url, $delCheck = 1)
	{
		$id = $this->model->getJobByURL($url, "id", $delCheck);
		return $this->getJob($id);
	}
	
	public function getJobs($deleted = 0, $key = "id")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
		];
		$rows = $this->model->getJobs($deleted);		
		foreach($rows AS $row) 
		{ 
			$return["all"][$row->$key] = $this->getJob($row->id); 
			if($return["all"][$row->$key]["active"]) { $return["active"][$row->$key] = $return["all"][$row->$key]; }
			else { $return["inactive"][$row->$key] = $return["all"][$row->$key]; }
		}		
		return $return;
	}
	
	#Generate job URL
	public function setJobURL($name, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("jobs"), "url", $name, "", ["del" => 0], $id);
	}
	
	#Check job URL duplication
	public function checkJobURL($url, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("jobs"), "url", $url, ["del" => 0], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Adminwork
	public function adminWork($tableName, $workType, $datas)
	{
		$table = $this->model->tables($tableName);
		$return = [
			"table" => $tableName,
			"work" => $workType,
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
			"params" => [],
			"id" => NULL,
		];
		
		switch($tableName)
		{
			case "from":
			case "categories":
				switch($workType)
				{
					case "new":
						$orderNumber = $this->model->reOrder($table);
						$return["params"] = ["name" => $datas["name"], "orderNumber" => $orderNumber];
						$return["id"] = $this->model->myInsert($table, $return["params"]);
						break;	
					case "edit":
						$return["params"] = ["name" => $datas["name"]];
						$return["id"] = $datas["id"];
						$this->model->myUpdate($table, $return["params"], $datas["id"]);
						break;
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->model->reOrder($table);
						break;
					case "order":
						$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table);
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;
				}
				break;
			case "jobs":
				$fields = ["active", "url", "name", "shortText", "textWork", "textRequire", "textProvide", "text", "place", "video", "metaName", "metaKeywords", "metaDescription"];
				$required = ["url", "name"];
				switch($workType)
				{
					case "new":
					case "edit":
						if($workType == "new") { $id = 0; }
						else { $id = $datas["id"];  }

						#Required
						foreach($required AS $requiredField)
						{
							if(!isset($datas[$requiredField]) OR empty($datas[$requiredField]))
							{
								$return["errors"]["required"][] = $requiredField;
								$return["type"] = "error";
							}
						}
						
						if($return["type"] == "success")
						{
							#Check URL
							if(mb_substr($datas["url"], 0, 1, "utf-8") == "#") {  }
							elseif(!$this->checkJobURL($datas["url"], $id)) 
							{ 
								$datas["url"] = $this->setJobURL($datas["url"], $id);
								$return["errors"]["changed"][] = "url";
							}
							
							#Params
							$params = [];
							foreach($fields AS $field)
							{
								if($field == "active" AND (!isset($datas[$field]) OR empty($datas[$field]))) { $datas[$field] = 0; }
								$params[$field] = $datas[$field];
							}
							
							#OrderNumber, database command
							if($workType == "new") 
							{ 
								$fields[] = "orderNumber";
								$params["orderNumber"] = $this->model->reOrder($table);
								
								$return["id"] = $this->model->myInsert($table, $params);
							}
							else
							{ 
								$this->model->myUpdate($table, $params, $id);
								$return["id"] = $id;
							}
							
							$return["params"] = $params;
						}
						break;
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->model->reOrder($table);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table);
						break;
					case "pic":	
						$this->model->myUpdate($table, ["pic" => $datas["pic"]], $datas["id"]);
						break;
					case "picDetails1":	
						$this->model->myUpdate($table, ["picDetails1" => $datas["pic"]], $datas["id"]);
						break;
					case "picDetails2":	
						$this->model->myUpdate($table, ["picDetails2" => $datas["pic"]], $datas["id"]);
						break;
					case "picDetails3":	
						$this->model->myUpdate($table, ["picDetails3" => $datas["pic"]], $datas["id"]);
						break;	
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;	
				}
			break;	
			default:
				$return["errors"]["others"] = "unknown-table";
				$return["type"] = "error";
				break;	
		}
		
		if(isset($GLOBALS["log"]))
		{
			$logData = [
				"vchar1" => "Career",
				"vchar2" => $tableName,
				"vchar3" => $workType,
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("adminwork", $logData);
		}
		return $return;
	}
	
	#Apply for a job / in database
	public function apply($datas)
	{
		$table = $this->model->tables("applies");
		$return = [
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
			"id" => NULL,
			"file" => NULL,
		];
		
		if(!isset($datas["type"]) OR empty($datas["type"])) 
		{
			$return["type"] = "error";
			$return["errors"][] = "unknown-type";
		}
		elseif($datas["type"] == "career-apply-job" OR $datas["type"] == "career-apply-database") 
		{
			#Recaptcha check
			$form = new \App\Http\Controllers\FormController;
			if(!$form->recaptchaResponse())
			{
				$return["type"] = "error";
				$return["errors"][] = "captcha";
			}
			
			#Terms accept check
			if(!isset($datas["acceptTerms"]) OR empty($datas["acceptTerms"]) OR !$datas["acceptTerms"])
			{
				$return["type"] = "error";
				$return["errors"][] = "accept-terms";
			}
			
			#Required check
			$required = ["name", "email", "fromID"];
			if($datas["type"] == "career-apply-job") { $required[] = "job"; }
			elseif($datas["type"] == "career-apply-database") { $required[] = "category"; }
			
			foreach($required AS $item)
			{
				if(!isset($datas[$item]) OR empty($datas[$item]))
				{
					$return["type"] = "error";
					$return["errors"][] = "missing-fields";
				}
			}
			if(!isset($_FILES["cv"]) OR !isset($_FILES["cv"]["name"]) OR empty($_FILES["cv"]["name"]))
			{
				$return["type"] = "error";
				$return["errors"][] = "missing-fields";
			}
			
			#Job or Category: active and exists
			if($return["type"] == "success")
			{
				if($datas["type"] == "career-apply-job") 
				{ 
					$row = $this->model->getJob($datas["job"]);
					if(!isset($row->id) OR empty($row->id))
					{
						$return["type"] = "error";
						$return["errors"][] = "unknown-job";
					}
					elseif(!$row->active OR $row->del)
					{
						$return["type"] = "error";
						$return["errors"][] = "unavailable-job";
					}
				}
				elseif($datas["type"] == "career-apply-database")
				{ 
					$row = $this->model->getCategory($datas["category"]);
					if(!isset($row->id) OR empty($row->id))
					{
						$return["type"] = "error";
						$return["errors"][] = "unknown-job-category";
					}
					elseif($row->del)
					{
						$return["type"] = "error";
						$return["errors"][] = "unavailable-job-category";
					}
				}
			}
			
			#Everything is OK - Database insert
			if($return["type"] == "success")
			{
				$paramKeys = $required;
				$params = [];
				foreach($paramKeys AS $item)
				{
					if(isset($datas[$item])) { $params[$item] = $datas[$item]; }
				}
				$params["acceptTerms"] = 1;
				$return["id"] = $this->model->insert($table, $params);
				
				#Upload file
				$files = new \App\Http\Controllers\FileController;
				$file = $files->upload("cv", "career-cv", $return["id"], [$params["name"]." CV"]);
				if($file[0]["type"] == "success") { $this->model->myUpdate($table, ["cv" => $file[0]["fileID"]], $return["id"]); }
				
				#Newsletter subscription
				if(isset($params["newsletter"]) AND $params["newsletter"]) { $subscribe = 1; }
				else { $subscribe = 0; }
				$emails = new \App\Http\Controllers\EmailController;
				$emails->newsletter($subscribe, $params["name"], $params["email"]);
			}
		}
		else
		{
			$return["type"] = "error";
			$return["errors"][] = "unknown-type";
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$logParams = ["text1" => $GLOBALS["log"]->json($return)];
			if($return["type"] == "success")
			{
				$logParams["int1"] = 1;
				$logParams["int2"] = $return["id"];
			}
			else { $logParams["int1"] = 0; }
			$GLOBALS["log"]->log("careers-apply", $logParams);
		}
		
		#Return
		return $return;
	}
	
	public function getApply($id, $allData = true)
	{
		$return = []; 
		$return["data"] = $apply = $this->model->getApply($id);
		$return["id"] = $apply->id;
		
		if($apply->acceptTerms) { $acceptTerms = "IGEN"; }
		else { $acceptTerms = "Nem"; }
		$return["details"] = [
			"date" => ["name" => "Dátum", "value" => $apply->created_at], 
			"name" => ["name" => "Jelentkező neve", "value" => $apply->name], 
			"email" => ["name" => "Jelentkező e-mail címe", "value" => $apply->email], 
			"from" => ["name" => "Honnan értesült az állásról?", "value" => NULL], 
			"job" => ["name" => "Állásajánlat", "value" => NULL], 
			"category" => ["name" => "Adatbázisba jelentkezés kategória", "value" => NULL], 
			"acceptTerms" => ["name" => "Adatvédelmi irányelveket elfogadta", "value" => $acceptTerms], 
			"cv" => ["name" => "Jelentkező önéletrajza", "value" => NULL], 
		];
		
		if($allData)
		{
			#Job
			if(!empty($apply->job)) 
			{ 
				$return["job"] = $this->getJob($apply->job); 
				$return["details"]["job"]["value"] = $return["job"]["data"]->name;
			}
			
			#Category
			if(!empty($apply->category)) 
			{ 
				$return["category"] = $this->getCategory($apply->category);
				$return["details"]["category"]["value"] = $return["category"]->name;
			}
			
			#From
			if(!empty($apply->fromID)) 
			{ 
				$return["from"] = $this->getFrom($apply->fromID);
				$return["details"]["from"]["value"] = $return["from"]->name;
			}
			
			#CV
			if(!empty($apply->cv))
			{
				$file = new \App\Http\Controllers\FileController;
				$return["cv"] = $file->getFile($apply->cv);
				$return["details"]["cv"]["value"] = "<a href='".$return["cv"]["path"]["web"]."' target='_blank'>".$return["cv"]["fullName"]."</a>";
			}
		}
		return $return;
	}
	
	public function getApplies($deleted = 0, $key = "id")
	{
		$return = [
			"job" => [],
			"category" => [],
			"all" => [],
		];
		$rows = $this->model->getApplies($deleted);		
		foreach($rows AS $row) 
		{ 
			$return["all"][$row->$key] = $this->getApply($row->id); 
			if(!empty($return["all"][$row->$key]["data"]->job)) { $return["job"][$row->$key] = $return["all"][$row->$key]; }
			if(!empty($return["all"][$row->$key]["data"]->category)) { $return["category"][$row->$key] = $return["all"][$row->$key]; }
		}		
		return $return;
	}
}
