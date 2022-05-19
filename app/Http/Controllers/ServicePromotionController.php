<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\ServicePromotion;

class ServicePromotionController extends BaseController
{	
	public $model;
	public $info;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "ServicePromotion";
		$this->model = new \App\ServicePromotion($connectionData);
	}
	
	public function getPromotion($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getPromotion($id); }
		if(!empty($row) AND is_object($row) AND isset($row->id) AND !empty($row->id))
		{
			#Details
			$return = [
				"data" => $row,
				"name" => $row->name,
				"text" => $row->text,
				"shortText" => mb_substr(strip_tags($row->text), 0, 200, "UTF-8")."...",
				"dateOut" => (!empty($row->date) AND $row->date != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->date)) : "",
				"activeFromOut" => (!empty($row->activeFrom) AND $row->activeFrom != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->activeFrom)) : "",
				"activeToOut" => (!empty($row->activeTo) AND $row->activeTo != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->activeTo)) : "",
				"active" => $this->promotionIsActive($row->activeFrom, $row->activeTo),
				"activeString" => "",
				"sites" => [],
				"brands" => [],
				"mainBrand" => NULL,
				"pic" => false,
				"picLink" => "",
			];
			
			#Active?
			$return["activeString"] = ($return["active"]) ? "<strong class='text-success'>Aktív</strong>" : "<span class='color-red'>Inaktív</span>";
			
			#Sites
			if(!empty($row->sites))
			{
				$sites = trim($row->sites, "|");
				$return["sites"] = explode("|", $sites);
			}
			
			#Brands and main brand
			if(!empty($row->brands))
			{
				$brands = trim($row->brands, "|");
				$return["brands"] = explode("|", $brands);
			}
			if(count($return["brands"]) == 1) { $return["mainBrand"] = $return["brands"][0]; }
			
			#Pic
			if(!empty($row->pic))
			{
				$file = new \App\Http\Controllers\FileController;
				$return["pic"] = $file->getFile($row->pic);
				if(empty($return["pic"]["id"]) OR $return["pic"]["data"]->del) { $return["pic"] = false; }
				else { $return["picLink"] = $return["pic"]["path"]["web"]; }
			}
			
			return $return;
		}
		else { return false; }
	}
	
	public function promotionIsActive($activeFrom, $activeTo)
	{
		$active = true;
		$date = date("Y-m-d H:i:s");
		if($active AND !empty($activeFrom) AND $activeFrom != "0000-00-00 00:00:00")
		{
			if($date < $activeFrom) { $active = false; }
		}
		if($active AND !empty($activeTo) AND $activeTo != "0000-00-00 00:00:00")
		{
			if($date > $activeTo) { $active = false; }
		}
		
		return $active;
	}
	
	public function getPromotions($activeFrom = NULL, $activeTo = NULL, $brands = NULL, $sites = NULL, $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getPromotions($activeFrom, $activeTo, $brands, $sites, $deleted);
		if($rows AND count($rows) > 0)
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getPromotion($row->id, $row); }
		}
		
		return $return;
	}
	
	public function getActivePromotions($brands = NULL, $sites = NULL)
	{
		$date = date("Y-m-d H:i:s");
		return $this->getPromotions($date, $date, $brands, $sites);
	}
	
	public function delPromotion($id)
	{
		return $this->model->myDelete($this->model->tables("promotions"), $id);
	}
	
	public function promotionWork($type, $datas)
	{
		if($type == "new" OR $type == "edit")
		{
			#Params
			$params = [];
			$fields = ["name", "text", "link", "activeFrom", "activeTo"];
			foreach($fields AS $field)
			{
				if(isset($datas[$field]) AND !empty($datas[$field])) { $params[$field] = $datas[$field]; }
				else { $params[$field] = NULL; }
			}
			
			#Checkboxes to string
			if(isset($datas["sites"]) AND !empty($datas["sites"])) { $params["sites"] = "|".implode("|", $datas["sites"])."|"; }
			else { $params["sites"] = NULL; }
			
			if(isset($datas["brands"]) AND !empty($datas["brands"])) { $params["brands"] = "|".implode("|", $datas["brands"])."|"; }
			else { $params["brands"] = NULL; }
			
			#Insert or Update
			if($type == "new") 
			{ 
				$params["date"] = date("Y-m-d H:i:s");
				$id = $this->model->myInsert($this->model->tables("promotions"), $params);
			}
			elseif($type == "edit") 
			{
				$id = $datas["id"];
				$this->model->myUpdate($this->model->tables("promotions"), $params, $id);
			}
			
			#Picture
			$file = new \App\Http\Controllers\FileController;
			$fileReturn = $file->upload("pic", "service-promotions-pic", $id);
			if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($this->model->tables("promotions"), ["pic" => $fileReturn[0]["fileID"]], $id); }
			
			return $id;
		}
		elseif($type == "picDel") { return $this->model->myUpdate($this->model->tables("promotions"), ["pic" => NULL], $datas["id"]); }
		else { return NULL; }
	}
}
