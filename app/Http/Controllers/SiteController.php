<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Site;

class SiteController extends BaseController
{
	public $routes;
	public $data;
	public $name;
	public $baseName;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Site";
		$this->model = new \App\Site($connectionData);
		
		#URL
		if(isset($GLOBALS["rootURL"]))
		{
			$url = $GLOBALS["rootURL"];
			
			#Get site
			$siteKey = $_SERVER["SERVER_NAME"];
			$this->data = $this->model->getSiteByBaseName($siteKey);
			if(empty($this->data->id))
			{
				if(!empty($url->routes)) { $siteKey = $url->routes[0]; }
				else { $siteKey = ""; }
				$this->data = $this->model->getSiteByBaseName($siteKey);
			}

			if(!empty($this->data->id))
			{
				if(!empty($this->data->baseName)) 
				{ 
					$this->name = $this->data->baseName; 
					$this->baseName = $this->name."/";
				}
				else 
				{ 
					$this->name = $this->data->url; 
					$this->baseName = "";
				}
				$this->routes = $this->name.".php";
			}
		}
	}
	
	#Pager
	public function pager($rowCount, $itemsPerPage = 25, $link = NULL, $getDataName = "oldal")
	{
		#Basic
		$getData = (isset($GLOBALS["URL"])) ? $GLOBALS["URL"]->get : $_GET;
		if($link === NULL) { $link = (isset($GLOBALS["URL"])) ? $GLOBALS["URL"]->currentURL : env("PATH_ROOT_WEB"); }
		
		#Set from and to
		$allPages = ceil($rowCount / $itemsPerPage);
		if(isset($_GET[$getDataName])) 
		{ 
			if($_GET[$getDataName] >= ($allPages - 1)) { $activePage = $allPages - 1; }
			elseif(empty($_GET[$getDataName]) OR $_GET[$getDataName] < 0) { $activePage = 0; }
			else { $activePage = $_GET[$getDataName]; }
		}
		else { $activePage = 0; }
		$from = $activePage * $itemsPerPage;
		
		$return = [
			"link" => $link,
			"getDataName" => $getDataName,
			"itemsPerPage" => $itemsPerPage,
			"rowCount" => $rowCount,
			"allPages" => $allPages,
			"activePage" => $activePage,
			"from" => $from,
			"to" => $from + $itemsPerPage - 1,
			"pageList" => [],
		];
		
		#Set pages
		for($i = 1; $i <= $return["allPages"]; $i++)
		{
			$page = [];
			$page["index"] = $i;
			$page["page"] = $i - 1;
			$page["from"] = $page["page"] * $return["itemsPerPage"];
			$page["fromOut"] = $page["from"] + 1;
			if($i < $return["allPages"]) 
			{ 
				$page["to"] = $page["from"] + $return["itemsPerPage"] - 1; 
				$page["toOut"] = $page["to"] + 1;
			}
			else { $page["to"] = $page["toOut"] = $rowCount; }
			$page["label"] = $page["fromOut"]." - ".$page["toOut"];
			 
			if($i > 1) { $getData[$getDataName] = $page["page"]; }
			elseif(isset($getData[$getDataName])) { unset($getData[$getDataName]); }
			$page["link"] = $link;
			if(!empty($getData)) { $page["link"] .= "?".http_build_query($getData); }
			
			$return["pageList"][$page["page"]] = $page;
		}
		
		return $return;
	}
	
	#Get site
	public function getSite($id, $allData = true)
	{
		$return = [];
		$return["data"] = $site = $this->model->getSite($id);
		
		if($allData)
		{
			$return["sliders"] = $this->getActiveSlides($site->id);
			$return["slider"] = [];
			$return["sliderMobile"] = [];
			foreach($return["sliders"] AS $rowID => $row)
			{
				if(isset($row["picPC"])) { $return["slider"][$rowID] = ["data" => $row["data"], "file" => $row["picPC"]]; }
				if(isset($row["picMobile"])) { $return["sliderMobile"][$rowID] = ["data" => $row["data"], "file" => $row["picMobile"]]; }
			}
		}
		
		return $return;
	}
	
	public function getSiteByURL($url, $allData = true)
	{
		$id = $this->model->getSiteByURL($url, "id");
		return $this->getSite($id, $allData);
	}
	
	#Get all sites
	public function getSites($orderBy = "url", $key = "url")
	{
		$rows = $this->model->getSites($orderBy);
		$return = [];
		foreach($rows AS $i => $row) 
		{ 
			if(!empty($key)) { $keyHere = $row->$key; }
			else { $keyHere = $i; }
			$return[$keyHere] = $row; 
		}
		
		return $return;
	}
	
	#Get slides
	public function getSlide($id, $allData = true)
	{
		$return = [];
		$return["data"] = $slide = $this->model->getSlide($id);
		if($allData)
		{
			$file = new \App\Http\Controllers\FileController;
			if(!empty($slide->picPC)) { $return["picPC"] = $file->getFile($slide->picPC); }
			if(!empty($slide->picMobile)) { $return["picMobile"] = $file->getFile($slide->picMobile); }
		}
		
		return $return;
	}
	
	public function getSlides($site, $deleted = 0, $key = "id")
	{
		$return = [];
		$rows = $this->model->getSlides($site, $deleted);		
		foreach($rows AS $row) { $return[$row->$key] = $this->getSlide($row->id, false); }		
		return $return;
	}
	
	#Get active slides
	public function getActiveSlides($site)
	{
		$return = [];
		$rows = $this->model->getActiveSlides($site);
		foreach($rows AS $row) { $return[$row->id] = $this->getSlide($row->id, true); }	
		return $return;		
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
			case "sliders":
				$fields = ["name", "href", "hrefTargetBlank", "dateFrom", "dateTo", "models", "buttonText", "buttonStyle"];
				$required = ["name"];
				$orderWhere = "del = '0' AND site = :site";
				$orderParams = ["site" => $datas["site"]];
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
							#Params
							$params = [];
							foreach($fields AS $field)
							{
								if($field == "active" AND (!isset($datas[$field]) OR empty($datas[$field]))) { $datas[$field] = 0; }
								if($field == "dateFrom" AND (!isset($datas[$field]) OR empty($datas[$field]))) { $datas[$field] = NULL; }
								if($field == "dateTo" AND (!isset($datas[$field]) OR empty($datas[$field]))) { $datas[$field] = NULL; }
								$params[$field] = $datas[$field];
							}
							
							#OrderNumber, database command
							if($workType == "new") 
							{ 
								$fields[] = "site";
								$params["site"] = $datas["site"];
								
								$fields[] = "orderNumber";
								$params["orderNumber"] = $this->model->reOrder($table, $orderWhere, $orderParams);
								
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
						$this->model->reOrder($table, $orderWhere, $orderParams);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table, $orderWhere, $orderParams);
						break;
					case "pic-pc":	
						$this->model->myUpdate($table, ["picPC" => $datas["pic"]], $datas["id"]);
						break;
					case "pic-mobile":	
						$this->model->myUpdate($table, ["picMobile" => $datas["pic"]], $datas["id"]);
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
				"vchar1" => "Site",
				"vchar2" => $tableName,
				"vchar3" => $workType,
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("adminwork", $logData);
		}
		return $return;
	}
}
