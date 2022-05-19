<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Customer;

class CustomerController extends BaseController
{	
	public $codeBefore = "GA";
	public $hashSaltBefore = "UFiO0o9f0V";
	public $hashSaltAfter = "pA8wYoVvXN";
	
	public $codeName = "Ügyfélszám";
	public $tokenName = "Token";
	
	public $nameUsed = 1;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Customer";
		$this->model = new \App\Customer($connectionData);
	}
	
	#Get customers
	public function getCustomer($id, $allDatas = true)
	{
		$customer = $this->model->getCustomer($id);
		if(!empty($customer) AND isset($customer->id) AND !empty($customer->id))
		{
			$return = [];
			$return["data"] = $customer;
			
			$return["id"] = $customer->id;
			$return["email"] = $customer->email;
			$return["code"] = $customer->code;
			$return["progressCode"] = $customer->progressCode;
			$return["hash"] = $customer->hash;
			$return["token"] = $customer->token;
			$return["tokenHashed"] = sha1($customer->token);
			$return["tireContractNumber"] = $customer->tireContractNumber;			
			
			$return["companyName"] = $customer->companyName;
			$return["name1"] = $customer->lastName." ".$customer->firstName;
			$return["name2"] = $customer->firstName." ".$customer->lastName;
			$return["fullName"] = $return["name"] = $return["name".$this->nameUsed];

			if(!empty($customer->beforeName)) { $return["fullName"] = $customer->beforeName." ".$return["fullName"]; }
			if(!empty($customer->afterName)) { $return["fullName"] .= " ".$customer->afterName; }
			
			$users = new \App\Http\Controllers\UserController;
			$return["user"] = $users->getUser($customer->user);
			$return["userName"] = $return["user"]["name"];
			
			if(empty($customer->date) OR $customer->date == "0000-00-00" OR $customer->date == "0000-00-00 00:00:00")
			{
				$return["date"] = "";
				$return["dateOut"] = "N/A";
			}
			else
			{
				$return["date"] = $customer->date;
				$return["dateOut"] = strftime("%Y. %B %d.", strtotime($customer->date));
			}
			
			$return["phone"] = $customer->phone;
			if(empty($customer->phone)) { $return["phoneOut"] = "N/A"; }
			else { $return["phoneOut"] = $customer->phone; }
			
			$return["mobile"] = $customer->mobile;
			if(empty($customer->mobile)) { $return["mobileOut"] = "N/A"; }
			else { $return["mobileOut"] = $customer->mobile; }
			
			if($allDatas)
			{
				#Import datas
				
				#Cars
				$return["cars"] = $this->getCarsByCustomer($customer->id);
				
				#Log
				$return["log"] = $this->getLogs($customer->id);
				
				#Service events
				$services = new \App\Http\Controllers\ServiceController;
				$return["serviceEvents"] = $services->getEvents([], $customer->id);
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getCustomerByEmail($email, $allDatas = true)
	{
		$id = $this->model->getCustomerByEmail($email, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByProgressCode($progressCode, $allDatas = true)
	{
		$id = $this->model->getCustomerByProgressCode($progressCode, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByToken($token, $allDatas = true)
	{
		$id = $this->model->getCustomerByToken($token, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByCode($code, $allDatas = true)
	{
		$id = $this->model->getCustomerByCode($code, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByHash($hash, $allDatas = true)
	{
		$id = $this->model->getCustomerByHash($hash, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByPhone($phone, $allDatas = true)
	{
		$id = $this->model->getCustomerByPhone($phone, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomerByMobile($mobile, $allDatas = true)
	{
		$id = $this->model->getCustomerByMobile($mobile, "id");
		if(!empty($id)) { $return = $this->getCustomer($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCustomersForList($search, $key = "id")
	{
		$return = [];
		if(!empty($search))
		{
			$fields = "id";
			if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
			$rows = $this->model->getCustomers($fields, $search);
			if(count($rows) > 0)
			{
				foreach($rows AS $i => $row) 
				{ 
					if(!empty($key)) { $keyHere = $row->$key; }
					else { $keyHere = $i; }
					$return[$keyHere] = $this->getCustomer($row->id, false); 
				}
			}
		}
		
		return $return;
	}
	
	#Get customer count
	public function countCustomers($dateFrom = NULL, $dateTo = NULL, $deleted = 0)
	{
		$row = $this->model->countCustomers($dateFrom, $dateTo, $deleted);
		return [
			"count" => $row[0]->count,
			"formatted" => number_format($row[0]->count, 0, 0, " ")." db",
		];
	}
	
	#Get customers from where
	public function customersFromWhere($allCustomers = NULL)
	{
		$returnRaw = [];
		if(empty($allCustomers)) { $allCustomers = $this->countCustomers(); }
		$rows = $this->model->customersFromWhere();
		foreach($rows AS $row) { $returnRaw[$row->fromWhere]++; }
		$returnRaw["N/A"] = $allCustomers["count"] - count($rows);
		arsort($returnRaw, SORT_NUMERIC);
		
		$return = [];
		foreach($returnRaw AS $name => $val)
		{
			$percentage = ($val * 100) / $allCustomers["count"];
			if($percentage < 1) { $percentageRound = 1; }
			else { $percentageRound = round($percentage); }
			$return[$name] = [
				"name" => $name,
				"count" => $val,
				"countFormatted" => number_format($val, 0, 0, " ")." db",
				"percentage" => $percentage,
				"percentageRound" => $percentageRound,
				"percentageFormatted" => number_format($percentage, 2, ",", " ")."%",
			];
		}
		return $return;
	}
	
	#Get points of customer
	public function getPointsByCustomer($customer)
	{
		$services = new \App\Service;
		$progressCode = $this->model->getCustomer($customer, "progressCode");
		
		$points1 = $services->getEventScoresByCustomer($customer, $progressCode);
		$points2 = $this->model->getServicePointsSumByCustomer($customer);
		
		$points = $points1 + $points2;	
		$pointsFormatted = number_format($points, 0, ",", " ");
		
		return [
			"points" => $points,
			"formatted" => $pointsFormatted,
			"out" => $pointsFormatted." pont",
		];
	}
	
	#Add point for a promotion view
	public function addPointForAction($customerID, $typeURL, $comment = NULL)
	{
		if(!empty($customerID))
		{
			$type = $this->model->getServicePointTypeByURL($typeURL);
			if(!empty($type) AND isset($type->id) AND !empty($type->id))
			{
				$existingPointsByType = $this->model->getServicePointsByType($type->id, "id");
				if($type->numberOfPointsAdd == -1 OR count($existingPointsByType) < $type->numberOfPointsAdd)
				{
					$params = [
						"customer" => $customerID,
						"date" => date("Y-m-d H:i:s"),
						"type" => $type->id,
						"pointChange" => $type->points,
						"comment" => $comment,
					];
					
					$this->model->myInsert($this->model->tables("servicePoints"), $params, $customerID);
				}
			}		
		}
	}
	
	#Get cars
	public function getCar($id, $allDatas = true)
	{
		$car = $this->model->getCar($id);
		if(!empty($car) AND isset($car->id) AND !empty($car->id))
		{
			$return = [];
			$return["data"] = $car;
			
			$return["id"] = $car->id;
			$return["customerID"] = $car->customer;
			$return["brand"] = $car->brand;
			$return["model"] = trim($car->name, "'");
			$return["regNumber"] = $car->regNumber;
			$return["bodyNumber"] = $car->bodyNumber;
			
			#Name
			$return["name"] = "";
			if(!empty($return["brand"])) { $return["name"] .= $return["brand"]; }
			if(!empty($return["model"])) 
			{ 
				if(!empty($return["name"])) { $return["name"] .= " "; }
				$return["name"] .= $return["model"]; 
			}
			
			#Date
			if(empty($car->date) OR $car->date == "0000-00-00" OR $car->date == "0000-00-00 00:00:00")
			{
				$return["date"] = "";
				$return["dateOut"] = "N/A";
			}
			else
			{
				$return["date"] = $car->date;
				$return["dateOut"] = strftime("%Y. %B %d.", strtotime($car->date));
			}
			
			if($allDatas)
			{
				// fuel: from cars_motors_fuels
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getCarByIdentifiers($customer, $regNumber, $bodyNumber, $allDatas = true)
	{
		$id = $this->model->getCarByIdentifiers($customer, $regNumber, $bodyNumber, "id", NULL);
		if(!empty($id)) { $return = $this->getCar($id, $allDatas); }
		else { $return = false; }
		return $return;
	}
	
	public function getCarsByCustomer($customer, $key = "id", $deleted = 0, $orderBy = "date DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getCarsByCustomer($customer, $fields, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getCar($row->id, false); 
			}
		}
		
		return $return;
	}
	
	public function getCarsByBrandsByCustomer($customer, $key = "id", $deleted = 0, $orderBy = "brand, date DESC")
	{
		$return = [
			"nissan" => [],
			"peugeot" => [],
			"kia" => [],
			"hyundai" => [],
			"citroen" => [],
			"infiniti" => [],
			"egyeb" => [],
		];
		$fields = "id, brand";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getCarsByCustomer($customer, $fields, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				$brand = mb_strtolower($row->brand);
				if(!isset($return[$brand])) { $brand = "egyeb"; }
				
				$keyHere = (!empty($key)) ? $row->$key : $i;
				$return[$brand][$keyHere] = $this->getCar($row->id, false); 
			}
		}
		
		return $return;
	}
	
	#Set token
	public function setToken($id = 0)
	{
		return self::setUniqueTokenDifferentChars(4, $allowToUse = ["numbers", "capitalLetters"], $this->model->tables("customers"), "token", [], $id);
	}
	
	#Set code
	public function setCode($date, $token)
	{
		if(empty($date) OR $date == "0000-00-00" OR $date = "0000-00-00 00:00:00") { $date = "2015-01-01"; }
		$dateHere = date("ym", strtotime($date));
		$codeBegin = $this->codeBefore.$dateHere."-";
		
		$rows = $this->model->select("SELECT code FROM ".$this->model->tables("customers")." WHERE code LIKE :code", ["code" => $codeBegin."%"]);
		$count = count($rows) + 1;
		$code = $codeBegin.sprintf("%04d", $count)."/".$token;
		
		return $code;
	}
	
	#Set hash
	public function setHash($user, $email, $code)
	{
		return sha1($this->hashSaltBefore."-".$user."-".$email."-".$code."-".$this->hashSaltAfter);
	}	
	
	#Get users for search
	public function getUserListForSearch()
	{
		$return = [];
		$userController = new \App\Http\Controllers\UserController;
		$users = $userController->getUsers(NULL, "id");
		foreach($this->model->getUsersForSearch() AS $row) { $return[$row->user] = $users["all"][$row->user]["name"]; }
		asort($return, SORT_STRING);
		return $return;
	}
	
	#Delete customer
	public function delCustomer($id, $reason)
	{
		$this->model->myDelete($this->model->tables("customers"), $id);
		$this->log("customers-del", $id, $id, ["text" => $reason]);
	}
	
	#Change customer
	public function changeCustomer($id, $datas, $customerData = NULL)
	{
		if(empty($customerData)) { $customerData = $this->model->getCustomer($id); }
		$return = $this->model->myUpdate($this->model->tables("customers"), $datas, $id);
		$logDatas = [
			"jsonOldDatas" => $customerData,
			"jsonNewDatas" => $datas,
		];
		$this->log("customers-change", $id, $id, $logDatas);
		
		return $return;
	}
	
	#New customer
	public function newCustomer($params, $systemTextOnSuccess = "")
	{
		if(!isset($params["email"]) OR empty($params["email"])) 
		{
			$return = false;
			$this->log("customers-new", NULL, NULL, ["systemText" => "Hiba: Nincs email!", "jsonNewDatas" => $params]);
		}
		else
		{
			$email = $params["email"] = trim($params["email"]);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$return = false;
				$this->log("customers-new", NULL, NULL, ["systemText" => "Hiba: Invalid email!", "jsonNewDatas" => $params]);
			}
			else
			{
				if((!isset($params["user"]) OR empty($params["user"])) AND defined("USERID")) { $params["user"] = USERID; }
				if(!isset($params["date"]) OR empty($params["date"])) { $params["date"] = date("Y-m-d H:i:s"); }
				$params["token"] = $this->setToken();
				$params["code"] = $this->setCode($params["date"], $params["token"]);
				$params["hash"] = $this->setHash($params["user"], $email, $params["code"]);
				
				if(isset($params["phone"]) AND !empty($params["phone"])) { $params["phone"] = $this->customersPhone($params["phone"]); }
				
				$return = $this->model->myInsert($this->model->tables("customers"), $params);
				$this->log("customers-new", $return, $return, ["systemText" => $systemTextOnSuccess, "jsonNewDatas" => $params]);
			}
		}
		
		return $return;
	}
	
	public function newCustomer2($params, $systemTextOnSuccess = "")
	{
		if((!isset($params["user"]) OR empty($params["user"])) AND defined("USERID")) { $params["user"] = USERID; }
		if(!isset($params["date"]) OR empty($params["date"])) { $params["date"] = date("Y-m-d H:i:s"); }
		$params["token"] = $this->setToken();
		$params["code"] = $this->setCode($params["date"], $params["token"]);
		$params["hash"] = $this->setHash($params["user"], $email, $params["code"]);
		
		if(isset($params["phone"]) AND !empty($params["phone"])) { $params["phone"] = $this->customersPhone($params["phone"]); }
		
		$return = $this->model->myInsert($this->model->tables("customers"), $params);
		$this->log("customers-new", $return, $return, ["systemText" => $systemTextOnSuccess, "jsonNewDatas" => $params]);
		return $return;
	}
	
	public function changeCustomerPhone($progressCode, $phone, $type)
	{
		return $this->model->myInsert($this->model->tables("phoneChanges"), ["date" => date("Y-m-d H:i:s"), "progressCode" => $progressCode, "phone" => $phone, "type" => $type]);
	}
	
	public function getCustomerPhoneChanges($dateFrom = NULL, $dateTo = NULL)
	{
		$query = "SELECT * FROM ".$this->model->tables("phoneChanges")." WHERE del = '0'";
		$params = [];
		
		if($dateFrom !== NULL)
		{
			$query .= " AND date >= :dateFrom";
			$params["dateFrom"] = $dateFrom;
		}	
		if($dateTo !== NULL)
		{
			$query .= " AND date <= :dateTo";
			$params["dateTo"] = $dateTo;
		}
		
		return $this->model->select($query, $params);		
	}
	
	public function customerPhoneChangesMonthlyReport()
	{
		$dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month"));
		$dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom));
		$rows = $this->getCustomerPhoneChanges($dateFrom, $dateTo);
		
		setlocale(LC_ALL, "HU_hu.UTF8");
		$dateOut = strftime("%Y. %B", strtotime($dateFrom));
		// include(DIR_VIEWS."emails/crm/customerPhoneChangesMonthlyReport.php"); 
	}
	
	#New car
	public function newCar($customerID, $params, $systemTextOnSuccess = "")
	{
		$customer = $this->getCustomer($customerID, false);
		if($customer === false)
		{
			$return = false;
			$this->log("customers-cars-new", NULL, NULL, ["systemText" => "Hiba: Nem létező ügyfél!", "json" => $customerID, "jsonNewDatas" => $params]);
		}
		else
		{
			$car = $this->getCarByIdentifiers($customerID, $params["regNumber"], $params["bodyNumber"], false);
			if($car !== false)
			{
				$return = false;
				$this->log("customers-cars-new", NULL, NULL, ["systemText" => "Hiba: Az autó már létezik!", "json" => $car["id"], "jsonNewDatas" => $params]);
			}
			else
			{
				$params["customer"] = $customerID;
				if((!isset($params["user"]) OR empty($params["user"])) AND defined("USERID")) { $params["user"] = USERID; }
				if(!isset($params["date"]) OR empty($params["date"])) { $params["date"] = date("Y-m-d H:i:s"); }
				
				$return = $this->model->myInsert($this->model->tables("cars"), $params);
				$this->log("customers-cars-new", $return, $return, ["systemText" => $systemTextOnSuccess, "jsonNewDatas" => $params]);
			}
		}
		
		return $return;
	}
	
	#Log
	public function log($typeName, $customer, $foreignKey, $datas = [])
	{
		return $this->model->log($typeName, $customer, $foreignKey, $datas);
	}
	
	#Log - get
	public function getLog($id)
	{
		$row = $this->model->getLog($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;		
			$return["id"] = $row->id;
		
			if(!empty($row->user))
			{
				$users = new \App\Http\Controllers\UserController;
				$return["user"] = $users->getUser($row->user);
				$return["userName"] = $return["user"]["name"];
			}
			else
			{
				$return["user"] = [];
				$return["userName"] = NULL;
			}
			
			$return["type"] = $type = $this->model->getLogType($row->type);
			$return["output"] = [
				"date" => ["name" => "Dátum", "val" => date("Y. m. d. H:i", strtotime($row->date))],
				"name" => ["name" => "Megnevezés", "val" => $type->namePublic],
				"user" => ["name" => "Munkatárs", "val" => $return["userName"]],
				"text" => ["name" => "Munkatárs üzenet", "val" => $row->text],
				"systemText" => ["name" => "Rendszerüzenet", "val" => $row->systemText],
				"foreignName" => ["name" => "Adat megnevezés", "val" => NULL],
			];
			
			switch($type->url)
			{;
				case "customers-import":
					$import = $this->model->getImportedData($row->foreignKey);
					if(!empty($import)) { $return["output"]["foreignName"]["val"] = "<strong><em>Tábla:</em></strong> ".$import->oldTable; }
					break;	
				case "customers-del":
					$return["output"]["text"]["val"] = "<strong><em>Indok:</em></strong> ".$return["output"]["text"]["val"];
					break;	
				case "customers-cars-new":
				case "customers-cars-edit":
				case "customers-cars-del":
					$car = $this->getCar($row->foreignKey, false);
					if($car !== false) { $return["output"]["foreignName"]["val"] = $car["name"]; }
					if($type->url == "customers-cars-del") { $return["output"]["text"]["val"] = "<strong><em>Indok:</em></strong> ".$return["output"]["text"]["val"]; }
					break;
				case "addresses-new":
				case "addresses-edit":
				case "addresses-del":
					break;	
				case "comments-new":
				case "comments-edit":
				case "comments-del":
					$comments = new \App\Http\Controllers\CommentController;
					$comment = $comments->getComment($row->foreignKey);
					if($comment !== false)
					{ 
						$output = [];
						if(!empty($return["output"]["text"]["val"])) { $output = $return["output"]["text"]["val"]; }
						if(!empty($comment["innerText"])) { $output[] = "<strong><em>Belső megjegyzés:</em></strong> ".$comment["innerText"]; }
						if(!empty($comment["publicText"])) { $output[] = "<strong><em>Nyilvános megjegyzés:</em></strong> ".$comment["innerText"]; }
						
						if(!empty($output)) { $return["output"]["text"]["val"] = implode("<br>", $output); }
						$return["output"]["foreignName"]["val"] = $comment["type"]["publicName"];
					}
					break;
				case "contacts-new":
				case "contacts-edit":
				case "contacts-del":
					if($type->url == "contacts-del") { $return["output"]["text"]["val"] = "<strong><em>Indok:</em></strong> ".$return["output"]["text"]["val"]; }
					break;
				case "services-event-new":
				case "services-event-edit":
				case "services-event-del":
					$services = new \App\Http\Controllers\ServiceController;
					$event = $services->model->getEvent($row->foreignKey);
					if($event !== false) { $return["output"]["foreignName"]["val"] = "<strong><em>Munkalapszám / Ajánlat sorszáma:</em></strong> ".$event->workSheetNumber; }
					if($type->url == "services-event-del") { $return["output"]["text"]["val"] = "<strong><em>Indok:</em></strong> ".$return["output"]["text"]["val"]; }
					break;
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getLogs($customer, $key = "id", $deleted = 0, $orderBy = "date DESC, id DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getLogsByCustomer($customer, $fields, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getLog($row->id); 
			}
		}
		
		return $return;
	}
	
	#JSON encode
	public function json($array)
	{
		return $this->model->json($array);
	}
	
	#Import datas --------------------------------------------------------------------------------------------------------------------------------------------------------
	public function importCustomers()
	{
		$return = [
			"ajanlatado_ugyfelek_NEW" => 0,
			"ajanlatado_ugyfelek_IMPORT_ONLY" => 0,
			"ajanlatado_ugyfelek_ALREADY_EXISTS" => 0,
			"ajanlatado_ugyfelek_INVALID" => 0,
			"ajanlatado_ugyfelek_INVALID_ARRAY" => [],
			"ajanlatado_kerdoiv_INVALID_ARRAY" => [],
			"ajanlatado_ajanlatok_INVALID_ARRAY" => [],
			"ajanlatado_ajanlatkerok_INVALID_ARRAY" => [],
		];
		
		$dbWebController = new \App\Http\Controllers\BaseController(["name" => MYSQL_CONNECTION_NAME_G2]);
		$dbWeb = $dbWebController->model;
		$db2016Controller = new \App\Http\Controllers\BaseController(["name" => MYSQL_CONNECTION_NAME_G]);
		$db2016 = $db2016Controller->model;
		$db = $this->model;
		
		#Ajánlatadó ügyfelek ---------------------------------------------------------------------------------------------------------------------------------------------
		$rows = $dbWeb->select("SELECT * FROM ajanlatado_ugyfelek WHERE allapot = '' AND ugyfel_email != '' AND ugyfel_email != '@' AND ugyfel_email != 'Nincs' AND ugyfel_email != 'x' ORDER BY ugyfel_email, id DESC");
		foreach($rows AS $i => $row)
		{
			$row->ugyfel_email = trim($row->ugyfel_email);
			if(!filter_var($row->ugyfel_email, FILTER_VALIDATE_EMAIL)) 
			{ 
				$return["ajanlatado_ugyfelek_INVALID"]++;
				$return["ajanlatado_ugyfelek_INVALID_ARRAY"][] = $row->id;
				continue; 
			}			
			$rows2 = $this->model->select("SELECT * FROM ".$this->model->tables("customers")." WHERE email = :email", ["email" => $row->ugyfel_email]);
			
			if(empty($row->user_eredeti)) { $row->user_eredeti = $row->user; }
			
			#Existing customer
			if(count($rows2) > 0)
			{
				$customerID = $rows2[0]->id;
				$rows3 = $this->model->select("SELECT * FROM ".$this->model->tables("importedDatas")." WHERE customer = :customer AND oldTable = 'ajanlatado_ugyfelek' AND oldID = :oldID", ["customer" => $customerID, "oldID" => $row->id]);
				if(count($rows3) == 0)
				{
					$return["ajanlatado_ugyfelek_IMPORT_ONLY"]++;
					
					$paramsImported = [
						"customer" => $customerID,
						"oldTable" => "ajanlatado_ugyfelek",
						"oldID" => $row->id,
						"fullName" => $row->ugyfel_nev,
						"originalPhone" => $row->ugyfel_tel,
						"zipCode" => $row->ugyfel_irsz,
						"address" => $row->ugyfel_cim,
						"rival" => $row->versenytars,
						"info" => $row->szemelyes_info,
						"creationMode" => $row->felvitel_modja,
						"currentCar" => $row->jelenlegi_auto,
						"fromWhere" => $row->honnan_jott,
						"json" => $this->json($row),
					];
					$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
					$logData = [
						"userImportant" => $row->user_eredeti,
						"dateImportant" => $row->datum,
					];
					$this->log("customers-import", $customerID, $importID, $logData);
					
					if(!empty($row->szemelyes_info))
					{
						$paramsComment = [
							"type" => 1,
							"foreignKey" => $customerID,
							"user" => $row->user_eredeti,
							"date" => $row->datum,
							"name" => "Személyes infó (Hobbi, család, ...)",
							"innerText" => $row->szemelyes_info,
						];
						$commentID = $this->model->myInsert("comments", $paramsComment);
						$logData = [
							"userImportant" => $row->user_eredeti,
							"dateImportant" => $row->datum,
							"systemText" => "Import - személyes infó",
						];
						$this->log("comments-new", $customerID, $commentID, $logData);
					}
				}
				else { $return["ajanlatado_ugyfelek_ALREADY_EXISTS"]++; }
			}
			#New customer
			else
			{
				$return["ajanlatado_ugyfelek_NEW"]++;
				$nameData = $this->importCustomersName($row->ugyfel_nev);
				$phone = $this->customersPhone($row->ugyfel_tel);
				
				#Params
				$token = $this->setToken();
				$code = $this->setCode($row->datum, $token);
				
				$paramsCustomer = [
					"user" => $row->user_eredeti,
					"date" => $row->datum,
					"email" => $row->ugyfel_email,
					"phone" => $phone,
					"token" => $token,
					"code" => $code,
					"hash" => $this->setHash($row->user_eredeti, $row->ugyfel_email, $code),
					"firstName" => $nameData["firstName"],
					"lastName" => $nameData["lastName"],
					"companyName" => $row->ugyfel_cegnev,
					"beforeName" => $nameData["beforeName"],
					"afterName" => "",
					"address" => NULL,
				];
				$customerID = $this->model->myInsert($this->model->tables("customers"), $paramsCustomer);
				
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
					"systemText" => "Import: ajanlatado_ugyfelek, ID: ".$row->id,
				];
				$this->log("customers-new", $customerID, $customerID, $logData);
				
				$paramsImported = [
					"customer" => $customerID,
					"oldTable" => "ajanlatado_ugyfelek",
					"oldID" => $row->id,
					"fullName" => $row->ugyfel_nev,
					"originalPhone" => $row->ugyfel_tel,
					"zipCode" => $row->ugyfel_irsz,
					"address" => $row->ugyfel_cim,
					"rival" => $row->versenytars,
					"info" => $row->szemelyes_info,
					"creationMode" => $row->felvitel_modja,
					"currentCar" => $row->jelenlegi_auto,
					"fromWhere" => $row->honnan_jott,
					"json" => $this->json($row),
				];
				$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
				];
				$this->log("customers-import", $customerID, $importID, $logData);
				
				if(!empty($row->szemelyes_info))
				{
					$paramsComment = [
						"type" => 1,
						"foreignKey" => $customerID,
						"user" => $row->user_eredeti,
						"date" => $row->datum,
						"name" => "Személyes infó (Hobbi, család, ...)",
						"innerText" => $row->szemelyes_info,
					];
					$commentID = $this->model->myInsert("comments", $paramsComment);
					$logData = [
						"userImportant" => $row->user_eredeti,
						"dateImportant" => $row->datum,
						"systemText" => "Import - személyes infó",
					];
					$this->log("comments-new", $customerID, $commentID, $logData);
				}
			}
		}
		#Ajánlatadó kérdőívek --------------------------------------------------------------------------------------------------------------------------------------------
		$rows = $dbWeb->select("SELECT * FROM ajanlatado_kerdoiv WHERE allapot = '' AND ugyfel_email != '' AND ugyfel_email != '@' AND ugyfel_email != 'Nincs' AND ugyfel_email != 'x' ORDER BY ugyfel_email, id DESC");
		foreach($rows AS $i => $row)
		{
			$row->ugyfel_email = trim($row->ugyfel_email);
			if(!filter_var($row->ugyfel_email, FILTER_VALIDATE_EMAIL)) 
			{ 
				$return["ajanlatado_ugyfelek_INVALID"]++;
				$return["ajanlatado_kerdoiv_INVALID_ARRAY"][] = $row->id;
				continue; 
			}			
			$rows2 = $this->model->select("SELECT * FROM ".$this->model->tables("customers")." WHERE email = :email", ["email" => $row->ugyfel_email]);
			
			if(empty($row->user_eredeti)) { $row->user_eredeti = $row->user; }
			
			#Existing customer
			if(count($rows2) > 0)
			{
				$customerID = $rows2[0]->id;
				$rows3 = $this->model->select("SELECT * FROM ".$this->model->tables("importedDatas")." WHERE customer = :customer AND oldTable = 'ajanlatado_kerdoiv' AND oldID = :oldID", ["customer" => $customerID, "oldID" => $row->id]);
				if(count($rows3) == 0)
				{
					$return["ajanlatado_ugyfelek_IMPORT_ONLY"]++;
					
					$paramsImported = [
						"customer" => $customerID,
						"oldTable" => "ajanlatado_kerdoiv",
						"oldID" => $row->id,
						"fullName" => $row->ugyfel_nev,
						"originalPhone" => $row->ugyfel_tel,
						"zipCode" => $row->ugyfel_irsz,
						"address" => $row->ugyfel_cim,
						"fromWhere" => $row->honnan,
						"json" => $this->json($row),
					];
					$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
					$logData = [
						"userImportant" => $row->user_eredeti,
						"dateImportant" => $row->datum,
					];
					$this->log("customers-import", $customerID, $importID, $logData);
				}
				else { $return["ajanlatado_ugyfelek_ALREADY_EXISTS"]++; }
			}
			#New customer
			else
			{
				$return["ajanlatado_ugyfelek_NEW"]++;
				$nameData = $this->importCustomersName($row->ugyfel_nev);
				$phone = $this->customersPhone($row->ugyfel_tel);
				
				#Params
				$token = $this->setToken();
				$code = $this->setCode($row->datum, $token);
				
				$paramsCustomer = [
					"user" => $row->user_eredeti,
					"date" => $row->datum,
					"email" => $row->ugyfel_email,
					"phone" => $phone,
					"token" => $token,
					"code" => $code,
					"hash" => $this->setHash($row->user_eredeti, $row->ugyfel_email, $code),
					"firstName" => $nameData["firstName"],
					"lastName" => $nameData["lastName"],
					"companyName" => $row->ugyfel_cegnev,
					"beforeName" => $nameData["beforeName"],
					"afterName" => "",
					"address" => NULL,
				];
				$customerID = $this->model->myInsert($this->model->tables("customers"), $paramsCustomer);
				
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
					"systemText" => "Import: ajanlatado_kerdoiv, ID: ".$row->id,
				];
				$this->log("customers-new", $customerID, $customerID, $logData);
				
				$paramsImported = [
					"customer" => $customerID,
					"oldTable" => "ajanlatado_kerdoiv",
					"oldID" => $row->id,
					"fullName" => $row->ugyfel_nev,
					"originalPhone" => $row->ugyfel_tel,
					"zipCode" => $row->ugyfel_irsz,
					"address" => $row->ugyfel_cim,
					"fromWhere" => $row->honnan,
					"json" => $this->json($row),
				];
				$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
				];
				$this->log("customers-import", $customerID, $importID, $logData);
			}
		}
		#Ajánlatadó ajánlatok --------------------------------------------------------------------------------------------------------------------------------------------
		$rows = $dbWeb->select("SELECT * FROM ajanlatado WHERE allapot = '' AND ugyfel_email != '' AND ugyfel_email != '@' AND ugyfel_email != 'Nincs' AND ugyfel_email != 'x' ORDER BY ugyfel_email, id DESC");
		foreach($rows AS $i => $row)
		{
			$row->ugyfel_email = trim($row->ugyfel_email);
			if(!filter_var($row->ugyfel_email, FILTER_VALIDATE_EMAIL)) 
			{ 
				$return["ajanlatado_ugyfelek_INVALID"]++;
				$return["ajanlatado_ajanlatok_INVALID_ARRAY"][] = $row->id;
				continue; 
			}			
			$rows2 = $this->model->select("SELECT * FROM ".$this->model->tables("customers")." WHERE email = :email", ["email" => $row->ugyfel_email]);
			
			if(empty($row->user_eredeti)) { $row->user_eredeti = $row->user; }
			
			#Existing customer
			if(count($rows2) > 0)
			{
				$customerID = $rows2[0]->id;
				$rows3 = $this->model->select("SELECT * FROM ".$this->model->tables("importedDatas")." WHERE customer = :customer AND oldTable = 'ajanlatado' AND oldID = :oldID", ["customer" => $customerID, "oldID" => $row->id]);
				if(count($rows3) == 0)
				{
					$return["ajanlatado_ugyfelek_IMPORT_ONLY"]++;
					
					$paramsImported = [
						"customer" => $customerID,
						"oldTable" => "ajanlatado",
						"oldID" => $row->id,
						"fullName" => $row->ugyfel_nev,
						"originalPhone" => $row->ugyfel_tel,
						"zipCode" => $row->ugyfel_irsz,
						"address" => $row->ugyfel_cim,
						"json" => $this->json($row),
					];
					$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
					$logData = [
						"userImportant" => $row->user_eredeti,
						"dateImportant" => $row->datum,
					];
					$this->log("customers-import", $customerID, $importID, $logData);
				}
				else { $return["ajanlatado_ugyfelek_ALREADY_EXISTS"]++; }
			}
			#New customer
			else
			{
				$return["ajanlatado_ugyfelek_NEW"]++;
				$nameData = $this->importCustomersName($row->ugyfel_nev);
				$phone = $this->customersPhone($row->ugyfel_tel);
				
				#Params
				$token = $this->setToken();
				$code = $this->setCode($row->datum, $token);
				
				$paramsCustomer = [
					"user" => $row->user_eredeti,
					"date" => $row->datum,
					"email" => $row->ugyfel_email,
					"phone" => $phone,
					"token" => $token,
					"code" => $code,
					"hash" => $this->setHash($row->user_eredeti, $row->ugyfel_email, $code),
					"firstName" => $nameData["firstName"],
					"lastName" => $nameData["lastName"],
					"companyName" => $row->ugyfel_cegnev,
					"beforeName" => $nameData["beforeName"],
					"afterName" => $row->ugyfel_nev_melle,
					"address" => NULL,
				];
				$customerID = $this->model->myInsert($this->model->tables("customers"), $paramsCustomer);
				
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
					"systemText" => "Import: ajanlatado, ID: ".$row->id,
				];
				$this->log("customers-new", $customerID, $customerID, $logData);
				
				$paramsImported = [
					"customer" => $customerID,
					"oldTable" => "ajanlatado",
					"oldID" => $row->id,
					"fullName" => $row->ugyfel_nev,
					"originalPhone" => $row->ugyfel_tel,
					"zipCode" => $row->ugyfel_irsz,
					"address" => $row->ugyfel_cim,
					"json" => $this->json($row),
				];
				$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
				$logData = [
					"userImportant" => $row->user_eredeti,
					"dateImportant" => $row->datum,
				];
				$this->log("customers-import", $customerID, $importID, $logData);
			}
		}
		#E-mail kapcsolatfelvételek --------------------------------------------------------------------------------------------------------------------------------------
		$rows = $db2016->select("SELECT * FROM emails_contactMessages WHERE del = '0' AND email != '' AND email != '@' AND email != 'Nincs' AND email != 'x' AND email NOT LIKE '%juizz%' ORDER BY email, id DESC");
		foreach($rows AS $i => $row)
		{
			$row->email = trim($row->email);
			if(!filter_var($row->email, FILTER_VALIDATE_EMAIL)) 
			{ 
				$return["ajanlatado_ugyfelek_INVALID"]++;
				$return["ajanlatado_ajanlatkerok_INVALID_ARRAY"][] = $row->id;
				continue; 
			}			
			$rows2 = $this->model->select("SELECT * FROM ".$this->model->tables("customers")." WHERE email = :email", ["email" => $row->email]);
			
			#Existing customer
			if(count($rows2) > 0)
			{
				$customerID = $rows2[0]->id;
				$rows3 = $this->model->select("SELECT * FROM ".$this->model->tables("importedDatas")." WHERE customer = :customer AND oldTable = 'emails_contactMessages' AND oldID = :oldID", ["customer" => $customerID, "oldID" => $row->id]);
				if(count($rows3) == 0)
				{
					$return["ajanlatado_ugyfelek_IMPORT_ONLY"]++;
					
					$paramsImported = [
						"customer" => $customerID,
						"oldTable" => "emails_contactMessages",
						"oldID" => $row->id,
						"fullName" => $row->name,
						"originalPhone" => $row->phone,
						"address" => $row->address,
						"fromWhere" => "Weboldal ajánlatkérés",
						"json" => $this->json($row),
					];
					$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
					$logData = [
						"userImportant" => NULL,
						"dateImportant" => $row->date,
					];
					$this->log("customers-import", $customerID, $importID, $logData);
				}
				else { $return["ajanlatado_ugyfelek_ALREADY_EXISTS"]++; }
			}
			#New customer
			else
			{
				$return["ajanlatado_ugyfelek_NEW"]++;
				$nameData = $this->importCustomersName($row->name);
				$phone = $this->customersPhone($row->phone);
				
				#Params
				$token = $this->setToken();
				$code = $this->setCode($row->date, $token);
				
				$paramsCustomer = [
					"date" => $row->date,
					"email" => $row->email,
					"phone" => $phone,
					"token" => $token,
					"code" => $code,
					"hash" => $this->setHash(NULL, $row->email, $code),
					"firstName" => $nameData["firstName"],
					"lastName" => $nameData["lastName"],
					"beforeName" => $nameData["beforeName"],
				];
				$customerID = $this->model->myInsert($this->model->tables("customers"), $paramsCustomer);
				
				$logData = [
					"userImportant" => NULL,
					"dateImportant" => $row->date,
					"systemText" => "Import: emails_contactMessages, ID: ".$row->id,
				];
				$this->log("customers-new", $customerID, $customerID, $logData);
				
				$paramsImported = [
					"customer" => $customerID,
					"oldTable" => "emails_contactMessages",
					"oldID" => $row->id,
					"fullName" => $row->name,
					"originalPhone" => $row->phone,
					"address" => $row->address,
					"fromWhere" => "Weboldal ajánlatkérés",
					"json" => $this->json($row),
				];
				$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
				$logData = [
					"userImportant" => NULL,
					"dateImportant" => $row->date,
				];
				$this->log("customers-import", $customerID, $importID, $logData);
			}
		}
		#Ajánlatkérők ----------------------------------------------------------------------------------------------------------------------------------------------------
		$allTablesHere = ["gablini", "hyundai", "kia", "peugeot"];
		foreach($allTablesHere AS $tableHere)
		{
			$tableName = "ajanlatkerok_".$tableHere;
			switch($tableHere)
			{
				case "gablini": $maxID = 699; break;
				case "kia": $maxID = 214; break;
				case "peugeot": $maxID = 442; break;
				default: $maxID = 0; break;
			}
			
			$rows = $dbWeb->select("SELECT * FROM ".$tableName." WHERE allapot = '' AND email != '' AND email != '@' AND email != 'Nincs' AND email != 'x' AND email NOT LIKE '%juizz%' AND id <= '{$maxID}' ORDER BY email, id DESC");
			foreach($rows AS $i => $row)
			{
				$row->email = trim($row->email);
				if(!filter_var($row->email, FILTER_VALIDATE_EMAIL)) 
				{ 
					$return["ajanlatado_ugyfelek_INVALID"]++;
					$return["ajanlatado_ajanlatkerok_INVALID_ARRAY"][] = $row->id;
					continue; 
				}			
				$rows2 = $this->model->select("SELECT * FROM ".$this->model->tables("customers")." WHERE email = :email", ["email" => $row->email]);
				
				$nameOriginal = $row->vnev;
				if(!empty($row->knev))
				{ 
					if(!empty($nameOriginal)) { $nameOriginal .= " "; }
					$nameOriginal .= $row->knev;
				}
				
				#Existing customer
				if(count($rows2) > 0)
				{
					$customerID = $rows2[0]->id;
					$rows3 = $this->model->select("SELECT * FROM ".$this->model->tables("importedDatas")." WHERE customer = :customer AND oldTable = '".$tableName."' AND oldID = :oldID", ["customer" => $customerID, "oldID" => $row->id]);
					if(count($rows3) == 0)
					{
						$return["ajanlatado_ugyfelek_IMPORT_ONLY"]++;
						
						$paramsImported = [
							"customer" => $customerID,
							"oldTable" => $tableName,
							"oldID" => $row->id,
							"fullName" => $nameOriginal,
							"originalPhone" => $row->telefon,
							"fromWhere" => "Weboldal ajánlatkérés",
							"json" => $this->json($row),
						];
						$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
						$logData = [
							"userImportant" => NULL,
							"dateImportant" => $row->datum,
						];
						$this->log("customers-import", $customerID, $importID, $logData);
					}
					else { $return["ajanlatado_ugyfelek_ALREADY_EXISTS"]++; }
				}
				#New customer
				else
				{
					$return["ajanlatado_ugyfelek_NEW"]++;
					$nameData = $this->importCustomersName($nameOriginal);
					$phone = $this->customersPhone($row->telefon);
					
					#Params
					$token = $this->setToken();
					$code = $this->setCode($row->datum, $token);
					
					$paramsCustomer = [
						"date" => $row->datum,
						"email" => $row->email,
						"phone" => $phone,
						"token" => $token,
						"code" => $code,
						"hash" => $this->setHash(NULL, $row->email, $code),
						"firstName" => $nameData["firstName"],
						"lastName" => $nameData["lastName"],
						"beforeName" => $nameData["beforeName"],
					];
					$customerID = $this->model->myInsert($this->model->tables("customers"), $paramsCustomer);
					
					$logData = [
						"userImportant" => NULL,
						"dateImportant" => $row->datum,
						"systemText" => "Import: ".$tableName.", ID: ".$row->id,
					];
					$this->log("customers-new", $customerID, $customerID, $logData);
					
					$paramsImported = [
						"customer" => $customerID,
						"oldTable" => $tableName,
						"oldID" => $row->id,
						"fullName" => $nameOriginal,
						"originalPhone" => $row->telefon,
						"fromWhere" => "Weboldal ajánlatkérés",
						"json" => $this->json($row),
					];
					$importID = $this->model->myInsert($this->model->tables("importedDatas"), $paramsImported);
					$logData = [
						"userImportant" => NULL,
						"dateImportant" => $row->datum,
					];
					$this->log("customers-import", $customerID, $importID, $logData);
				}
			}
		}
		
		return $return;
	}
	
	public function importCustomersName($name)
	{
		if(!empty($name))
		{
			#Before
			if(mb_substr($name, 0, 3, "utf-8") == "dr " OR mb_substr($name, 0, 3, "utf-8") == "DR " OR mb_substr($name, 0, 3, "utf-8") == "Dr " OR mb_substr($name, 0, 4, "utf-8") == "dr. " OR mb_substr($name, 0, 4, "utf-8") == "DR. " OR mb_substr($name, 0, 4, "utf-8") == "Dr. ") 
			{
				$name = explode(" ", $name);
				$beforeName = array_shift($name);
			}
			elseif(mb_substr($name, 0, 3, "utf-8") == "dr." OR mb_substr($name, 0, 3, "utf-8") == "DR." OR mb_substr($name, 0, 3, "utf-8") == "Dr.") 
			{
				$beforeName = mb_substr($name, 0, 3, "utf-8");
				$name = mb_substr($name, 3, NULL, "utf-8");
				$name = explode(" ", $name);
			}
			else
			{
				$name = explode(" ", $name);
				$beforeName = "";
			}		
			
			#First and last
			if(count($name) > 2)
			{
				$lastName = array_shift($name);
				$firstName = implode(" ", $name);
			}
			elseif(count($name) == 2)
			{
				$firstName = $name[1];
				$lastName = $name[0];
			}
			else
			{
				$firstName = $name[0];
				$lastName = "";
			}
		}
		else { $beforeName = $firstName = $lastName = ""; }
		
		$return = [
			"firstName" => $firstName,
			"lastName" => $lastName,
			"beforeName" => $beforeName,
		];
		
		return $return;
	}
	
	public function customersName($name)
	{
		if(!empty($name))
		{
			$nameItems = explode(" ", $name);
			$nameCheck = mb_strtolower($name, "utf-8");
			$nameCheckItems = explode(" ", $nameCheck);
			
			#Before list
			if(count($nameCheckItems) == 2)
			{
				$firstName = $nameItems[1];
				$lastName = $nameItems[0];
			}
			else
			{
				$befores = ["dr", "dr.", "ifj", "ifj.", "özv", "özv."];			
				if(in_array($nameCheckItems[0], $befores)) 
				{
					$firstName = $nameItems[2];
					$lastName = $nameItems[0]." ".$nameItems[1];
				}
				else
				{
					$firstName = $nameItems[1]." ".$nameItems[2];
					$lastName = $nameItems[0];
				}
			}
		}
		else { $firstName = $lastName = ""; }
		
		$return = [
			"firstName" => $firstName,
			"lastName" => $lastName,
		];
		
		return $return;
	}
	
	public function customersAddress($address)
	{
		if(!empty($address))
		{
			$items = explode(" ", $address);
			$check = mb_strtolower($address, "utf-8");
			$checkItems = explode(" ", $check);
			
			#Zip, city
			$zipCode = $items[0];
			$city = $items[1];
			
			unset($items[0]);
			unset($items[1]);
			unset($checkItems[0]);
			unset($checkItems[1]);
			
			#Street, number
			$streetTypes = ["árok", "átjáró", "dűlő", "dűlőút", "erdősor", "fasor", "forduló", "gát", "határsor", "határút", "kapu", "körönd", "körtér", "körút", "köz", "lakótelep", "lejáró", "lejtő", "lépcső", "liget", "mélyút", "orom", "ösvény", "park", "part", "pincesor", "rakpart", "sétány", "sikátor", "sor", "sugárút", "tér", "udvar", "út", "utca", "üdülőpart", "u", "u.", "krt", "krt."];
			$streetKey = false;
			foreach($streetTypes AS $streetType)
			{
				$streetKey = array_search($streetType, $checkItems);
				if($streetKey !== false) { break; }
			}
			
			if($streetKey === false)
			{
				$address = implode(" ", $items);
				$number = "";
			}
			else
			{ 
				$address = [];
				for($i = 2; $i <= $streetKey; $i++)
				{
					$address[] = $items[$i];
					unset($items[$i]);
				}
				$address = implode(" ", $address);
				$number = implode(" ", $items);
			}
		}
		else { $zipCode = $city = $address = $number = ""; }
		
		$return = [
			"zipCode" => $zipCode,
			"city" => $city,
			"address" => $address,
			"number" => $number,
		];
		
		return $return;
	}
	
	public function customersCategory($name)
	{	
		if(!empty($name))
		{
			#Name lists
			$maleNames = ["Aba", "Abád", "Abbás", "Abdiás", "Abdon", "Abdullah", "Ábel", "Abelárd", "Ábner", "Abod", "Abony", "Abos", "Abosa", "Ábrahám", "Ábrám", "Ábrán", "Ábris", "Absa", "Absolon", "Acél", "Achilles", "Achillesz", "Áchim", "Acsád", "Adalbert", "Ádám", "Adeboró", "Ádel", "Adelmár", "Áden", "Adeodát", "Ádér", "Ádin", "Adolár", "Adolf", "Ádomás", "Adonisz", "Adony", "Adorján", "Adrián", "Adriánó", "Agád", "Agamemnon", "Agapion", "Agaton", "Agenor", "Aggeus", "Agmánd", "Ágost", "Ágoston", "Ahillész", "Áhim", "Ahmed", "Airton", "Ajád", "Ajándok", "Ajtony", "Akács", "Akitó", "Ákos", "Aladár", "Aladdin", "Aladin", "Alajos", "Alán", "Alap", "Alárd", "Alarik", "Albert", "Albin", "Aldán", "Áldás", "Aldó", "Áldor", "Alek", "Alekszej", "Alen", "Alex", "Alexander", "Alfonz", "Alfréd", "Algernon", "Ali", "Almár", "Álmos", "Alpár", "Alperen ", "Álváró", "Alvián", "Alvin", "Amadé", "Amadeusz", "Amadó", "Amand", "Amar", "Amator", "Ambos", "Ambró", "Ambrus", "Ámer", "Amin", "Amir", "Ammar", "Ammon", "Ámon", "Amondó", "Ámor", "Ámos", "Anakin", "Anasztáz", "Anatol", "Andon", "Andor", "Andorás", "Andos", "András", "André", "Andrej", "Andzseló", "Angelus", "Angelusz", "Ángyán", "Anicét", "Aníziusz", "Antal", "Antigon", "Anton", "Antónió", "Antos", "Anzelm", "Ányos", "Apaj", "Apollinár", "Apolló", "Apor", "Apostol", "Apród", "Aracs", "Arad", "Aragorn", "Aram", "Aramisz", "Archibald", "Árden", "Ardó", "Arek", "Árész", "Arétász", "Arián", "Arie", "Ariel", "Arif", "Arion", "Árisz", "Arisztid", "Ariton", "Arkád", "Árkád", "Árkos", "Arlen", "Árlen", "Armand", "Armandó", "Ármin", "Arnó", "Arnold", "Arnót", "Áron", "Árpád", "Arszák", "Árszen", "Arszlán", "Artemon", "Artúr", "Arus", "Arvéd", "Arvid", "Árvin", "Arzén", "Aser", "Áser", "Ashraf", "Asur", "Aszáf", "Aszmet", "Aszter", "Asztor", "Asztrik", "Ata", "Atád", "Atakám", "Atanáz", "Atilla", "Atlasz", "Aton", "Atos", "Atosz", "Attila", "Auguszt", "Augusztusz", "Aurél", "Aurélián", "Avenár", "Avidan", "Avner", "Axel", "Azár", "Azarél", "Azim", "Aziz", "Azriel", "Bács", "Bacsó", "Bagamér", "Baján", "Bajka", "Bajnok", "Bájron", "Bakács", "Baksa", "Bakta", "Balabán", "Baladéva", "Balambér", "Balár", "Balaráma", "Balassa", "Balázs", "Baldó", "Baldvin", "Balián", "Bálint", "Balla", "Balló", "Balmaz", "Baltazár", "Bán", "Bandó", "Bánk", "Bános", "Barabás", "Baracs", "Barakony", "Barangó", "Bardó", "Barla", "Barlám", "Barna", "Barnabás", "Barót", "Bars", "Barsz", "Barta", "Bartal", "Bartó", "Barton", "Bartos", "Báruk", "Bató", "Bátony", "Bátor", "Batu", "Batus", "Bazil", "Bazsó", "Becse", "Bedecs", "Bedő", "Beke", "Bekény", "Bekes", "Békés", "Bekő", "Béla", "Belár", "Belián", "Belizár", "Ben", "Benája", "Benammi", "Bence", "Bende", "Bendegúz", "Bendit", "Bene", "Benedek", "Benedikt", "Benediktusz", "Benett", "Béni", "Beniel", "Benignusz", "Benitó", "Benjamin", "Benjámin", "Benke", "Benkő", "Bennó", "Benő", "Bentli", "Benvenútó", "Berárd", "Bérc", "Bercel", "Bere", "Berec", "Berend", "Berengár", "Berény", "Berger", "Berián", "Beriszló", "Berke", "Berkó", "Bernárd", "Bernát", "Bertalan", "Bertel", "Bertil", "Bertin", "Bertold", "Berton", "Bertram", "Berzsián", "Bese", "Beten", "Betlen", "Bihar", "Björn", "Boáz", "Bocsárd", "Bod", "Bodó", "Bódog", "Bodomér", "Bodony", "Bodor", "Bogát", "Bogdán", "Bogumil", "Bohumil", "Bohus", "Boján", "Bojta", "Bojtor", "Bojtorján", "Boldizsár", "Boleszláv", "Bolivár", "Bolyk", "Bonaventúra", "Bonca", "Bongor", "Bonifác", "Bónis", "Bónó", "Borbás", "Borisz", "Borocs", "Boromir", "Boroszló", "Bors", "Borsa", "Botár", "Botir", "Botond", "Botos", "Bottyán", "Bozsidár", "Bozsó", "Bökény", "Böngér", "Brájen", "Bránkó", "Brendon", "Brett", "Brúnó", "Brútusz", "Buda", "Bulcsú", "Buldus", "Burak", "Buzád", "Buzát", "Cádók", "Cecilián", "Celesztin", "Cézár", "Ciceró", "Ciprián", "Cirill", "Cirják", "Cirjék", "Círus", "Cvi", "Csaba", "Csák", "Csanád", "Csát", "Csatád", "Csatár", "Csató", "Csed", "Csege", "Csegő", "Csejte", "Cseke", "Csekő", "Csemen", "Csenger", "Csépán", "Csepel", "Cserjén", "Csete", "Csetény", "Csikó", "Csingiz", "Csobád", "Csobajd", "Csobán", "Csobánc", "Csolt", "Csoma", "Csombor", "Csomor", "Csongor", "Csörsz", "Dagobert", "Dagomér", "Dakó", "Dalia", "Dalibor", "Dalton", "Damáz", "Damián", "Damír", "Damján", "Damos", "Dániel", "Daniló", "Dános", "Dante", "Daren", "Dárió", "Dárius", "Dáriusz", "Dárkó", "Darnel", "Dasztin", "Dávid", "Décse", "Deján", "Dejte", "Delánó", "Deli", "Deme", "Demény", "Demeter", "Demjén", "Dénes", "Dengezik", "Denisz", "Denton", "Deodát", "Derek", "Derel", "Ders", "Derzs", "Dés", "Detre", "Dévald", "Devecser", "Dexter", "Dezmér", "Dezsér", "Dezsider", "Dezső", "Diaz", "Diegó", "Dienes", "Dijár", "Dilen", "Dilon", "Dimitri", "Dimitrij", "Dioméd", "Ditmár", "Doboka", "Dókus", "Doma", "Domán", "Dománd", "Domicián", "Dominik", "Domokos", "Domonkos", "Domos", "Donald", "Donát", "Donátó", "Donoven", "Dorel", "Dorián", "Dorion", "Dormán", "Dormánd", "Doroteó", "Dotán", "Dov", "Dózsa", "Döme", "Dömjén", "Dömös", "Dömötör", "Dragan", "Drasek", "Dukász", "Dusán", "Dzsamal", "Dzsárgál", "Dzsasztin", "Dzserald", "Dzsingiz", "Dzsínó", "Dzsúlió", "Ében", "Ecse", "Ede", "Edekon", "Edgár", "Edizon", "Edmond", "Edmund", "Edömér", "Edrik", "Eduán", "Eduárd", "Eduárdó", "Edvárd", "Edvin", "Efraim", "Efrém", "Egbert", "Egmont", "Egon", "Egyed", "Elád", "Eldon", "Elek", "Elemér", "Éli", "Elián", "Éliás", "Eliél", "Eliézer", "Elígiusz", "Elihú", "Eliot", "Elizeus", "Ellák", "Elmár", "Elmó", "Élon", "Előd", "Elton", "Elvir", "Emánuel", "Emeka", "Emil", "Emilián", "Emir", "Emmett", "Emőd", "Endre", "Éneás", "Engelbert", "Engelhard", "Enki", "Énok", "Énók", "Enrikó", "Enzó", "Erazmus", "Erdő", "Erhard", "Erik", "Erk", "Ernák", "Ernán", "Erneszt", "Ernesztó", "Ernik", "Ernő", "Ernye", "Erős", "Ervin", "Étán", "Ete", "Etele", "Etre", "Eugén", "Euszták", "Eutim", "Évald", "Evariszt", "Everard", "Evron", "Ezékiel", "Ezel", "Ezra", "Ézsaiás", "Fábián", "Fábió", "Fábiusz", "Fabó", "Fabríció", "Fabrícius", "Fabríciusz", "Fajsz", "Farkas", "Fausztusz", "Fedor", "Felicián", "Félix", "Ferdinánd", "Ferenc", "Fernandó", "Fernándó", "Fidél", "Filemon", "Filep", "Filibert", "Filip", "Filippó", "Filomén", "Fineás", "Flavián", "Flávió", "Fláviusz", "Florentin", "Flórián", "Flóris", "Fodor", "Folkus", "Fóris", "Fortunát", "Fortunátó", "Frank", "Franklin", "Frederik", "Frederíkó", "Frej", "Fremont", "Fridolin", "Frigyes", "Frodó", "Fülöp", "Gáber", "Gábor", "Gabos", "Gábos", "Gábri", "Gábriel", "Gál", "Gallusz", "Gálos", "Gandalf", "Gara", "Gáspár", "Gaszton", "Gaura", "Gazsó", "Gécsa", "Gede", "Gedeon", "Gedő", "Gejza", "Gellén", "Gellért", "Geminián", "Geraszim", "Gerd", "Geréb", "Gereben", "Gergely", "Gergő", "Gerhárd", "Gerjén", "Germán", "Gernot", "Gerold", "Gerő", "Gerváz", "Gerzson", "Géza", "Gibárt", "Gida", "Gilbert", "Gilgames", "Girót", "Gisó", "Gobert", "Godó", "Godvin", "Gópál", "Gorán", "Gorda", "Gordon", "Gorgiás", "Gotárd", "Gotfrid", "Gothárd", "Gotlíb", "Góvardhan", "Góvinda", "Göncöl", "Gracián", "Gregor", "Guidó", "Gujdó", "Gusztáv", "Günter", "Gyárfás", "Gyécsa", "Gyeke", "Gyenes", "Gyoma", "Györe", "György", "Györk", "Györke", "Győző", "Gyula", "Habib", "Hadi", "Hadriel", "Hadúr", "Háfiz", "Hájim", "Hakim", "Hamid", "Hamilkár", "Hamza", "Hananjá", "Hannibál", "Hannó", "Harald", "Hari", "Harka", "Harkány", "Harlám", "Harri", "Hárs", "Harsány", "Hartvig", "Harun", "Hasszán", "Havel", "Hejkó", "Hektor", "Heliodor", "Héliosz", "Helmond", "Helmut", "Hendri", "Henrik", "Herbert", "Herkules", "Herman", "Hermész", "Hermiás", "Hermiusz", "Herold", "Hetény", "Hiador", "Hilár", "Hiláriusz", "Hilmár", "Hippolit", "Hódos", "Holden", "Holló", "Honor", "Honorát", "Honorátusz", "Honóriusz", "Hont", "Horác", "Horáció", "Horáciusz", "Horka", "Hős", "Huba", "Hubert", "Hubertusz", "Hugó", "Humbert", "Hunor", "Hunyad", "Hümér", "Ibrahim", "Ibrány", "Ignác", "Igor", "Iláj", "Ilán", "Ilárion", "Ildár", "Ildefonz", "Ilián", "Iliász", "Illés", "Ilmár", "Imbert", "Immánuel", "Imre", "Ince", "Indár", "Ingmár", "Ipoly", "Iréneusz", "Irnik", "István", "Isua", "Iszam", "Itiel", "Iván", "Ivár", "Ivó", "Ivor", "Ixion", "Izaiás", "Izidor", "Izmael", "Izor", "Izrael", "Izsák", "Izsó", "Jábin", "Jácint", "Jáfet", "Jagelló", "Jáir", "Jakab", "Jákim", "Jákó", "Jákob", "Jakus", "Janek", "Jankó", "Janó", "János", "Január", "Jánusz", "Járed", "Járfás", "Jászon", "Jávor", "Jázon", "Jefte", "Jelek", "Jeles", "Jenő", "Jeremi", "Jeremiás", "Jermák", "Jernő", "Jeromos", "Jetró", "Joáb", "Joachim", "Joáhim", "Joakim", "Joákim", "Jób", "Joel", "Johanán", "Jónás", "Jonatán", "Jordán", "Jozafát", "József", "Józsiás", "Józsua", "Józsué", "Júda", "Jukundusz", "Julián", "Juliánusz", "Júliusz", "Junior", "Jusztin", "Jusztusz", "Juszuf", "Jutas", "Jutocsa", "Juvenál", "Kaba", "Kabos", "Kada", "Kadicsa", "Kadisa", "Kadocsa", "Kadosa", "Káin", "Kajetán", "Kájusz", "Kál", "Káldor", "Káleb", "Kálmán", "Kamil", "Kamill", "Kamilló", "Kán", "Kandid", "Kanut", "Kapisztrán", "Kaplon", "Kaplony", "Kapolcs", "Kara", "Karacs", "Karácson", "Karád", "Karcsa", "Kardos", "Karim", "Karion", "Kármán", "Károly", "Karsa", "Kartal", "Kászon", "Kasszián", "Kasztor", "Katapán", "Kazimír", "Kázmér", "Keán", "Keled", "Kelemen", "Kelemér", "Kelen", "Kelén", "Kelvin", "Kemal", "Kemál", "Kemecse", "Kemenes", "Kénán", "Kende", "Kenese", "Kenéz", "Kerecsen", "Kerény", "Keresztély", "Keresztes", "Kerim", "Kerubin", "Késav", "Keszi", "Kesző", "Ketel", "Keve", "Kevend", "Kevin", "Kian", "Kián", "Kien", "Kilán", "Kilény", "Kilián", "Kilit", "Kinizs", "Kirill", "Kiron", "Kirtan", "Kitán", "Klaudió", "Klaudiusz", "Klausz", "Kleofás", "Kleon", "Klétus", "Kocsárd", "Kofi", "Kolen", "Kolin", "Kolja", "Kolos", "Kolozs", "Kolta", "Kolumbán", "Kolumbusz", "Kond", "Konor", "Konrád", "Konstantin", "Kont", "Koppány", "Koridon", "Koriolán", "Kornél", "Kornéliusz", "Koron", "Korvin", "Kos", "Kósa", "Kovrat", "Kozma", "Kötöny", "Kövecs", "Kratosz", "Kresimir", "Krisna", "Kristóf", "Krisztián", "Krisztofer", "Krizantusz", "Krizosztom", "Krizsán", "Kund", "Kunó", "Kurd", "Kurszán", "Kurt", "Kusán", "Kustyán", "Kücsid", "Kürt", "Kventin", "Laborc", "Lád", "Ladomér", "Lajos", "Lamar", "Lambert", "Lándor", "Lantos", "Largó", "Larion", "László", "Laurent", "Lázár", "Lázó", "Leander", "Leandró", "Leándrosz", "Lehel", "Lél", "Lemmi", "Lénárd", "Lénárt", "Lennon", "Lennox", "Leó", "Leon", "Leonárd", "Leonárdó", "Leonid", "Leonidász", "Leontin", "Leopold", "Lestár", "Levéd", "Levedi", "Levente", "Lévi", "Levin", "Liam", "Liberátusz", "Libériusz", "Libor", "Liboriusz", "Libóriusz", "Liem", "Linasz", "Lionel", "Lior", "Lipót", "Lívió", "Liviusz", "Líviusz", "Lizander", "Lóci", "Loik", "Loránd", "Lóránd", "Loránt", "Lóránt", "Lorenzó", "Lotár", "Lőrinc", "Lucián", "Lúciusz", "Ludvig", "Lukács", "Lukréciusz", "Lüszien", "Madocsa", "Magnusz", "Magor", "Mahmud", "Majlát", "Majs", "Makabeus", "Makár", "Malakiás", "Malik", "Manaén", "Manassé", "Mandel", "Manfréd", "Manó", "Mánóah", "Manóhar", "Manszvét", "Manuel", "Mánuel", "Manzur", "Marcel", "Marcell", "Marcián", "Március", "Marcselló", "Marián", "Marinusz", "Márió", "Máriusz", "Márk", "Markel", "Markó", "Márkó", "Márkus", "Márkusz", "Marlon", "Marót", "Marsal", "Martin", "Marton", "Márton", "Martos", "Marvin", "Matán", "Máté", "Mateó", "Mateusz", "Mattenai", "Matteó", "Mátyás", "Mátyus", "Mauríció", "Mauró", "Max", "Maxim", "Maximilián", "Maximusz", "Mazen", "Medárd", "Medox", "Megyer", "Melchior", "Melhior", "Meliton", "Melkisédek", "Memnon", "Ménás", "Mendel", "Ménrót", "Menyhért", "Merlin", "Merse", "Metód", "Mihály", "Mika", "Mikán", "Mike", "Mikeás", "Mikes", "Mikió", "Miklós", "Mikó", "Miksa", "Milán", "Milen", "Milián", "Miló", "Milorád", "Milos", "Milován", "Miltiádész", "Milton", "Mirán", "Mirkó", "Miró", "Miron", "Misa", "Misel", "Miska", "Miske", "Misó", "Mizse", "Modesztusz", "Mohamed", "Móka", "Monor", "Monti", "Mór", "Morgan", "Móric", "Mózes", "Mózsi", "Músza", "Nabil", "Nader", "Nadim", "Naftali", "Nága", "Naim", "Nándor", "Napóleon", "Nárájan", "Narcisszusz", "Náron", "Naróttama", "Naszim", "Nasszer", "Nátán", "Nátánael", "Nataniel", "Navid", "Názár", "Nelzon", "Nemere", "Némó", "Nenád", "Nepomuk", "Nergál", "Néró", "Nesztor", "Nétus", "Nétusz", "Nevin", "Nikander", "Nikétás", "Nikodémus", "Nikodémusz", "Nikolasz", "Nikosz", "Nilsz", "Nimái", "Nimer", "Nimród", "Nitái", "Noam", "Nodin", "Noé", "Noel", "Nolan", "Nolen", "Nónusz", "Norbert", "Norik", "Norisz", "Norman", "Norton", "Norvard", "Nurszultán", "Nyék", "Nyikoláj", "Oberon", "Odiló", "Odin", "Odisszeusz", "Odó", "Ódor", "Oguz", "Oktáv", "Oktávián", "Olaf", "Olavi", "Oldamur", "Oleg", "Olivér", "Omár", "Ompoly", "Omri", "Ond", "Orbán", "Orbó", "Orda", "Oresztész", "Orfeusz", "Orion", "Orlandó", "Ormos", "Oros", "Oszkár", "Oszlár", "Osszián", "Oszvald", "Otelló", "Otmár", "Otniel", "Ottó", "Ottokár", "Oven", "Ovídiusz", "Ozirisz", "Ozmin", "Ozor", "Ozul", "Ozsvát", "Ödön", "Örkény", "Örkönd", "Örs", "Őze", "Özséb", "Pál", "Palkó", "Pálos", "Pamfil", "Pantaleon", "Páris", "Parker", "Paszkál", "Patony", "Patrícius", "Patrik", "Pázmán", "Pázmány", "Pelágiusz", "Pelbárt", "Pellegrin", "Pentele", "Penton", "Peregrin", "Periklész", "Perjámos", "Péter", "Pető", "Petres", "Petrik", "Petróniusz", "Petur", "Petúr", "Piládész", "Pió", "Piramusz", "Piusz", "Placid", "Platón", "Polidor", "Polikárp", "Pongor", "Pongrác", "Porfir", "Pósa", "Preszton", "Prímusz", "Principiusz", "Rabán", "Radamesz", "Radiszló", "Radó", "Radomér", "Rados", "Radován", "Radvány", "Rafael", "Ráfis", "Ragnar", "Rahim", "Raid", "Rajan", "Rájen", "Rajka", "Rajmond", "Rajmund", "Rajnald", "Ralf", "Rami", "Ramiel", "Ramirez", "Ramiró", "Ramiz", "Ramón", "Ramszesz", "Rápolt", "Rasid", "Rasszel", "Rátold", "Raúf", "Raul", "Razmus", "Rázsony", "Redmond", "Reginald", "Regő", "Regös", "Regős", "Reinhard", "Remig", "Rémus", "Rémusz", "Renátó", "Renátusz", "René", "Réva", "Rézmán", "Rezső", "Richárd", "Rihárd", "Rikárdó", "Rikó", "Rinaldó", "Róbert", "Robertó", "Robin", "Robinzon", "Rodel", "Roderik", "Rodion", "Rodrigó", "Rodzser", "Rokkó", "Rókus", "Roland", "Rolf", "Rollan", "Román", "Románó", "Romárió", "Romed", "Rómeó", "Romuald", "Romulusz", "Romvald", "Ron", "Ronald", "Ronaldó", "Ronel", "Ronen", "Roni", "Ronin", "Rua", "Ruben", "Rúben", "Rudolf", "Rúfusz", "Ruga", "Rupert", "Rurik", "Rusdi", "Ruszlán", "Rusztem", "Ruven", "Salamon", "Sámson", "Samu", "Sámuel", "Sándor", "Sarel", "Sas", "Saul", "Sebes", "Sebestyén", "Sebő", "Sebők", "Sejbán", "Sém", "Semjén", "Sét", "Simeon", "Simó", "Simon", "Sion", "Sjám", "Slomó", "Sobor", "Solt", "Solymár", "Sólyom", "Som", "Soma", "Spartakusz", "Srídhara", "Stefán", "Surány", "Surd", "Suriel", "Sükösd", "Szabin", "Szabolcs", "Szaján", "Szalárd", "Szaléz", "Szalók", "Szalvátor", "Szalviusz", "Szamir", "Szandró", "Szaniszló", "Szantiágó", "Szantínó", "Szatmár", "Szavér", "Szebáld", "Szebasztián", "Szecső", "Szedrik", "Szelemér", "Szelestény", "Szelim", "Szemere", "Szemír", "Szendrő", "Szente", "Szepes", "Szeráf", "Szerénd", "Szerénusz", "Szergej", "Szergiusz", "Szermond", "Szervác", "Szevér", "Szeveréd", "Szeverián", "Szeverin", "Szidor", "Szigfrid", "Szilamér", "Szilárd", "Szilas", "Szilvánusz", "Szilver", "Szilvér", "Szilveszter", "Szilvió", "Szilviusz", "Szindbád", "Szinisa", "Szíriusz", "Szixtusz", "Szofron", "Szókratész", "Szólát", "Szolón", "Szórád", "Szoren", "Szorin", "Szovárd", "Szovát", "Szörénd", "Szörény", "Szpartakusz", "Szulejmán", "Szven", "Tacitusz", "Taddeus", "Tádé", "Tadeusz", "Takin", "Taksony", "Talabér", "Talabor", "Talamér", "Táltos", "Tamás", "Tamerlán", "Tankréd", "Tar", "Taráz", "Tarcal", "Tarcsa", "Tardos", "Tarek", "Tarján", "Tárkány", "Tarsa", "Tarzíciusz", "Tas", "Tasnád", "Tasziló", "Tege", "Tegze", "Temes", "Tenger", "Teó", "Teobald", "Teodor", "Teofil", "Terenc", "Terestyén", "Tétény", "Tézeusz", "Tibád", "Tibériusz", "Tibold", "Tibor", "Tiborc", "Ticián", "Tihamér", "Timó", "Timon", "Timor", "Timót", "Timóteus", "Timóteusz", "Timoti", "Timur", "Timuzsin", "Tirzusz", "Titán", "Titusz", "Tivadar", "Tiván", "Tobán", "Tóbiás", "Tódor", "Toma", "Tomaj", "Tomor", "Tonuzóba", "Torda", "Tordas", "Tormás", "Torna", "Torontál", "Töhötöm", "Tömör", "Törtel", "Trajánusz", "Trevisz", "Trisztán", "Tullió", "Turul", "Tuzson", "Ubul", "Údó", "Ugocsa", "Ugod", "Ugor", "Ugron", "Ulászló", "Uldin", "Ulisszesz", "Ulpián", "Ulrik", "Umbertó", "Upor", "Urbán", "Uri", "Uriás", "Uriel", "Uros", "Uzon", "Uzor", "Üllő", "Vadim", "Vadony", "Vajda", "Vajk", "Vajta", "Valdemár", "Valdó", "Valentin", "Valentínó", "Valér", "Valérió", "Valid", "Valter", "Valton", "Várkony", "Varsány", "Vászoly", "Vata", "Vazul", "Vázsony", "Vejke", "Velek", "Vencel", "Vendel", "Vérbulcsú", "Vern", "Verner", "Vernon", "Versény", "Vértes", "Vetúriusz", "Viátor", "Vicián", "Vid", "Víd", "Vida", "Vidor", "Vidos", "Viggó", "Vikram", "Viktor", "Vilibald", "Vilmos", "Vince", "Vincent", "Virgil", "Virgíniusz", "Vitálij", "Vitális", "Vitályos", "Vitány", "Vitéz", "Vító", "Vitold", "Vitus", "Vladek", "Vladimír", "Vojta", "Volfram", "Voren", "Votan", "Vulkán", "Xavér", "Xerxész", "Zádor", "Zágon", "Zain", "Zajta", "Zajzon", "Zakari", "Zakariás", "Zakeus", "Zala", "Zalán", "Zaman", "Zámor", "Zarán", "Zaránd", "Zdenkó", "Zebadiás", "Zebulon", "Zeke", "Zeki", "Zekő", "Zéman", "Zénó", "Zente", "Zerénd", "Zerind", "Zéta", "Zete", "Zétény", "Zeusz", "Zev", "Ziad", "Zílió", "Zimány", "Zlatan", "Zoárd", "Zobor", "Zohár", "Zolta", "Zoltán", "Zólyom", "Zombor", "Zongor", "Zorán", "Zorba", "Zotmund", "Zovárd", "Zovát", "Zuárd", "Zuboly", "Zuriel", "Zsadán", "Zsadány", "Zserald", "Zsigmond", "Zsolt", "Zsombor", "Zsongor", "Zsubor", "Zsülien"];
			$femaleNames = ["Abélia", "Abiáta", "Abigél", "Ada", "Adala", "Adalberta", "Adalbertina", "Adalind", "Adaora", "Adél", "Adela", "Adéla", "Adelaida", "Adelgund", "Adelgunda", "Adelheid", "Adélia", "Adelin", "Adelina", "Adelinda", "Adema", "Adeodáta", "Adina", "Admira", "Adolfina", "Adonika", "Adóra", "Adria", "Adriána", "Adrianna", "Adrienn", "Adrienna", "Áfonya", "Áfra", "Afrodita", "Afrodité", "Afszana", "Agáta", "Ági", "Aglája", "Aglent", "Agnabella", "Agnella", "Ágnes", "Agnéta", "Ágosta", "Ágota", "Agrippína", "Aida", "Aina", "Ainó", "Aira", "Aisa", "Aisah", "Ajándék", "Ájlin", "Ajna", "Ajnácska", "Ajnó", "Ajra", "Ajsa", "Ajtonka", "Akaiéna", "Akilina", "Alamea", "Alaméa", "Alana", "Alba", "Alberta", "Albertin", "Albertina", "Albina", "Alda", "Áldáska", "Aldea", "Álea", "Aléna", "Aleszja", "Alesszia", "Alett", "Aletta", "Alexa", "Alexandra", "Alexandrin", "Alexandrina", "Alexia", "Alfonza", "Alfonzin", "Alfonzina", "Alfréda", "Alia", "Alica", "Alicia", "Alícia", "Alida", "Alina", "Alinda", "Alinka", "Alirán", "Alisa", "Aliszia", "Alissza", "Alitta", "Aliz", "Alíz", "Aliza", "Alizé", "Alízia", "Allegra", "Alma", "Almanda", "Almira", "Almiréna", "Almitra", "Aloé", "Alojzia", "Aloma", "Alóma", "Alvina", "Ama", "Amábel", "Amadea", "Amadil", "Amaja", "Amál", "Amália", "Amanda", "Amandina", "Amarant", "Amaranta", "Amarill", "Amarilla", "Amarillisz", "Amáta", "Amázia", "Ambrózia", "Amelda", "Ameli", "Amélia", "Amelita", "Ametiszt", "Amidala", "Amilla", "Amina", "Ámina", "Aminta", "Amira", "Amrita", "Anabel", "Anabell", "Anabella", "Anada", "Anaisz", "Anaszta", "Anasztázia", "Anatólia", "Ancilla", "Anda", "Andelin", "Andelina", "Andi", "Andrea", "Andreina", "Androméda", "Anélia", "Anelma", "Anéta", "Anett", "Anetta", "Ánfissza", "Angéla", "Angélia", "Angelika", "Angelina", "Angella", "Angyal", "Angyalka", "Ani", "Ania", "Anica", "Anicéta", "Aniella", "Anika", "Anikó", "Anilla", "Anima", "Anina", "Anissza", "Anita", "Anitra", "Anízia", "Ánizs", "Anka", "Ankisza", "Anna", "Annabel", "Annabell", "Annabella", "Annabori", "Annakarina", "Annakata", "Annaleila", "Annaléna", "Annalilla", "Annaliza", "Annalujza", "Annamari", "Annamária", "Annamira", "Annamíra", "Annaregina", "Annaréka", "Annarita", "Annaróza", "Annasára", "Annaszófia", "Annavera", "Annavirág", "Annelin", "Anni", "Annika", "Annunciáta", "Anriett", "Antea", "Antigoné", "Antoaneta", "Antoanett", "Antonella", "Antónia", "Antoniett", "Antonietta", "Anzelma", "Apol", "Apolka", "Apollinária", "Apollónia", "Aporka", "Appia", "Ápril", "Áprilka", "Arabella", "Aranka", "Arany", "Aranyka", "Aranyos", "Arenta", "Ari", "Aria", "Ariadna", "Ariadné", "Ariana", "Arianna", "Ariéla", "Ariella", "Arienn", "Arika", "Arikán", "Arina", "Arinka", "Arita", "Arlett", "Armanda", "Armandina", "Armella", "Armida", "Armilla", "Ármina", "Arna", "Árnika", "Arnolda", "Árpádina", "Artemisz", "Artemízia", "Artiana", "Árvácska", "Árven", "Arzénia", "Asma", "Aszpázia", "Asszunta", "Asztéria", "Asztrid", "Asztrida", "Atala", "Atalanta", "Atália", "Atanázia", "Atára", "Aténa", "Aténé", "Atika", "Atina", "Auguszta", "Augusztina", "Aura", "Aurea", "Aurélia", "Aurora", "Auróra", "Avarka", "Aviána", "Avitál", "Azálea", "Aziza", "Azra", "Azucséna", "Azura", "Azurea", "Babér", "Babett", "Babetta", "Babiána", "Babita", "Bagita", "Bahar", "Balbina", "Balda", "Balzsa", "Balzsam", "Bara", "Barack", "Baranka", "Barbara", "Barbarella", "Barbel", "Barbi", "Barka", "Bársony", "Bársonyka", "Baucisz", "Bazilia", "Bea", "Beáta", "Beatricse", "Beatrisz", "Beatrix", "Bebóra", "Béda", "Begónia", "Bejke", "Béke", "Bekka", "Belinda", "Bella", "Bellatrix", "Benáta", "Benedetta", "Benedikta", "Beneditta", "Benigna", "Benita", "Benjamina", "Bereniké", "Berfin", "Berill", "Berkenye", "Berna", "Bernadett", "Bernadetta", "Bernarda", "Bernardina", "Berta", "Bertilla", "Bertina", "Bertolda", "Béta", "Betsabé", "Betta", "Betti", "Bettina", "Biana", "Bianka", "Bibiána", "Bíbor", "Bíbora", "Bíboranna", "Bíborka", "Bié", "Birgit", "Biri", "Bítia", "Blandina", "Blanka", "Blazsena", "Blondina", "Bóbita", "Bodza", "Bogárka", "Bogáta", "Bogdána", "Bogi", "Boglár", "Boglárka", "Bojána", "Bolda", "Bolívia", "Boni", "Bonita", "Bonni", "Bora", "Bóra", "Borbála", "Borbolya", "Borcsa", "Bori", "Boris", "Boriska", "Borka", "Boróka", "Borostyán", "Borsika", "Bozsena", "Bozsóka", "Böbe", "Böske", "Brenda", "Brianna", "Briell", "Brigi", "Brigitta", "Britani", "Britni", "Britta", "Brohe", "Brunella", "Brunhilda", "Brünhild", "Búzavirág", "Cecília", "Cecilla", "Celerina", "Celeszta", "Celesztina", "Célia", "Celina", "Cettina", "Cezarin", "Cezarina", "Cicelle", "Ciklámen", "Cila", "Cili", "Cilka", "Cilla", "Cinderella", "Cinella", "Cinka", "Cinna", "Cinnia", "Cintia", "Cipora", "Cippóra", "Cipra", "Cipriána", "Ciprienn", "Cirel", "Cirilla", "Citta", "Csaga", "Csalka", "Csende", "Csendike", "Csenge", "Csengele", "Csente", "Cseperke", "Csepke", "Cseresznye", "Csermely", "Cserne", "Csilla", "Csillag", "Csillagvirág", "Csinszka", "Csinyere", "Csobánka", "Csobilla", "Csoboka", "Csönge", "Csöre", "Dafna", "Dafné", "Dála", "Dalanda", "Dália", "Dalida", "Dalla", "Dalma", "Damajanti", "Damarisz", "Damiána", "Damira", "Damjána", "Dana", "Danica", "Daniela", "Daniéla", "Daniella", "Danila", "Danka", "Danna", "Danuta", "Dára", "Daria", "Dária", "Darina", "Darinka", "Darla", "Davina", "Dea", "Debóra", "Delani", "Delfina", "Délia", "Délibáb", "Delila", "Delinda", "Delinke", "Della", "Demetria", "Déna", "Denerisz", "Denisza", "Denissza", "Deniza", "Deodáta", "Derien", "Detti", "Dettina", "Déva", "Devana", "Deveni", "Devínia", "Dezdemóna", "Dézi", "Dezideráta", "Dezidéria", "Dia", "Diana", "Diána", "Diandra", "Diké", "Dilara", "Dimitra", "Dina", "Dinara", "Dió", "Dionízia", "Diotíma", "Ditke", "Ditta", "Ditte", "Ditti", "Dolli", "Dolóresz", "Dolorita", "Doloróza", "Domicella", "Domiciána", "Dominika", "Dominka", "Domitilla", "Donáta", "Donatella", "Donna", "Dóra", "Dorabella", "Doren", "Doretta", "Dóri", "Dorina", "Dorinda", "Dorinka", "Dorisz", "Dorit", "Dorka", "Dorkás", "Dorkász", "Dormánka", "Dorotea", "Doroti", "Dorotina", "Dorottya", "Dotti", "Döníz", "Druzsiána", "Dulcinea", "Dusánka", "Dusenka", "Dusmáta", "Dzsamila", "Dzsámília", "Dzsamilla", "Dzsenet", "Dzsenifer", "Dzsenna", "Dzsenni", "Dzsesszika", "Dzsindzser", "Écska", "Éda", "Edda", "Edentina", "Edina", "Edit", "Edmunda", "Edna", "Édra", "Édua", "Eduárda", "Edvarda", "Edvina", "Effi", "Egberta", "Egres", "Eija", "Eisa", "Ekaterina", "Ékes", "Eleanor", "Elektra", "Elen", "Elena", "Eleni", "Elenor", "Eleonóra", "Életke", "Elfrida", "Elga", "Eliána", "Elif", "Elin", "Elina", "Elinor", "Eliora", "Eliz", "Elíz", "Eliza", "Elizabell", "Elizabet", "Elizin", "Elka", "Elke", "Ella", "Elli", "Ellina", "Elma", "Elmira", "Eloiz", "Elvira", "Elza", "Emanuéla", "Emerencia", "Emerika", "Emerina", "Emerita", "Emerka", "Emese", "Émi", "Emili", "Emilia", "Emília", "Emiliána", "Emina", "Emíra", "Emma", "Emmaróza", "Emmi", "Emő", "Emőke", "Enciána", "Ené", "Enéh", "Enese", "Enet", "Enid", "Enikő", "Enja", "Enna", "Eper", "Eperke", "Epifánia", "Eponin", "Era", "Erátó", "Erika", "Erina", "Ermelinda", "Erna", "Ernella", "Erneszta", "Ernesztin", "Ernesztina", "Ervina", "Ervínia", "Erzsébet", "Erzsi", "Esma", "Estella", "Estilla", "Eszmeralda", "Esztella", "Eszténa", "Eszter", "Eta", "Etel", "Etelka", "Etka", "Etta", "Etus", "Eudoxia", "Eufémia", "Eufrozina", "Eugénia", "Eulália", "Eunika", "Euniké", "Euridiké", "Európa", "Eutímia", "Euzébia", "Éva", "Evangelika", "Evangelina", "Evelin", "Evelina", "Evetke", "Évi", "Evica", "Evila", "Evina", "Evita", "Evódia", "Evolet", "Evolett", "Evra", "Fabiána", "Fabióla", "Fabrícia", "Fadett", "Fahéj", "Fáni", "Fanna", "Fanni", "Fantina", "Fárá", "Fáta", "Fatima", "Fatime", "Fausztina", "Fébé", "Febrónia", "Federika", "Fedóra", "Fédra", "Fehér", "Fehéra", "Fehére", "Fehérke", "Felda", "Felícia", "Feliciána", "Felicita", "Felicitás", "Felicitász", "Fenenna", "Feodóra", "Ferdinanda", "Fernanda", "Fiametta", "Fiamma", "Fidélia", "Filadelfia", "Filippa", "Fillisz", "Filoméla", "Filoména", "Filotea", "Fióna", "Fiorella", "Fioretta", "Firtos", "Flamina", "Flanna", "Flávia", "Flóra", "Floransz", "Florencia", "Florentina", "Florianna", "Florica", "Florina", "Florinda", "Fortuna", "Fortunáta", "Főbe", "Franciska", "Frangipáni", "Franka", "Franni", "Freja", "Frézia", "Frida", "Friderika", "Fruzsina", "Fulvia", "Fürtike", "Füzér", "Füzike", "Gabriella", "Gaia", "Gajána", "Gala", "Galamb", "Galatea", "Galina", "Gardénia", "Gauri", "Géda", "Géla", "Gemella", "Gemma", "Génia", "Genovéva", "Georgina", "Gerbera", "Gerda", "Gerle", "Gerti", "Gertrúd", "Gertrúdisz", "Gesztenye", "Gilberta", "Gilda", "Gina", "Giszmunda", "Gitka", "Gitta", "Giza", "Gizella", "Glenda", "Glória", "Godiva", "Golda", "Goldina", "Gordána", "Grácia", "Graciána", "Graciella", "Grész", "Gréta", "Gréte", "Gréti", "Grizelda", "Grizeldisz", "Gunda", "Gvenda", "Gvendolin", "Gyémánt", "Gyopár", "Gyopárka", "Gyömbér", "Gyöngy", "Gyöngyi", "Gyöngyike", "Gyöngyös", "Gyöngyvér", "Gyöngyvirág", "Györgyi", "Györgyike", "Hadassa", "Hágár", "Hajna", "Hajnácska", "Hajnal", "Hajnalka", "Hajni", "Halina", "Hana", "Hanga", "Hanife", "Hanka", "Hanna", "Hannabella", "Hannadóra", "Hannaliza", "Hannaróza", "Hanni", "Hargita", "Hargitta", "Harmat", "Harmatka", "Harriet", "Hatidzse", "Havadi", "Havaska", "Heba", "Hébé", "Héda", "Hedda", "Hédi", "Hedvig", "Heidi", "Héla", "Helen", "Helén", "Helena", "Heléna", "Helga", "Hélia", "Helka", "Hella", "Helza", "Heni", "Henna", "Henni", "Henriett", "Henrietta", "Héra", "Hermia", "Hermina", "Herta", "Heszna", "Hessza", "Hetti", "Hiacinta", "Hieronima", "Hilária", "Hilda", "Hildegárd", "Hildelita", "Hilka", "Hina", "Hippia", "Hippolita", "Hófehérke", "Holda", "Holli", "Honóra", "Honoráta", "Honória", "Honorina", "Honorka", "Horácia", "Hortenzia", "Hóvirág", "Huberta", "Hunóra", "Hunorka", "Hürrem", "Ibolya", "Ibolyka", "Ica", "Ida", "Idril", "Iduna", "Ifigénia", "Ignácia", "Ika", "Ila", "Ilang", "Ilda", "Ildi", "Ildikó", "Ilitia", "Ilka", "Illa", "Illangó", "Ilma", "Ilon", "Ilona", "Ilonka", "Ilus", "Iluska", "Ilze", "Imela", "Imelda", "Immakuláta", "Imodzsen", "Imogén", "Imola", "Inana", "India", "Indira", "Indra", "Inez", "Inge", "Ingeborg", "Ingrid", "Inka", "Innocencia", "Ippolita", "Irén", "Iréne", "Irénke", "Irina", "Iringó", "Írisz", "Irma", "Isméria", "Itala", "Ivána", "Ivett", "Ivetta", "Ivica", "Ividő", "Ivola", "Ivon", "Ivonn", "Iza", "Izabel", "Izabell", "Izabella", "Izaura", "Izidóra", "Ízisz", "Izméne", "Izolda", "Izóra", "Jácinta", "Jáde", "Jádránka", "Jadviga", "Jáel", "Jáél", "Jaffa", "Jáhel", "Jaina", "Jakobina", "Jamina", "Jamuná", "Jana", "Janina", "Janka", "Janna", "Jara", "Jára", "Járá", "Jarmila", "Jászira", "Jávorka", "Jázmin", "Jázmina", "Jelena", "Jelina", "Jelka", "Jella", "Jemima", "Jenni", "Jente", "Jerne", "Jeronima", "Jerta", "Jeszénia", "Jetta", "Jetti", "Jiszka", "Joana", "Johanka", "Johanna", "Johara", "Jokébed", "Jola", "Jolán", "Jolanda", "Jolánka", "Jolánta", "Joli", "Jolina", "Jonka", "Jordána", "Joszina", "Jována", "Jozefa", "Jozefin", "Jozefina", "Józsa", "Juci", "Judit", "Julcsi", "Juli", "Júlia", "Juliána", "Julianna", "Julietta", "Julilla", "Julinka", "Juliska", "Julitta", "Julka", "Júnó", "Jusztícia", "Jusztina", "Jusztínia", "Jutka", "Jutta", "Kádizsá", "Kála", "Kali", "Kalina", "Kalipszó", "Kalli", "Kalliopé", "Kalliszta", "Kalpavalli", "Kámea", "Kamélia", "Kamilia", "Kamília", "Kamilla", "Kandida", "Karen", "Karin", "Karina", "Kárisz", "Karitász", "Karla", "Karméla", "Karmelina", "Karmella", "Karmen", "Kármen", "Kármin", "Karola", "Karolin", "Karolina", "Karolt", "Kasszandra", "Kata", "Katalin", "Katalina", "Katarina", "Katerina", "Kati", "Katica", "Katinka", "Kató", "Katrin", "Katrina", "Kátya", "Kecia", "Kéla", "Kelda", "Kelli", "Kendra", "Kenza", "Kéra", "Kéren", "Kerka", "Kersztin", "Kerubina", "Késa", "Ketrin", "Kiara", "Kikinda", "Kiliána", "Kimberli", "Kincs", "Kincse", "Kincső", "Kinga", "Kira", "Kíra", "Kiri", "Kirilla", "Kirtana", "Kisanna", "Kisó", "Kitana", "Kitti", "Klára", "Klári", "Klarisz", "Klarissza", "Klaudetta", "Klaudia", "Klaudiána", "Klea", "Klélia", "Klemátisz", "Klemencia", "Klementin", "Klementina", "Kleó", "Kleopátra", "Klió", "Kloé", "Klotild", "Kolett", "Koletta", "Kolomba", "Kolombina", "Konkordia", "Konstancia", "Konstantina", "Kora", "Korália", "Korall", "Kordélia", "Korina", "Korinna", "Kornélia", "Koronilla", "Kozett", "Kozima", "Kökény", "Kreola", "Kreszcencia", "Krimhilda", "Kriszta", "Krisztabell", "Kriszti", "Krisztiána", "Krisztin", "Krisztina", "Krizanta", "Kunigunda", "Kunti", "Küllikki", "Küne", "Ladiszla", "Laksmi", "Lalita", "Lamberta", "Lana", "Lara", "Larcia", "Larett", "Larina", "Larissza", "Lartia", "Latífe", "Latiká", "Latinka", "Laura", "Laurencia", "Lauretta", "Lava", "Lavanda", "Lavínia", "Lea", "Leandra", "Léda", "Leila", "Lejka", "Lejla", "Lejle", "Lejre", "Léla", "Lélia", "Lelle", "Lemna", "Léna", "Lencsi", "Léni", "Lenita", "Lenka", "Lenke", "Lenóra", "Leona", "Leonarda", "Leonetta", "Leoni", "Leonor", "Leonóra", "Leontina", "Leopolda", "Leopoldina", "Leóti", "Leticia", "Letícia", "Letisa", "Létó", "Letta", "Letti", "Levendula", "Levina", "Levízia", "Lexa", "Lia", "Liana", "Liána", "Lianna", "Lícia", "Lida", "Lidi", "Lídia", "Lígia", "Lili", "Lilia", "Lilian", "Lilián", "Liliána", "Lilianna", "Lilibell", "Lilibet", "Lilien", "Liliom", "Liliróza", "Lilit", "Lilita", "Lilla", "Lilu", "Lina", "Linda", "Linéa", "Linett", "Linetta", "Linka", "Lionella", "Lióra", "Lira", "Líra", "Líria", "Lítia", "Lívia", "Liviána", "Livianna", "Liz", "Liza", "Lizabell", "Lizabella", "Lizandra", "Lizanka", "Lizanna", "Lizavéta", "Lizbett", "Lizett", "Lizetta", "Lizi", "Lízia", "Lizinka", "Ljuba", "Lóisz", "Lola", "Lolita", "Lolli", "Lona", "Lóna", "Lonci", "Lora", "Lorella", "Lorena", "Loréna", "Lorenza", "Lorett", "Loretta", "Lori", "Loriana", "Lorin", "Lorina", "Lorka", "Lorna", "Lotta", "Lotte", "Lotti", "Luca", "Lúcia", "Luciána", "Lucilla", "Lucinda", "Lúcsia", "Ludmilla", "Ludovika", "Lujza", "Lujzi", "Lukrécia", "Lula", "Lulu", "Luna", "Lupitá", "Lüszi", "Mábel", "Mabella", "Madita", "Madlen", "Madléna", "Magda", "Magdaléna", "Magdi", "Magdó", "Magdolna", "Magnólia", "Magorka", "Mahália", "Maida", "Maja", "Majoranna", "Makaréna", "Makrina", "Maléna", "Malina", "Malka", "Málna", "Malvin", "Malvina", "Mályva", "Maminti", "Manda", "Mandola", "Mandorla", "Mandula", "Manfréda", "Manga", "Manka", "Manna", "Manolita", "Manon", "Manszvéta", "Manuela", "Manuéla", "Manuella", "Manyi", "Mara", "Marcella", "Marcellina", "Maréza", "Margarét", "Margaréta", "Margaretta", "Margarita", "Margit", "Margita", "Margitta", "Margitvirág", "Margó", "Mari", "Mária", "Mariam", "Mariann", "Marianna", "Marica", "Mariella", "Mariett", "Marietta", "Marika", "Marilla", "Marina", "Marinella", "Marinetta", "Marinka", "Marion", "Marióra", "Mariska", "Marita", "Markéta", "Marléne", "Márta", "Márti", "Martina", "Martinella", "Martinka", "Masa", "Mása", "Matild", "Matilda", "Mátka", "Maura", "Maurícia", "Maxima", "Maximilla", "Mea", "Méda", "Medárda", "Médea", "Médeia", "Médi", "Medina", "Medó", "Medóra", "Megán", "Megara", "Meggi", "Mehdia", "Melani", "Melánia", "Melba", "Mélia", "Melina", "Melinda", "Melióra", "Melissza", "Melitta", "Melizand", "Melodi", "Melódia", "Meluzina", "Mena", "Mendi", "Menodóra", "Menta", "Méra", "Méráb", "Mercédesz", "Meredisz", "Meri", "Méri", "Meril", "Merilin", "Merima", "Messzua", "Méta", "Metella", "Métisz", "Metta", "Mia", "Miana", "Mici", "Miett", "Mietta", "Mihaéla", "Mikolt", "Mila", "Milágrosz", "Milana", "Milanna", "Milda", "Milena", "Miléna", "Miletta", "Milica", "Milka", "Milla", "Milli", "Mimi", "Mimma", "Mimóza", "Mina", "Mína", "Mínea", "Minerva", "Minetta", "Minka", "Minna", "Minóna", "Mira", "Míra", "Mirabel", "Mirabell", "Mirabella", "Mirana", "Miranda", "Mirandella", "Mirandola", "Mirandolína", "Mirázs", "Mirea", "Mirella", "Miri", "Miriam", "Miriám", "Mirijam", "Mirjam", "Mirjána", "Mirka", "Mirna", "Mirona", "Mirtilia", "Mirtill", "Misell", "Mita", "Miu", "Moána", "Modeszta", "Modesztina", "Mogyoró", "Móhini", "Moira", "Molli", "Mona", "Móna", "Móni", "Mónika", "Montika", "Morella", "Morgána", "Muriel", "Múzsa", "Nabiha", "Nadett", "Nadia", "Nadin", "Nadinka", "Nadira", "Nádja", "Nagyezsda", "Naira", "Nájiká", "Nalani", "Nalini", "Nalla", "Namika", "Nana", "Nanae", "Nanda", "Nandin", "Nandini", "Nandita", "Nanett", "Nanetta", "Náni", "Naomi", "Naómi", "Napsugár", "Napvirág", "Nara", "Nárcisz", "Narcissza", "Narin", "Narina", "Narmin", "Násfa", "Nasira", "Nasztázia", "Natali", "Natália", "Natánia", "Natasa", "Nauszika", "Nausziká", "Nauzika", "Nazira", "Nazli", "Nea", "Nedda", "Nefelejcs", "Nefeli", "Néla", "Nelda", "Néle", "Nélia", "Nella", "Nelli", "Nenszi", "Nerella", "Nerina", "Neste", "Neszrin", "Neszta", "Netta", "Netti", "Néva", "Névia", "Nia", "Niara", "Nika", "Niké", "Niki", "Nikodémia", "Nikol", "Nikola", "Nikolett", "Nikoletta", "Nikolina", "Nila", "Níla", "Nilla", "Nilüfer", "Nimfa", "Nina", "Ninell", "Ninett", "Ninetta", "Ninon", "Niobé", "Nirmalá", "Nisá", "Nissza", "Nita", "Niva", "Noa", "Noéla", "Noélia", "Noelin", "Noella", "Noémi", "Nola", "Nolina", "Nomin", "Nona", "Nóna", "Nonna", "Nóra", "Norberta", "Norella", "Norena", "Nóri", "Norina", "Norka", "Norma", "Nova", "Noveli", "Nurbanu", "Nyeste", "Nyina", "Oana", "Odett", "Odetta", "Odil", "Odília", "Ofélia", "Oktávia", "Olena", "Olga", "Oliána", "Olimpia", "Olina", "Olinda", "Oliva", "Olivia", "Olívia", "Opál", "Opika", "Ora", "Orália", "Orchidea", "Orgona", "Oriána", "Orsi", "Orsika", "Orsolya", "Oszvalda", "Otília", "Ottilia", "Oxána", "Ozora", "Örsi", "Örsike", "Örzse", "Őszike", "Őzike", "Pálma", "Palmira", "Paloma", "Palóma", "Pamela", "Paméla", "Pamína", "Pandora", "Pandóra", "Panka", "Panna", "Panni", "Parisza", "Paszkália", "Pasztorella", "Patricia", "Patrícia", "Paula", "Pauletta", "Paulin", "Paulina", "Peggi", "Pelágia", "Pénelopé", "Peónia", "Peregrina", "Perenna", "Perla", "Perpétua", "Petra", "Petrina", "Petronella", "Petrónia", "Petúnia", "Pilár", "Pintyőke", "Pipacs", "Pippa", "Pírea", "Piri", "Pirit", "Piros", "Piroska", "Placida", "Platina", "Polda", "Polett", "Pólika", "Polina", "Polixéna", "Polla", "Polli", "Pompília", "Pompónia", "Poppea", "Primula", "Priszcilla", "Prudencia", "Psziché", "Ráchel", "Rafaéla", "Rafaella", "Ragna", "Ráhel", "Rahima", "Rajmonda", "Rajmunda", "Rákhel", "Rákis", "Raktimá", "Ramina", "Ramira", "Ramóna", "Rana", "Rasdi", "Rea", "Rebeka", "Rege", "Regina", "Réka", "Relinda", "Rella", "Relli", "Remény", "Reményke", "Réna", "Renáta", "Réta", "Rezeda", "Rézi", "Ria", "Riana", "Rika", "Ríka", "Rikarda", "Ripszima", "Rita", "Ritta", "Riza", "Roberta", "Robertin", "Robertina", "Robina", "Robinetta", "Rodé", "Rodelinda", "Romána", "Romi", "Romina", "Rominett", "Romola", "Rona", "Ronett", "Ronetta", "Rovéna", "Roxán", "Roxána", "Róza", "Rózabella", "Rozál", "Rozali", "Rozália", "Rozalina", "Rozalinda", "Rózamari", "Rozamunda", "Rozanna", "Rozi", "Rózi", "Rozina", "Rozita", "Rozmarin", "Rozmaring", "Rozvita", "Rózsa", "Rózsi", "Röné", "Rubi", "Rubin", "Rubina", "Rubinka", "Rudolfina", "Rufina", "Rúna", "Ruperta", "Ruszalka", "Ruszlána", "Rut", "Ruti", "Ruzsinka", "Rüja", "Sába", "Sáfély", "Sáfrány", "Sakira", "Sakti", "Salomé", "Salóme", "Samuella", "Sanel", "Santál", "Santel", "Sára", "Sári", "Sarlott", "Sárlott", "Sárma", "Sármen", "Sarolt", "Sarolta", "Sáron", "Seherezádé", "Seila", "Sejda", "Sejla", "Sekina", "Seli", "Senon", "Sera", "Serihen", "Seron", "Sifra", "Sirá", "Siváni", "Skolasztika", "Stefánia", "Stefi", "Stella", "Sudár", "Sudárka", "Sugár", "Sugárka", "Suki", "Szabella", "Szabina", "Szabrina", "Szaffi", "Szafia", "Szafira", "Szalima", "Szalóme", "Szalvia", "Szamanta", "Szamia", "Szamira", "Szamóca", "Szandra", "Szangítá", "Szániva", "Szanna", "Szantána", "Szantina", "Szaraszvati", "Szaszkia", "Száva", "Szavanna", "Szavéta", "Szavina", "Szávitri", "Szébra", "Szeder", "Szederke", "Szedra", "Szegfű", "Szeléna", "Szelina", "Szelli", "Szellő", "Szellőke", "Szelma", "Szemerke", "Szemira", "Szemirámisz", "Szemőke", "Szende", "Szendi", "Szendike", "Szendile", "Szengláhák", "Szénia", "Szenta", "Szépa", "Szépe", "Szera", "Szerafina", "Szeréna", "Szerénke", "Szerkő", "Szevda", "Szeverina", "Szibell", "Szibella", "Szibill", "Szibilla", "Szidalisz", "Szidi", "Szidónia", "Sziéna", "Sziglind", "Szilárda", "Szille", "Szilva", "Szilvána", "Szilvesztra", "Szilvi", "Szilvia", "Szimin", "Szimka", "Szimóna", "Szimonett", "Szimonetta", "Szindi", "Színes", "Szinta", "Szintia", "Szira", "Szíra", "Szirén", "Sziri", "Sziringa", "Szirka", "Szirom", "Szironka", "Szítá", "Szivárvány", "Szixtin", "Szixtina", "Szkarlett", "Szkilla", "Szmirna", "Szofi", "Szofia", "Szófia", "Szofiana", "Szofianna", "Szofinett", "Szofrónia", "Szohéila", "Szolanzs", "Szona", "Szonja", "Szonóra", "Szoraja", "Szorina", "Szörénke", "Sztavrula", "Sztefani", "Sztella", "Sztilla", "Szulamit", "Szulejka", "Szulikó", "Szulita", "Szultána", "Szurina", "Szüntüké", "Szüvellő", "Szvetlana", "Szvetlána", "Tábita", "Tácia", "Taciána", "Taisza", "Tália", "Talita", "Tallula", "Támár", "Tamara", "Tamina", "Tamira", "Tanázia", "Tanita", "Tánya", "Tara", "Tarzícia", "Tatjána", "Tavasz", "Tavaszka", "Tea", "Tegza", "Tekla", "Telka", "Telma", "Témisz", "Téna", "Ténia", "Teobalda", "Teodolinda", "Teodóra", "Teodózia", "Teofánia", "Teofila", "Teónia", "Tercia", "Teréz", "Tereza", "Teréza", "Terézia", "Teri", "Terka", "Tertullia", "Tessza", "Tétisz", "Tia", "Tiana", "Tiána", "Tiara", "Tícia", "Ticiána", "Tifani", "Tikva", "Tilda", "Tília", "Tilla", "Tímea", "Timona", "Timótea", "Tina", "Tinetta", "Tinka", "Tira", "Tíra", "Tirca", "Tíria", "Tirza", "Titánia", "Titanilla", "Titti", "Tittína", "Tóbia", "Tomázia", "Tomazina", "Topáz", "Toszka", "Triniti", "Triszta", "Trixi", "Trudi", "Túlia", "Tulipán", "Tullia", "Tünde", "Tündér", "Tündi", "Tűzvirág", "Uljána", "Ulla", "Ulrika", "Úna", "Uránia", "Urbána", "Urszula", "Urzula", "Urzulina", "Uzonka", "Üdvöske", "Üne", "Ünige", "Ünőke", "Vadvirág", "Valencia", "Valentina", "Valéria", "Valetta", "Valina", "Vanda", "Vanessza", "Vanília", "Vanilla", "Varínia", "Vásti", "Vaszilia", "Vaszília", "Vasziliki", "Veca", "Véda", "Vélia", "Velmira", "Vendelina", "Vénusz", "Vera", "Veránka", "Verbéna", "Veréna", "Verita", "Verka", "Verna", "Veron", "Verona", "Veronika", "Veronka", "Veselke", "Veszna", "Veszta", "Véta", "Vetti", "Vetúria", "Via", "Vica", "Viki", "Vikta", "Viktória", "Viktorina", "Vilászini", "Vilhelma", "Vilhelmina", "Vilja", "Villő", "Vilma", "Vilora", "Vilté", "Vincencia", "Viola", "Violenta", "Violet", "Violett", "Violetta", "Viorika", "Virág", "Virgília", "Virgínia", "Víta", "Vitália", "Vitolda", "Vivi", "Viviána", "Vivianna", "Vivien", "Vivika", "Vrinda", "Vrindávani", "Vulfia", "Xavéria", "Xénia", "Xenodiké", "Zada", "Zádorka", "Zafira", "Zahara", "Zahira", "Zaina", "Zája", "Zakária", "Zalánka", "Záli", "Zamfira", "Zamina", "Zamira", "Zamíra", "Zana", "Zara", "Zarina", "Zazi", "Zdenka", "Zea", "Zéfi", "Zefira", "Zejna", "Zejnep", "Zéla", "Zelda", "Zélia", "Zelina", "Zelinda", "Zelinde", "Zeline", "Zelinke", "Zelka", "Zella", "Zelli", "Zelma", "Zelmira", "Zena", "Zenge", "Zengő", "Zenina", "Zenke", "Zenkő", "Zenna", "Zenóbia", "Zenta", "Zeta", "Zetta", "Zetti", "Zia", "Ziara", "Zília", "Zille", "Zimra", "Zina", "Zinaida", "Zinajda", "Zinka", "Zita", "Ziva", "Ziza", "Zizi", "Zlata", "Zoárda", "Zoé", "Zohara", "Zoja", "Zója", "Zolna", "Zoltána", "Zomilla", "Zonera", "Zonga", "Zora", "Zóra", "Zorina", "Zorinka", "Zorka", "Zosja", "Zöldike", "Zulejka", "Zsadányka", "Zsaklin", "Zsáklin", "Zsálya", "Zsana", "Zsanett", "Zsanin", "Zsanina", "Zsanka", "Zsanna", "Zsazsa", "Zsázsa", "Zsejke", "Zselinke", "Zsella", "Zselyke", "Zseni", "Zseraldin", "Zseráldin", "Zseraldina", "Zsinett", "Zsófi", "Zsófia", "Zsófianna", "Zsóka", "Zsolna", "Zsorzsett", "Zsuzsa", "Zsuzsanna", "Zsuzsánna", "Zsuzsi", "Zsuzska", "Zsüliett", "Zsüsztin"];
			$companyNames = [
				"egyéni vállalkozó", "ev.", "e.v.",
				"bt", "bt.", "betéti társaság",
				"kft", "kft.", "korlátolt felelősségű társaság",
				"kkt", "kkt.", "közkereseti társaság",
				"rt", "rt.", "részvénytársaság", "részvény társaság",
				"nyrt", "nyrt.", "nyilvánosan működő részvénytársaság", "nyilvánosan működő részvény társaság",
				"zrt", "zrt.", "zártkörűen működő részvénytársaság", "zártkörűen működő részvény társaság",
			];
			
			#Male, female?
			$category = "";
			$nameLower = mb_strtolower($name, "utf-8");
			$nameTitle = mb_convert_case($name, MB_CASE_TITLE, "utf-8");	
			
			foreach($companyNames AS $nameThis)
			{
				if(mb_strpos($nameLower, $nameThis) !== false) 
				{ 
					$category = "cég";
					break;
				}
			}
			if(empty($category))
			{
				foreach($maleNames AS $nameThis)
				{
					if(mb_strpos($nameTitle, $nameThis."né") !== false) 
					{ 
						$category = "nő";
						break;
					}
					elseif(mb_strpos($nameTitle, $nameThis) !== false) 
					{ 
						$category = "férfi";
						break;
					}
				}			
			}			
			if(empty($category))
			{
				foreach($femaleNames AS $nameThis)
				{
					if(mb_strpos($nameTitle, $nameThis) !== false) 
					{ 
						$category = "nő";
						break;
					}
				}
			}
		}
		else { $category = ""; }
		
		return $category;
	}
	
	public function customersPhone($phoneString)
	{
		#Wrong format
		$phoneString = (string)$phoneString;
		if(empty($phoneString) OR $phoneString == "x" OR $phoneString == "123456" OR $phoneString == "nincs" OR strpos($phoneString, "@") !== false) { $phone = NULL; }
		#Okay
		else
		{
			#Delete special chars
			$phone = str_replace([" ", "/", "-", ".", "(", ")"], ["", "", "", "", "", ""], $phoneString); 
			#If Hungarian phone --> add '+'
			if(mb_substr($phone, 0, 4, "utf-8") == "3620" OR mb_substr($phone, 0, 4, "utf-8") == "3630" OR mb_substr($phone, 0, 4, "utf-8") == "3670") { $phone = "+".$phone; }
			#Change 06 to +36
			elseif(mb_substr($phone, 0, 4, "utf-8") == "0620" OR mb_substr($phone, 0, 4, "utf-8") == "0630" OR mb_substr($phone, 0, 4, "utf-8") == "0670") 
			{ 
				$phone = mb_substr($phone, 1, NULL, "utf-8");
				$phone = "+3".$phone;
			}
			#Change +06 to +36
			elseif(mb_substr($phone, 0, 5, "utf-8") == "+0620" OR mb_substr($phone, 0, 5, "utf-8") == "+0630" OR mb_substr($phone, 0, 5, "utf-8") == "+0670") 
			{ 
				$phone = mb_substr($phone, 2, NULL, "utf-8");
				$phone = "+3".$phone;
			}
			
			#Our format
			if(mb_substr($phone, 0, 1, "utf-8") == "+")
			{
				$length = mb_strlen($phone, "utf-8");
				#Format: +36201234567
				if($length == 12) 
				{
					$phoneArray = str_split($phone);
					$phone = "(".$phoneArray[3].$phoneArray[4].") ".$phoneArray[5].$phoneArray[6].$phoneArray[7]." ".$phoneArray[8].$phoneArray[9].$phoneArray[10].$phoneArray[11];
				}
				#Format: +3624123456
				elseif($length == 11) 
				{
					$phoneArray = str_split($phone);
					$phone = "(".$phoneArray[3].$phoneArray[4].") ".$phoneArray[5].$phoneArray[6].$phoneArray[7]." ".$phoneArray[8].$phoneArray[9].$phoneArray[10];
				}
			}
		}
		
		return $phone;
	}
	
	public function getMarketingDisabledIdList()
	{
		$return = [];
		$list = $this->model->getMarketingDisabledList();
		foreach($list AS $progressCode)
		{
			if(!empty($progressCode))
			{
				$rows = $this->model->select("SELECT id FROM ".$this->model->tables("customers")." WHERE progressCode = :progressCode", ["progressCode" => $progressCode]);
				foreach($rows AS $row) { $return[] = $row->id; }
			}
		}
		
		return $return;
	}
	
	public function addMarketingDisabled($progressCode, $info = NULL)
	{
		$params = [
			"date" => date("Y-m-d H:i:s"), 
			"progressCode" => $progressCode, 
			"info" => $info
		];
		if(empty($progressCode)) { $params["del"] = 1; }
		
		$this->model->myInsert($this->model->tables("marketingDisabled"), $params);
	}
}
