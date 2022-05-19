<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\WebAddress;

class WebAddressController extends BaseController
{	
	public $model;
	public $info;
	public $addressTypes;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "WebAddress";
		$this->model = new \App\WebAddress($connectionData);
		
		$this->addressTypes = [
			"to" => "Címzett",
			"cc" => "Másolat",
			"bcc" => "Titkos másolat",
			"info" => "Információ",
		];
	}
	
	#Get address
	public function getAddress($id, $row = NULL, $usersModal = NULL)
	{
		if($row === NULL) { $row = $this->model->getAddress($id); }
		if(!empty($row) AND is_object($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [
				"id" => $row->id,
				"data" => $row,
				"date" => date("Y. m. d. H:i", strtotime($row->date)),
				"active" => ($row->active),
				"activeString" => "",
				"user" => false,
				"name" => $row->name,
				"email" => $row->email,
				"rowType" => "other",
				"rowTypeName" => "További címzett",
				"typeName" => (isset($this->addressTypes[$row->type])) ? $this->addressTypes[$row->type] : $row->type,
			];
			
			$return["activeString"] = ($return["active"]) ? "<strong class='text-success'>Aktív</strong>" : "<span class='color-red'>Inaktív</span>";
			
			if(!empty($row->user))
			{
				$return["rowType"] = "user";
				$return["rowTypeName"] = "Munkatárs";
				
				if(empty($usersModal)) { $usersModal = new \App\User(["name" => MYSQL_CONNECTION_NAME_G]); }
				$userRows = $usersModal->select("SELECT id, email, firstName, lastName, del FROM ".$usersModal->tables("users")." WHERE id = :id", ["id" => $row->user]);
				if(!empty($userRows[0]) AND is_object($userRows[0]) AND isset($userRows[0]->id) AND !empty($userRows[0]->id))
				{
					$return["user"] = $userRows[0];
					$return["name"] = $return["user"]->lastName." ".$return["user"]->firstName;
					if($return["user"]->del) { $return["name"] .= " <strong class='color-red'><em>[TÖRÖLT]</em></strong>"; }
					$return["email"] = $return["user"]->email;
				}
			}
			
			return $return;
		}
		else { return false; }
	}
	
	#Get addresses
	public function getAddresses($search = [], $orderBy = "name", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getAddresses($search, $orderBy, $deleted);
		if($rows AND count((array)$rows) > 0)
		{
			$usersModal = new \App\User(["name" => MYSQL_CONNECTION_NAME_G]);
			foreach($rows AS $row) { $return[$row->id] = $this->getAddress($row->id, $row, $usersModal); }
		}
		
		return $return;
	}
	
	public function getAddressesForAdminList($categoryID)
	{
		return $this->getAddresses([["category", $categoryID]]);
	}
	
	public function getAddressesForSending($categoryID)
	{
		$return = [
			"to" => [],
			"cc" => [],
			"bcc" => [],
			"all" => [],
		];
		$rows = $this->getAddresses([["category", $categoryID], ["active", 1]]);
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				if(empty($row["email"])) { continue; }
				if(!filter_var($row["email"], FILTER_VALIDATE_EMAIL)) { continue; }
				
				if($row["data"]->type != "info")
				{
					$okay = true;
					if($row["user"] !== false)
					{
						if($row["user"]->del) { $okay = false; }
					}
					
					if($okay)
					{
						$return["all"][] = ["type" => $row["data"]->type, "email" => $row["email"], "name" => $row["name"]];
						$return[$row["data"]->type][$row["email"]] = $row["name"];
					}
				}
			}
		}
		
		return $return;
	}
	
	public function getAddressesForSendingByURL($categoryURL)
	{
		$catID = $this->model->getCategoryByURL($categoryURL, "id");
		$id = (!empty($catID)) ? $catID : -1;
		
		return $this->getAddressesForSending($id);
	}
	
	#Get category
	public function getCategory($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getCategory($id); }
		if(!empty($row) AND is_object($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [
				"id" => $row->id,
				"data" => $row,
				"date" => date("Y. m. d. H:i", strtotime($row->date)),
			];	
			
			return $return;
		}
		else { return false; }
	}
	
	public function getCategoryByURL($url)
	{
		$row = $this->model->getCategoryByURL($url);
		return $this->getCategory(NULL, $row);
	}
	
	#Get categories
	public function getCategories($search = [], $orderBy = "name", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getCategories($search, $orderBy, $deleted);
		if($rows AND count((array)$rows) > 0)
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getCategory($row->id, $row); }
		}
		
		return $return;
	}
	
	public function getMainCategories($orderBy = "name", $deleted = 0)
	{
		$search = [
			["parentID", "IS NULL"]
		];
		
		return $this->getCategories($search, $orderBy, $deleted);
	}
	
	public function getSubCategories($parentID, $orderBy = "name", $deleted = 0)
	{
		$search = [
			["parentID", $parentID]
		];
		
		return $this->getCategories($search, $orderBy, $deleted);
	}
	
	public function getCategoriesForAdminList()
	{
		$return = [];
		
		$mainCategories = $this->getMainCategories();
		if(count($mainCategories) > 0)
		{
			foreach($mainCategories AS $mainCategory)
			{
				$subCategories = $this->getSubCategories($mainCategory["data"]->id);
				if(count($subCategories) > 0)
				{
					$return[$mainCategory["data"]->id] = [
						"category" => $mainCategory,
						"rows" => $subCategories,
					];
				}
			}
		}
		
		return $return;
	}
	
	#Admin processes
	public function adminProcessesCategories($type, $datas = [])
	{
		$table = $this->model->tables("categories");
		$return = [
			"errors" => [],
			"type" => $type,
			"datas" => $datas,
			"id" => NULL,
		];
		
		switch($type)
		{
			case "new":
			case "edit":
				if(!isset($datas["work"]) OR $datas["work"] != $type) { $return["errors"][] = "work"; }
				else
				{
					$return["id"] = (isset($datas["id"]) AND !empty($datas["id"])) ? $datas["id"] : NULL;
					
					if($type == "edit" AND empty($return["id"])) { $return["errors"][] = "id"; }
					else
					{
						$params = [];					
						$params["name"] = (isset($datas["name"]) AND !empty($datas["name"])) ? $datas["name"] : NULL;
						$params["description"] = (isset($datas["description"]) AND !empty($datas["description"])) ? $datas["description"] : NULL;
						$params["parentID"] = (isset($datas["parentID"]) AND !empty($datas["parentID"])) ? $datas["parentID"] : NULL;					
						$params["url"] = (isset($datas["url"]) AND !empty($datas["url"])) ? $datas["url"] : NULL;
						$params["emailSubject"] = (isset($datas["emailSubject"]) AND !empty($datas["emailSubject"])) ? $datas["emailSubject"] : NULL;
						$params["ftpDir"] = (isset($datas["ftpDir"]) AND !empty($datas["ftpDir"])) ? $datas["ftpDir"] : NULL;
						$params["ftpFile"] = (isset($datas["ftpFile"]) AND !empty($datas["ftpFile"])) ? $datas["ftpFile"] : NULL;
						
						$checkURLParams = [
							"url" => $params["url"],
							"id" => $return["id"],
						];
						
						$setURLParams = [
							"name" => $params["name"],
							"id" => $return["id"],
							"parentID" => $params["parentID"],
						];
						
						if(empty($params["url"]) OR !$this->adminProcessesCategories("check-url", $checkURLParams)) { $params["url"] = $this->adminProcessesCategories("set-url", $setURLParams); }
						
						if($type == "new")
						{
							$params["date"] = date("Y-m-d H:i:s");
							$return["id"] = $this->model->myInsert($table, $params);
						}
						else { $this->model->myUpdate($table, $params, $return["id"]); }
					}
				}
				break;
			case "del":
				if(isset($datas["id"]) AND !empty($datas["id"]))
				{
					$return["id"] = $datas["id"];
					$this->model->myDelete($table, $return["id"]);
					$this->adminProcessesCategories("re-order");
				}
				else { $return["errors"][] = "id"; }
				break;
			case "set-url":
				$id = (isset($datas["id"]) AND !empty($datas["id"])) ? $datas["id"] : 0;
				$name = (isset($datas["name"]) AND !empty($datas["name"])) ? $datas["name"] : uniqid("", true);
				
				if(isset($datas["parentID"]) AND !empty($datas["parentID"]))
				{
					$parentURL = $this->model->getCategory($datas["parentID"], "url");
					if(!empty($parentURL)) { $name = $parentURL."-".$name; }
				}
				
				$return = $this->setUniqueURL($table, "url", $name, "", [], $id);
				break;
			case "check-url":
				$id = (isset($datas["id"]) AND !empty($datas["id"])) ? $datas["id"] : 0;
				$url = (isset($datas["url"]) AND !empty($datas["url"])) ? $datas["url"] : uniqid("", true);
				$rows = $this->model->checkUniqueField($table, "url", $url, [], $id);
				return (count($rows) == 0);
				break;	
			default:
				$return["errors"][] = "unknown-type";
				break;		
		}
		
		return $return;
	}
	
	public function adminProcessesAddresses($type, $datas = [])
	{
		$table = $this->model->tables("addresses");
		$return = [
			"errors" => [],
			"type" => $type,
			"datas" => $datas,
			"id" => NULL,
		];
		
		switch($type)
		{
			case "new":
				if(!isset($datas["work"]) OR $datas["work"] != $type) { $return["errors"][] = "work"; }
				elseif(!isset($datas["category"]) OR empty($datas["category"])) { $return["errors"][] = "category"; }
				else
				{
					$date = date("Y-m-d H:i:s");
					
					#Add users
					if(isset($datas["users"]) AND count($datas["users"]) > 0)
					{
						$return["webAddressUserList"] = [];
						foreach($datas["users"] AS $userIndex => $userData)
						{
							if(isset($userData["id"]) AND !empty($userData["id"]))
							{
								$params = [
									"date" => $date,
									"category" => $datas["category"],
									"user" => $userData["id"],
									"type" => (isset($userData["addressType"]) AND !empty($userData["addressType"]) AND isset($this->addressTypes[$userData["addressType"]])) ? $userData["addressType"] : "to",
									"comment" => (isset($userData["comment"]) AND !empty($userData["comment"])) ? $userData["comment"] : NULL,
									"active" => (isset($userData["active"]) AND $userData["active"]) ? 1 : 0,
								];
								
								$return["webAddressUserList"][] = $this->model->myInsert($table, $params);
							}
						}
					}
					
					#Add other addresses
					if(isset($datas["addresses"]) AND count($datas["addresses"]) > 0)
					{
						$return["webAddressOtherList"] = [];
						foreach($datas["addresses"] AS $addressIndex => $addressData)
						{
							if(isset($addressData["email"]) AND !empty($addressData["email"]))
							{
								$params = [
									"date" => $date,
									"category" => $datas["category"],
									"email" => $addressData["email"],
									"name" => (isset($addressData["name"]) AND !empty($addressData["name"])) ? $addressData["name"] : NULL,
									"type" => (isset($addressData["addressType"]) AND !empty($addressData["addressType"]) AND isset($this->addressTypes[$addressData["addressType"]])) ? $addressData["addressType"] : "to",
									"comment" => (isset($addressData["comment"]) AND !empty($addressData["comment"])) ? $addressData["comment"] : NULL,
									"active" => (isset($addressData["active"]) AND $addressData["active"]) ? 1 : 0,
								];
								
								$return["webAddressOtherList"][] = $this->model->myInsert($table, $params);
							}
						}
					}
				}
				break;
			case "edit":
				if(!isset($datas["work"]) OR $datas["work"] != $type) { $return["errors"][] = "work"; }
				else
				{
					if(!isset($datas["id"]) OR empty($datas["id"])) { $return["errors"][] = "id"; }
					else
					{						
						$row = $this->getAddress($datas["id"]);
						if($row !== false)
						{
							$params = [];
							$params["active"] = (isset($datas["active"]) AND $datas["active"]) ? 1 : 0;
							$params["type"] = (isset($datas["type"]) AND !empty($datas["type"])) ? $datas["type"] : "to";
							$params["comment"] = (isset($datas["comment"]) AND !empty($datas["comment"])) ? $datas["comment"] : NULL;
							
							if(empty($row["data"]->user))
							{
								$params["name"] = (isset($datas["name"]) AND !empty($datas["name"])) ? $datas["name"] : NULL;
								$params["email"] = (isset($datas["email"]) AND !empty($datas["email"])) ? $datas["email"] : NULL;
							}
							
							$this->model->myUpdate($table, $params, $datas["id"]);
							$return["id"] = $datas["id"];
						}
						else { $return["errors"][] = "id"; }
					}
				}
				break;
			case "del":
				if(isset($datas["id"]) AND !empty($datas["id"]))
				{
					$row = $this->model->getAddress($datas["id"]);
					if(!empty($row) AND is_object($row) AND isset($row->id) AND !empty($row->id))
					{
						$return["id"] = $datas["id"];
						$this->model->myDelete($table, $return["id"]);
						$this->adminProcessesAddresses("re-order", ["category" => $row->category]);
					}
				}
				else { $return["errors"][] = "id"; }
				break;
			case "activate":
				if(isset($datas["id"]) AND !empty($datas["id"]))
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, ["active" => 1], $return["id"]);
				}
				else { $return["errors"][] = "id"; }
				break;
			case "deactivate":
				if(isset($datas["id"]) AND !empty($datas["id"]))
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, ["active" => 0], $return["id"]);
				}
				else { $return["errors"][] = "id"; }
				break;
			default:
				$return["errors"][] = "unknown-type";
				break;		
		}
		
		return $return;
	}
	
	#Export
	public function export($saveFileToTemp = false)
	{
		#Output
		if($saveFileToTemp)
		{
			$fileName = uniqid("", true).".csv";
			$filePath = base_path("_temp-files/".$fileName);
			$output = fopen($filePath, "w");
		}
		else
		{
			$fileName = date("Ymd")."_email_cimzettek.csv";
			header("Content-Type: text/csv; charset=iso-8859-2");
			header("Content-Disposition: attachment; filename=\"".$fileName."\"");
			$output = fopen("php://output", "w");
		}
		
		#Header row
		$out = [
			iconv("utf-8", "iso-8859-2", "Főkategória (Tartó)"),
			iconv("utf-8", "iso-8859-2", "Kategória"),
			iconv("utf-8", "iso-8859-2", "E-mail tárgya"),
			iconv("utf-8", "iso-8859-2", "Cím típusa"),
			iconv("utf-8", "iso-8859-2", "Név"),
			iconv("utf-8", "iso-8859-2", "E-mail cím"),
			iconv("utf-8", "iso-8859-2", "Típus"),
			iconv("utf-8", "iso-8859-2", "Aktív?"),
			iconv("utf-8", "iso-8859-2", "Megjegyzés"),
		];
		fputcsv($output, $out, ";"); 
		
		#Loop main categories
		$mainCategories = $this->getMainCategories();
		if(count($mainCategories) > 0)
		{
			$i = 0;
			foreach($mainCategories AS $mainCategory)
			{
				$subCategories = $this->getSubCategories($mainCategory["data"]->id);
				if(count($subCategories) > 0)
				{
					$j = 0;
					#Loop sub categories
					foreach($subCategories AS $subCategory)
					{
						$addresses = $this->getAddresses([["category", $subCategory["data"]->id]]);
						if(count($addresses) > 0)
						{
							#Loop addresses
							foreach($addresses AS $address)
							{
								$out = [
									iconv("utf-8", "iso-8859-2", $mainCategory["data"]->name),
									iconv("utf-8", "iso-8859-2", $subCategory["data"]->name),
									iconv("utf-8", "iso-8859-2", strip_tags(str_replace(["\n", "\r", "\t"], " ", $subCategory["data"]->emailSubject))),
									iconv("utf-8", "iso-8859-2", $address["rowTypeName"]),
									iconv("utf-8", "iso-8859-2", $address["name"]),
									iconv("utf-8", "iso-8859-2", $address["email"]),
									iconv("utf-8", "iso-8859-2", $address["typeName"]),
									iconv("utf-8", "iso-8859-2", strip_tags($address["activeString"])),
									iconv("utf-8", "iso-8859-2", strip_tags(str_replace(["\n", "\r", "\t"], " ", $address["data"]->comment))),
								];
								fputcsv($output, $out, ";"); 
							}
						}
						#NO addresses
						else
						{
							$out = [
								iconv("utf-8", "iso-8859-2", $mainCategory["data"]->name),
								iconv("utf-8", "iso-8859-2", $subCategory["data"]->name),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
								iconv("utf-8", "iso-8859-2", "-"),
							];
							fputcsv($output, $out, ";"); 
						}
						
						$j++;
					}
				}
				#NO sub categories
				else
				{
					$out = [
						iconv("utf-8", "iso-8859-2", $mainCategory["data"]->name),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
						iconv("utf-8", "iso-8859-2", "-"),
					];
					fputcsv($output, $out, ";"); 
				}
				$i++;
			}
		}
		
		#Close file
		fclose($output);
		if($saveFileToTemp) { return $filePath; }
		else { exit; }
	}
}
