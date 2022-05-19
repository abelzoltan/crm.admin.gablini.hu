<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Content;

class ContentController extends BaseController
{	
	public $model;
	public $files = "//admin.gablini.hu/cdn/gablini.hu/images/";
	public $filesNews;
	public $newsDetailsURL = "hir/";
	public $newsURL = "hirek/";
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Content";
		$this->model = new \App\Content($connectionData);
		$this->filesNews = $this->files."hirek/";
	}
	
	public function getNewsByID($id)
	{
		$return = [];
		$return["data"] = $data = $this->model->getNewsByID($id);
		$return["detailsImage"] = $this->filesNews."eredeti/".$data->oldID.".jpg";
		$return["detailsImageKia"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=kia-web";
		$return["detailsImageHyundai"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=hyundai-web-details";
		$return["detailsImageCitroen"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=citroen-web";
		$return["listImage"] = $this->filesNews."g1/".$data->oldID.".jpg?marka=gablini-web";
		$return["listImageKia"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=kia-web";
		$return["listImageHyundai"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=hyundai-web";
		$return["listImageCitroen"] = $this->filesNews."eredeti/".$data->oldID.".jpg?marka=citroen-web";
		$return["urlPath"] = $this->newsDetailsURL.$data->name;
		$return["url"] = setLink($return["urlPath"]);
		$return["name"] = $data->nameOut;
		$return["text"] = $data->text;
		$return["date"] = strftime("%Y. %B %d.", strtotime($data->dateOut));
		
		$shortText = strip_tags($data->shortText);	
		if(!empty($shortText))
		{
			if(strlen($shortText) > 550) { $shortText = nl2br(mb_substr($shortText, 0, 550, "utf-8"))."[...]";  }
			else { $shortText = nl2br($shortText); }
		}
		$return["shortText"] = $shortText;
		
		$return["sites"] = explode("|", $data->sites);
		
		#List picture
		$file = new \App\Http\Controllers\FileController;
		$return["pic"] = $return["picDetails"] = NULL;
		if(!empty($data->pic)) 
		{ 
			$return["pic"] = $file->getFile($data->pic); 
			if(!empty($return["pic"]["path"]["inner"]) AND file_exists($return["pic"]["path"]["inner"])) { $return["listImage"] = $return["listImageKia"] = $return["listImageHyundai"] = $return["listImageCitroen"] = $return["pic"]["path"]["web"];  }
		}
		if(!empty($data->picDetails)) 
		{ 
			$return["picDetails"] = $file->getFile($data->picDetails); 
			if(!empty($return["picDetails"]["path"]["inner"]) AND file_exists($return["picDetails"]["path"]["inner"])) { $return["detailsImage"] = $return["detailsImageKia"] = $return["detailsImageHyundai"] = $return["detailsImageCitroen"] = $return["picDetails"]["path"]["web"];  }
		}
		
		return $return;
	}
	
	public function getNewsByName($name)
	{
		$row = $this->model->getNewsByName($name);
		return $this->getNewsByID($row->id);
	}
	
	public function getNews($sites = [], $limit = NULL, $returnOrderNumber = false, $order = "id DESC")
	{
		$rows = $this->model->getNews($order);
		$return = [];
		
		$count = 0;
		foreach($rows AS $i => $row)
		{
			$okay = false;
			if(empty($sites)) { $okay = true; }
			else
			{
				$rowSites = explode("|", $row->sites);
				foreach($sites AS $site)
				{
					if(in_array($site, $rowSites)) 
					{ 
						$okay = true;
						break;
					}
				}
			}
			
			if($okay)
			{
				if(!isset($return[$row->name]))
				{  
					$count++;
					if($limit !== NULL AND $count <= $limit["from"]) { continue; }
					
					if($returnOrderNumber) { $return[$row->name] = $row->id; }
					else { $return[$row->name] = $this->getNewsByID($row->id); }
					
					if($limit !== NULL AND $count > $limit["to"]) { break; }
				}
			}
		}
		
		if($returnOrderNumber) { return count($return); }
		else { return $return; }
	}
	
	#Adminwork
	public function adminWork($type, $workType, $datas)
	{
		$table = $this->model->tables("contents");
		$fields = ["sites", "name", "nameOut", "shortText", "text", "keywords", "metaKeywords", "metaDescription", "author", "sourceDate", "sourceLink", "sourceName", "dateActiveFrom", "dateActiveTo"];
		$required = ["name", "nameOut"];
		$return = [
			"work" => $workType,
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
			"params" => [],
			"id" => NULL,
		];
		
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
					if(!$this->checkContentsURL($datas["name"], $type->id, $id)) 
					{ 
						$datas["name"] = $this->setContentsURL($datas["nameOut"], $type->id, $id);
						$return["errors"]["changed"][] = "url";
					}
					
					#Params
					$params = [];
					foreach($fields AS $field)
					{
						if($field == "sites") 
						{
							if(isset($datas[$field]) AND !empty($datas[$field])) { $datas[$field] = implode("|", $datas[$field]); }
							else { $datas[$field] = ""; }
						}
						elseif($field == "keywords" AND (!isset($datas[$field]) OR empty($datas[$field]))) { $datas[$field] = $datas["nameOut"]; }
						elseif($field == "metaKeywords") { $datas[$field] = $datas["keywords"]; }
						elseif($field == "metaDescription") { $datas[$field] = $datas["shortText"]; }
						elseif(($field == "sourceDate" OR $field == "dateActiveFrom" OR $field == "dateActiveTo") AND (!isset($datas[$field]) OR empty($datas[$field]) OR $datas[$field] == "0000-00-00" OR $datas[$field] == "0000-00-00 00:00:00")) { $datas[$field] = NULL; }
						
						if(isset($datas[$field])) { $params[$field] = $datas[$field]; }
					}
					
					#Database command
					if($workType == "new") 
					{ 
						$fields[] = "type";
						$fields[] = "dateOut";
						$fields[] = "orderNumber";
						$params["type"] = $type->id;
						$params["dateOut"] = date("Y-m-d H:i:s");
						$params["orderNumber"] = 0;
						
						$return["id"] = $id = $this->model->myInsert($table, $params);
					}
					else 
					{ 
						$return["id"] = $id;
						$this->model->myUpdate($table, $params, $id);
					}
					$return["params"] = $params;
					
					#Pics
					$file = new \App\Http\Controllers\FileController;					
					$return["pic"] = $fileReturn = $file->upload("pic", "contents", $id);
					if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($table, ["pic" => $fileReturn[0]["fileID"]], $id); }
					
					$return["picDetails"] = $fileReturn = $file->upload("picDetails", "contents-details", $id);
					if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($table, ["picDetails" => $fileReturn[0]["fileID"]], $id); }
				}
				break;
			case "del":
				$this->model->myDelete($table, $datas["id"]);
				break;
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		
		if(isset($GLOBALS["log"]))
		{
			$logData = [
				"vchar1" => "Content",
				"vchar2" => "contents",
				"vchar3" => $workType,
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("adminwork", $logData);
		}
		return $return;
	}
	
	#Generate Contents URL
	public function setContentsURL($name, $type, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("contents"), "name", $name, "", ["del" => 0, "type" => $type], $id);
	}
	
	#Check Contents URL duplication
	public function checkContentsURL($name, $type, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("contents"), "name", $name, ["del" => 0, "type" => $type], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
}
