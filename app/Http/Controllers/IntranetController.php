<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Intranet;

class IntranetController extends BaseController
{	
	public $model;
	public $info;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Intranet";
		$this->model = new \App\Intranet($connectionData);
	}
	
	#Create IT record
	public function itInsert($tableName, $data, $user)
	{
		$return = [
			"tableName" => $tableName,
			"data" => $data,
			"id" => NULL,
			"success" => true,
			"error" => NULL,
			"params" => NULL,
		];
		
		#Fields
		switch($tableName)
		{
			case "maintenance": 
				$fields = ["subject", "premise", "programs", "message"]; 
				break;
			case "report": 
				$fields = ["subject", "message"]; 
				break;
			case "toner": 
				$fields = ["premise", "printerID", "printer", "color", "quantity", "message"]; 
				break;
			default:
				$return["success"] = false;
				$return["error"] = "tableName";
				break;
		}
		
		#Params and query
		if($return["success"])
		{
			$params = [];			
			$params["date"] = date("Y-m-d H:i:s");
			$params["user"] = $user["data"]->id;
			$params["userName"] = $user["name"];
			$params["userEmail"] = $user["data"]->email;
			
			foreach($fields AS $field) { if(isset($data[$field])) { $params[$field] = $data[$field]; } }
			$return["id"] = $this->model->myInsert($this->model->tables($tableName), $params);
			$return["params"] = $params;
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("intranet-it", ["int1" => $return["id"], "vchar1" => $tableName, "text1" => $this->json($return)]);
		}
		
		#Return
		return $return;
	}
	
	#Get IT maintaince
	public function getMaintenance($id)
	{
		$row = $this->model->getMaintenance($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["user"] = $row->user;
			$return["subject"] = $row->subject;
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($row->date));
			$return["programs"] = nl2br($row->programs);
			$return["message"] = $row->message;
			
			$return["details"] = [
				"date" => ["name" => "Bejelentés ideje", "value" => $return["dateOut"]],
				"userName" => ["name" => "Bejelentő neve", "value" => $row->userName],
				"userEmail" => ["name" => "Bejelentő e-mail címe", "value" => $row->userEmail],
				"subject" => ["name" => "Tárgy", "value" => $row->subject],
				"premise" => ["name" => "Telephely", "value" => $row->premise],
				"programs" => ["name" => "Érintett programok", "value" => $return["programs"]],
			];
		}
		
		return $return;
	}
	
	#Get IT report
	public function getReport($id)
	{
		$row = $this->model->getReport($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["user"] = $row->user;
			$return["subject"] = $row->subject;
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($row->date));
			$return["message"] = $row->message;
			
			$return["details"] = [
				"date" => ["name" => "Bejelentés ideje", "value" => $return["dateOut"]],
				"userName" => ["name" => "Bejelentő neve", "value" => $row->userName],
				"userEmail" => ["name" => "Bejelentő e-mail címe", "value" => $row->userEmail],
				"subject" => ["name" => "Tárgy", "value" => $row->subject],
			];
		}
		
		return $return;
	}
	
	#Get IT toner
	public function getToner($id)
	{
		$row = $this->model->getToner($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["user"] = $row->user;
			$return["subject"] = $return["printer"] = $row->printer;
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($row->date));
			$return["quantity"] = $row->quantity;
			$return["quantityOut"] = number_format($row->quantity, 0, ",", " ")." db";
			$return["message"] = $row->message;
			
			$return["details"] = [
				"date" => ["name" => "Kérés beküldésének ideje", "value" => $return["dateOut"]],
				"userName" => ["name" => "Kérő neve", "value" => $row->userName],
				"userEmail" => ["name" => "Kérő e-mail címe", "value" => $row->userEmail],
				"premise" => ["name" => "Telephely", "value" => $row->premise],
				"printerID" => ["name" => "Nyomtató ID", "value" => $row->printerID],
				"printer" => ["name" => "Nyomtató megnevezése, típusa", "value" => $row->printer],
				"color" => ["name" => "Szín", "value" => $row->color],
				"quantity" => ["name" => "Darabszám", "value" => $return["quantityOut"]],
			];
		}
		
		return $return;
	}
	
	#Get printer
	public function getPrinter($id)
	{
		$row = $this->model->getPrinter($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["brand"] = $row->brand;
			$return["name"] = $row->name;
			$return["fullName"] = $return["brand"]." ".$return["name"];
			
			$return["toners"] = [];
			$return["toner1"] = [];
			$return["toner2"] = [];
			$return["toner3"] = [];
			$return["toner4"] = [];
			$return["toner5"] = [];
			
			for($i = 1; $i <= 5; $i++)
			{
				$nameField = "toner".$i."Name";
				$typeField = "toner".$i."Type";
				if(!empty($row->$nameField) OR !empty($row->$typeField))
				{
					$return["toner".$i] = [
						"name" => $row->$nameField,
						"type" => $row->$typeField,
						"fullName" => "",
					];
					if(!empty($row->$typeField)) { $return["toner".$i]["fullName"] .= $row->$typeField; }
					if(!empty($row->$nameField))
					{ 
						if(!empty($row->$typeField)) { $return["toner".$i]["fullName"] .= " [".$row->$nameField."]"; }
						else { $return["toner".$i]["fullName"] .= $row->$nameField; }
					}
					$return["toners"][] = $return["toner".$i];
				}
			}
		}
		
		return $return;
	}
	
	public function getPrinters()
	{
		$return = [];
		$rows = $this->model->getPrinters("id");		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getPrinter($row->id); }
		}
		
		return $return;
	}
	
	public function printerWork($work, $datas)
	{
		$return = [
			"work" => $work,
			"datas" => $datas,
		];
		$table = $this->model->tables("printers");
		$fields = ["brand", "name", "toner1Name", "toner1Type", "toner2Name", "toner2Type", "toner3Name", "toner3Type", "toner4Name", "toner4Type", "toner5Name", "toner5Type"];
		switch($work)
		{
			case "new":
				$return["params"] = [];
				foreach($fields AS $field) { $return["params"][$field] = $datas[$field]; }
				$return["id"] = $this->model->myInsert($table, $return["params"]);
				break;	
			case "edit":
				$return["params"] = [];
				foreach($fields AS $field) { $return["params"][$field] = $datas[$field]; }
				$return["id"] = $datas["id"];
				$this->model->myUpdate($table, $return["params"], $return["id"]);
				break;
			case "del":
				$return["id"] = $datas["id"];
				$this->model->myDelete($table, $datas["id"]);
				break;
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		return $return;
	}
	
	#Reservation time list
	public function reservationTimeList($interval = 30)
	{
		$return = [];
		$time = "00:00";
		while(true)
		{
			$return[] = $time;
			$time = date("H:i", strtotime($time." +".$interval." Minutes"));
			$timeHour = date("H", strtotime($time));
			if($time == "00:00") { break; }
		}
		
		return $return;
	}
	
	#Get Room Reservation
	public function getRoom($id)
	{
		$row = $this->model->getRoom($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["reservationType"] = "room";
			
			if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
			if(!empty($row->delUser)) { $return["delUser"] = $GLOBALS["users"]->getUser($row->delUser); }
			$return["delReason"] = $row->delReason;
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d.", strtotime($row->date));
			
			$return["user"] = $row->user;
			$return["premise"] = $row->premise;
			$return["subject"] = $row->subject;
			$return["message"] = $row->message;
			
			$return["label"] = $row->userName." - ".$row->subject;
			$return["chosenDate"] = $row->chosenDate;
			$return["timeFrom"] = $row->timeFrom;
			$return["timeTo"] = $row->timeTo;
			
			$return["dateFrom"] = $return["chosenDate"]." ".$return["timeFrom"].":00";
			$return["dateTo"] = $return["chosenDate"]." ".$return["timeTo"].":00";
			
			$return["chosenDateOut"] = date("Y. m. d.", strtotime($row->chosenDate));
			$return["timeOut"] = $row->timeFrom." - ".$row->timeTo;
			$return["dateFromOut"] = date("Y. m. d. H:i", strtotime($return["dateFrom"]));
			$return["dateToOut"] = date("Y. m. d. H:i", strtotime($return["dateTo"]));
			$return["dateTimeOut"] = $return["chosenDateOut"]." ".$return["timeOut"];
			
			$return["emailUserIDs"] = explode("|", $row->emailUsers);
			$return["emailUsers"] = [];
			if(!empty($row->emailUsers))
			{
				foreach($return["emailUserIDs"] AS $emailUserID) { $return["emailUsers"][$emailUserID] = $GLOBALS["users"]->getUser($emailUserID); }
			}
			
			#Details
			$return["details"] = [
				"premise" => ["name" => "Tárgyaló", "value" => $row->premise],
				"chosenDate" => ["name" => "Foglalt idősáv", "value" => $return["dateTimeOut"]],
				"userName" => ["name" => "Foglaló neve", "value" => $row->userName],
				"userEmail" => ["name" => "Foglaló e-mail címe", "value" => $row->userEmail],
				"date" => ["name" => "Foglalás beküldésének ideje", "value" => $return["dateOut"]],
				"printer" => ["name" => "Tárgy", "value" => $row->subject],
			];
			
			#Event
			$return["event"] = [
				"title" => $return["label"],
				"start" => $row->chosenDate."T".$row->timeFrom,
				"end" => $row->chosenDate."T".$row->timeTo,
				"allDay" => false,
				"className" => ["fullcalendar-event", "transition"],
				"id" => "fullcalendar-event-".$row->id,
			];			
			if($row->chosenDate == date("Y-m-d"))
			{
				$return["event"]["backgroundColor"] = "#2b9a57";
				$return["event"]["borderColor"] = "#25864c";
			}
		}
		
		return $return;
	}
	
	public function getRoomsForCalendar()
	{
		$return = [];
		$rows = $this->model->select("SELECT id FROM ".$this->model->tables("rooms")." WHERE del = '0' AND chosenDate >= :chosenDate ORDER BY premise, chosenDate, timeFrom", ["chosenDate" => date("Y")."-01-01"]);		
		if(!empty($rows))
		{
			foreach($rows AS $row)
			{
				$room = $this->getRoom($row->id);
				if($room !== false)
				{
					$return["all"][] = $room["event"];
					$return["premises"][$room["premise"]][] = $room["event"];
				}
			}
		}
		
		return $return;
	}
	
	#Get Car Reservation
	public function getCar($id)
	{
		$row = $this->model->getCar($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["reservationType"] = "car";
			
			if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
			if(!empty($row->delUser)) { $return["delUser"] = $GLOBALS["users"]->getUser($row->delUser); }
			$return["delReason"] = $row->delReason;
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d.", strtotime($row->date));
			
			$return["user"] = $row->user;
			$return["carCode"] = $row->carCode;
			$return["car"] = $row->car;
			$return["subject"] = $row->subject;
			$return["message"] = $row->message;
			
			$return["label"] = $row->userName." - ".$row->subject;
			$return["chosenDate"] = $row->chosenDate;
			$return["timeFrom"] = $row->timeFrom;
			$return["timeTo"] = $row->timeTo;
			
			$return["dateFrom"] = $return["chosenDate"]." ".$return["timeFrom"].":00";
			$return["dateTo"] = $return["chosenDate"]." ".$return["timeTo"].":00";
			
			$return["chosenDateOut"] = date("Y. m. d.", strtotime($row->chosenDate));
			$return["timeOut"] = $row->timeFrom." - ".$row->timeTo;
			$return["dateFromOut"] = date("Y. m. d. H:i", strtotime($return["dateFrom"]));
			$return["dateToOut"] = date("Y. m. d. H:i", strtotime($return["dateTo"]));
			$return["dateTimeOut"] = $return["chosenDateOut"]." ".$return["timeOut"];
			
			#Details
			$return["details"] = [
				"car" => ["name" => "Autó", "value" => $row->car],
				"carCode" => ["name" => "Autó HAHU kódja", "value" => $row->carCode],
				"chosenDate" => ["name" => "Foglalt idősáv", "value" => $return["dateTimeOut"]],
				"userName" => ["name" => "Foglaló neve", "value" => $row->userName],
				"userEmail" => ["name" => "Foglaló e-mail címe", "value" => $row->userEmail],
				"date" => ["name" => "Foglalás beküldésének ideje", "value" => $return["dateOut"]],
				"printer" => ["name" => "Tárgy", "value" => $row->subject],
			];
			
			#Event
			$return["event"] = [
				"title" => $return["label"],
				"start" => $row->chosenDate."T".$row->timeFrom,
				"end" => $row->chosenDate."T".$row->timeTo,
				"allDay" => false,
				"className" => ["fullcalendar-event", "transition"],
				"id" => "fullcalendar-event-".$row->id,
			];			
			if($row->chosenDate == date("Y-m-d"))
			{
				$return["event"]["backgroundColor"] = "#2b9a57";
				$return["event"]["borderColor"] = "#25864c";
			}
		}
		
		return $return;
	}
	
	public function getCarsForCalendar($carCode)
	{
		$return = [];
		$rows = $this->model->select("SELECT id FROM ".$this->model->tables("cars")." WHERE del = '0' AND carCode = :carCode AND chosenDate >= :chosenDate ORDER BY chosenDate, timeFrom", ["carCode" => $carCode, "chosenDate" => date("Y")."-01-01"]);		
		if(!empty($rows))
		{
			foreach($rows AS $row)
			{
				$car = $this->getCar($row->id);
				if($car !== false) { $return[] = $car["event"]; }
			}
		}
		
		return $return;
	}
	
	#Create Reservation record
	public function reservationInsert($tableName, $data, $user)
	{
		$return = [
			"tableName" => $tableName,
			"data" => $data,
			"id" => NULL,
			"success" => true,
			"errors" => [],
			"params" => NULL,
		];
		$fields = ["subject", "chosenDate", "timeFrom", "timeTo", "message"];
		$required = ["subject", "chosenDate", "timeFrom", "timeTo"];
		
		#Fields
		switch($tableName)
		{
			case "rooms": 
				$fields[] = "emailUsers";
				$data["emailUsers"] = implode("|", $data["emailUsers"]);
				$field = "premise";
				break;
			case "cars": 
				$field = "carCode"; 
				$fields[] = $required[] = "car";
				break;
			default:
				$return["success"] = false;
				$return["errors"][] = "tableName";
				break;
		}
		$fields[] = $required[] = $field; 
		
		#Required
		foreach($required AS $item)
		{
			if(!isset($data[$item]) OR empty($data[$item]))
			{
				$return["success"] = false;
				$return["errors"][] = "required";
				break;
			}
		}
		
		#From and to time
		if(isset($data["chosenDate"]) AND !empty($data["chosenDate"]) AND $data["chosenDate"] < date("Y-m-d"))
		{
			$return["success"] = false;
			$return["errors"][] = "date";
		}
		
		if(isset($data["timeFrom"]) AND !empty($data["timeFrom"]) AND isset($data["timeTo"]) AND !empty($data["timeTo"]))
		{
			$timeFrom = strtotime($data["timeFrom"]);
			$timeTo = strtotime($data["timeTo"]);
			if($timeFrom >= $timeTo)
			{
				$return["success"] = false;
				$return["errors"][] = "time";
			}
			
			#Is taken?
			if(isset($data[$field]) AND !empty($data[$field]) AND isset($data["chosenDate"]) AND !empty($data["chosenDate"]))
			{
				if($this->checkReservationTaken($tableName, $data[$field], $data["chosenDate"], $data["timeFrom"], $data["timeTo"]))
				{
					$return["success"] = false;
					$return["errors"][] = "taken";
				}
			}
		}
		
		#Params and query
		if($return["success"])
		{
			$params = [];			
			$params["date"] = date("Y-m-d H:i:s");
			$params["user"] = $user["data"]->id;
			$params["userName"] = $user["name"];
			$params["userEmail"] = $user["data"]->email;
			
			foreach($fields AS $item) { if(isset($data[$item])) { $params[$item] = $data[$item]; } }
			$return["id"] = $this->model->myInsert($this->model->tables($tableName), $params);
			$return["params"] = $params;
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$success = ($return["success"]) ? 1 : 0;
			$GLOBALS["log"]->log("intranet-reservation", ["int1" => $success, "int2" => $return["id"], "vchar1" => $tableName, "text1" => $this->json($return)]);
		}
		
		#Return
		return $return;
	}
	
	#Check if reservation is taken
	public function checkReservationTaken($tableName, $fieldVal, $chosenDate, $timeFrom, $timeTo)
	{
		$taken = false;
		
		switch($tableName)
		{
			case "rooms": $field = "premise"; break;
			case "cars": $field = "carCode"; break;
		}
		
		$params = [
			$field => $fieldVal,
			"chosenDate" => $chosenDate,
			"timeFrom1" => $timeFrom,
			"timeFrom2" => $timeTo,
			"timeFrom3" => $timeFrom,
			"timeTo1" => $timeFrom,
			"timeTo2" => $timeTo,
			"timeTo3" => $timeTo,
		];
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables($tableName)." WHERE del = '0' AND ".$field." = :".$field." AND chosenDate = :chosenDate AND (((timeFrom BETWEEN :timeFrom1 AND :timeFrom2) OR (timeTo BETWEEN :timeTo1 AND :timeTo2)) OR (timeFrom <= :timeFrom3 AND timeTo >= :timeTo3))", $params);
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				if($timeTo <= $row->timeFrom) {  }
				elseif($timeFrom >= $row->timeTo) {  }
				else 
				{ 
					$taken = true; 
					break;
				}
			}
		}
		
		return $taken;
	}
	
	#Delete room reservation
	public function delRoom($id, $reason)
	{
		if(isset($GLOBALS["user"])) { $this->model->myUpdate($this->model->tables("rooms"), ["delReason" => $reason, "delUser" => $GLOBALS["user"]["id"]], $id); }
		return $this->model->myDelete($this->model->tables("rooms"), $id);
	}
	
	#Delete car reservation
	public function delCar($id, $reason)
	{
		if(isset($GLOBALS["user"])) { $this->model->myUpdate($this->model->tables("cars"), ["delReason" => $reason, "delUser" => $GLOBALS["user"]["id"]], $id); }
		return $this->model->myDelete($this->model->tables("cars"), $id);
	}
	
	#Get program
	public function getProgram($id)
	{
		$row = $this->model->getProgram($id);		
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			#Basic
			$now = date("Y-m-d H:i:s");
			$return = [];
			$return["data"] = $row;
			
			#Users
			$return["users"] = $this->getProgramUsers($row->id);
			$return["usersCount"] = count($return["users"]);
			$return["usersCountOut"] = number_format($return["usersCount"], 0, ",", " ")." fő";
			$return["loggedInUser"] = NULL;
			$return["loggedInUserStatus"] = "Nincs feliratkozva";
			$return["loggedInUserStatusFormatted"] = "<em>".$return["loggedInUserStatus"]."</em>";
			if($return["usersCount"] > 0 AND isset($GLOBALS["user"]))
			{
				foreach($return["users"] AS $userHere) 
				{ 
					if($userHere["user"] == $GLOBALS["user"]["id"]) 
					{ 
						$return["loggedInUser"] = $userHere; 
						$return["loggedInUserStatus"] = $userHere["status"];
						$return["loggedInUserStatusFormatted"] = $userHere["statusFormatted"];
						break; 
					} 
				}
			}
			
			#Email users
			$return["emailUserIDs"] = explode("|", $row->emailUsers);
			$return["emailUsers"] = [];
			if(!empty($row->emailUsers))
			{
				if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
				foreach($return["emailUserIDs"] AS $emailUserID) { $return["emailUsers"][$emailUserID] = $GLOBALS["users"]->getUser($emailUserID); }
			}
			
			#Datas
			$return["id"] = $row->id;
			$return["subject"] = $return["name"] = $row->name;
			$return["details"] = $row->details;
			$return["user"] = $row->user;
			$return["userName"] = $row->userName;
			$return["userEmail"] = $row->userEmail;
			$return["message"] = $row->details;
			
			#Creation date and program date
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d.", strtotime($row->date));
			
			$return["programDate"] = $row->programDate;
			$return["programDateOut"] = date("Y. m. d. H:i", strtotime($row->programDate));
			$return["regText"] = "Regisztráció nem engedélyezett";
			$return["regStatus"] = "noRegistration";
			
			#Interest date
			$return["regDate"] = $return["interestDate"] = $row->interestDate;
			$return["regDateOut"] = $return["interestDateOut"] = date("Y. m. d. H:i", strtotime($row->interestDate));
			if(!empty($return["regDate"]) AND $now < $return["regDate"])
			{
				$return["regText"] = "Feliratkozás (Érdeklődés)";
				$return["regStatus"] = "interest";
			}
			
			#Apply date
			if(!empty($row->applyDate))
			{
				$return["regDate"] = $return["applyDate"] = $row->applyDate;
				$return["regDateOut"] = $return["applyDateOut"] = date("Y. m. d. H:i", strtotime($row->applyDate));
				if($now < $return["regDate"])
				{
					$return["regText"] = "Jelentkezés (Részvétel)";
					$return["regStatus"] = "apply";
				}
			}
			else { $return["applyDate"] = $return["applyDateOut"] = ""; }
			
			#Registration status
			if($now >= $return["regDate"]) 
			{ 
				$return["regText"] = "LEZÁRT";
				$return["regStatus"] = "closed";
			}
			
			#Active
			$return["active"] = ($now <= date("Y-m-d 23:59:59", strtotime($return["programDate"]))) ? true : false; 
				
			#Details
			$return["details"] = [
				"name" => ["name" => "Esemény megnevezése", "value" => $return["name"]],
				"programDate" => ["name" => "Esemény időpontja", "value" => $return["programDateOut"]],
				"userName" => ["name" => "Szervező neve", "value" => $return["userName"]],
				"userEmail" => ["name" => "Szervező e-mail címe", "value" => $return["userEmail"]],
				"status" => ["name" => "Jelentkezés fázisa", "value" => $return["regText"]],
			];
			if($row->apply) { $return["details"]["applyDate"] = ["name" => "Jelentkezés (Részvétel) határidő", "value" => $return["applyDateOut"]]; }
			else { $return["details"]["interestDate"] = ["name" => "Feliratkozás (Érdeklődés) határidő", "value" => $return["interestDateOut"]]; }
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getPrograms($user = NULL, $checkProgramDate = true, $key = "id", $deleted = 0, $orderBy = "programDate DESC")
	{
		$return = [];
		$rows = $this->model->getPrograms($user, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!$checkProgramDate OR ($row->programDate > date("Y-m-d H:i:s")))
				{
					$keyHere = (empty($key)) ? $i : $row->$key;
					$return[$keyHere] = $this->getProgram($row->id); 
				}
			}		
		}
		return $return;
	}
	
	#New program
	public function newProgram($data, $user)
	{
		$return = [
			"data" => $data,
			"id" => NULL,
			"params" => NULL,
			"type" => "insert",
		];
		$fields = ["name", "details", "programDate", "interestDate", "apply", "applyDate", "emailUsers"];
		
		#Params and query
		$params = [];			
		$params["date"] = date("Y-m-d H:i:s");
		$params["user"] = $user["data"]->id;
		$params["userName"] = $user["name"];
		$params["userEmail"] = $user["data"]->email;
		
		foreach($fields AS $item) { if(isset($data[$item])) { $params[$item] = $data[$item]; } }
		if(isset($params["emailUsers"])) { $params["emailUsers"] = implode("|", $params["emailUsers"]); }
		$return["id"] = $this->model->myInsert($this->model->tables("programs"), $params);
		$return["params"] = $params;
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("intranet-program", ["int1" => $return["id"], "text1" => $this->json($return)]);
		}
		
		#Return
		return $return;
	}
	
	#Edit program
	public function editProgram($data, $programID, $user)
	{
		$return = [
			"data" => $data,
			"id" => $programID,
			"params" => NULL,
			"type" => "update",
		];
		$fields = ["name", "details", "programDate", "interestDate", "apply", "applyDate"];
		
		#Params and query
		$params = [];					
		foreach($fields AS $item) { if(isset($data[$item])) { $params[$item] = $data[$item]; } }
		$this->model->myUpdate($this->model->tables("programs"), $params, $programID);
		$return["params"] = $params;
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("intranet-program", ["int1" => $return["id"], "text1" => $this->json($return)]);
		}
		
		#Return
		return $return;
	}
	
	#Get program
	public function getProgramUser($id)
	{
		$row = $this->model->getProgramUser($id);		
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			
			$return["id"] = $row->id;
			$return["user"] = $row->user;
			$return["userName"] = $row->userName;
			$return["userEmail"] = $row->userEmail;
			$return["program"] = $row->program;
			$return["interested"] = $row->interested;
			$return["applied"] = $row->applied;
			$return["interestDate"] = $return["interestDateOut"] = $return["applyDate"] = $return["applyDateOut"] = "";
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($row->date));
			
			if(!empty($row->interestDate))
			{
				$return["interestDate"] = $row->interestDate;
				$return["interestDateOut"] = date("Y. m. d. H:i", strtotime($row->interestDate));
			}
			
			if(!empty($row->applyDate))
			{
				$return["applyDate"] = $row->applyDate;
				$return["applyDateOut"] = date("Y. m. d. H:i", strtotime($row->applyDate));
			}
			
			if($return["applied"]) 
			{ 
				$return["status"] = "Résztvevő"; 
				$return["statusFormatted"] = "<strong class='color-success'>".$return["status"]."</strong>"; 
			}
			elseif($return["interested"]) 
			{ 
				$return["status"] = "Érdeklődő";
				$return["statusFormatted"] = "<strong>".$return["status"]."</strong>"; 
			}
			else 
			{ 
				$return["status"] = "N/A"; 
				$return["statusFormatted"] = "<em>".$return["status"]."</em>"; 
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function delProgram($id)
	{
		return $this->model->myDelete($this->model->tables("programs"), $id);
	}
	
	public function getProgramUsers($program, $key = "id", $deleted = 0, $orderBy = "userName")
	{
		$return = [];
		$rows = $this->model->getProgramUsers($program, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				$keyHere = (empty($key)) ? $i : $row->$key;
				$return[$keyHere] = $this->getProgramUser($row->id); 
			}		
		}
		return $return;
	}
	
	public function programUserStatus($type, $programID, $row)
	{
		$table = $this->model->tables("programUsers");
		$now = date("Y-m-d H:i:s");
		$return = NULL;
		switch($type)
		{
			#Apply
			case "apply":
				if($row === NULL)
				{
					$params = [
						"date" => $now,
						"user" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["id"] : NULL,
						"userName" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["name"] : NULL,
						"userEmail" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["email"] : NULL,
						"program" => $programID,
						"applied" => 1,
						"applyDate" => $now,
					];
					$return = $this->model->myInsert($table, $params); 
				}
				else
				{
					$params = [
						"applied" => 1,
						"applyDate" => $now,
					];
					$return = $this->model->myUpdate($table, $params, $row["id"]); 
				}
				break;
			#Insert
			case "interest":
				if($row === NULL) 
				{ 
					$params = [
						"date" => $now,
						"user" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["id"] : NULL,
						"userName" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["name"] : NULL,
						"userEmail" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["email"] : NULL,
						"program" => $programID,
						"interested" => 1,
						"interestDate" => $now,
					];
					$return = $this->model->myInsert($table, $params); 
				}
				break;
			case "unapply":
				if($row !== NULL) { $return = $this->model->myDelete($table, $row["id"]); }
				break;	
		}
		
		return $return;
	}
	
	#Get actuality link
	public function getActualityLink($id)
	{
		$row = $this->model->getActualityLink($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["user"] = $row->user;
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($return["date"]));
			$return["orderNumber"] = $row->orderNumber;
			
			$return["link"] = $return["linkOut"] = $row->link;
			$return["file"] = $return["pic"] = [];
			$return["picSrc"] = DIR_PUBLIC_WEB."pics/intranet/nincs-kep-aktualis-link.jpg";
			if(!empty($row->file) OR !empty($row->pic))
			{
				$files = new \App\Http\Controllers\FileController;
				if(!empty($row->file))
				{
					$file = $files->getFile($row->file);
					if(!empty($file["id"]) AND file_exists($file["path"]["inner"]))
					{
						$return["file"] = $file;
						$return["linkOut"] = $file["path"]["web"];
					}
				}
				if(!empty($row->pic))
				{
					$pic = $files->getFile($row->pic);
					if(!empty($pic["id"]) AND file_exists($pic["path"]["inner"]))
					{
						$return["pic"] = $pic;
						$return["picSrc"] = $pic["path"]["web"];
					}
				}
			}
			
			if(empty($return["linkOut"])) { $return["linkOut"] = "#"; }
		}
		
		return $return;
	}
	
	public function getActualityLinks()
	{
		$return = [];
		$rows = $this->model->getActualityLinks("id");		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getActualityLink($row->id); }
		}
		
		return $return;
	}
	
	public function actualityLinkWork($work, $datas)
	{
		$return = [
			"work" => $work,
			"datas" => $datas,
		];
		$table = $this->model->tables("links");
		switch($work)
		{
			case "new":
			case "edit":
				$return["params"] = [
					"name" => $datas["name"],
					"link" => $datas["link"],
				];
				
				if($work == "new")
				{
					$return["params"]["date"] = date("Y-m-d H:i:s");
					$return["params"]["user"] = USERID;
					$return["params"]["orderNumber"] = $this->model->reOrder($table);
					$return["id"] = $this->model->myInsert($table, $return["params"]);
				}
				else
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, $return["params"], $return["id"]);
				}
				
				$files = new \App\Http\Controllers\FileController;
				$fileReturn = $files->upload("pic", "intranet-links-pic", $return["id"]);
				if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($table, ["pic" => $fileReturn[0]["fileID"]], $return["id"]); }
				
				$fileReturn = $files->upload("file", "intranet-links-file", $return["id"]);
				if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($table, ["file" => $fileReturn[0]["fileID"]], $return["id"]); }
				break;
			case "order":
				$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table);
				break;
			case "del":
				$return["id"] = $datas["id"];
				$this->model->myDelete($table, $datas["id"]);
				break;
			case "file-del":
				$return["id"] = $datas["id"];
				$this->model->myUpdate($table, [$datas["field"] => NULL], $return["id"]);
				break;
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		return $return;
	}
	
	#Get document
	public function getDocument($id, $allData = true)
	{
		$row = $this->model->getDocument($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			
			$return["level"] = $row->level;
			$return["categoryID"] = $row->category;
			
			$return["userID"] = $row->user;
			$return["orderNumber"] = $row->orderNumber;
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($return["date"]));
			
			$file = new \App\Http\Controllers\FileController;
			$return["documents"] = $file->getFileList("intranet-documents", $return["id"]);			

			if($allData)
			{			
				if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
				$return["user"] = (!empty($return["userID"])) ? $GLOBALS["users"]->getUser($return["userID"]) : false;
				$return["category"] = (!empty($return["categoryID"])) ? $this->getDocument($return["categoryID"]) : false;
				
				$return["parentCategories"] = [];
				if($return["level"] == 2) { $return["parentCategories"][] = $return["category"]["name"]; }
				elseif($return["level"] == 3) 
				{ 
					$return["parentCategories"][] = $return["category"]["name"];
					$return["parentCategories"][] = $return["category"]["category"]["name"]; 
				}
			}
		}
		
		return $return;
	}
	
	public function getDocuments($category = NULL, $level = NULL, $deleted = 0, $orderBy = "orderNumber")
	{
		$return = [];
		$rows = $this->model->getDocuments($level, $category, "id", $deleted, $orderBy);		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getDocument($row->id, false); }
		}
		
		return $return;
	}
	
	public function documentWork($work, $datas)
	{
		$return = [
			"work" => $work,
			"datas" => $datas,
		];
		$table = $this->model->tables("documents");
		switch($work)
		{
			case "new":
			case "edit":
				$return["params"] = [
					"name" => $datas["name"],
				];
				
				if($work == "new")
				{
					$return["params"]["user"] = USERID;
					$return["params"]["date"] = date("Y-m-d H:i:s");
					$return["params"]["category"] = (isset($datas["category"]) AND !empty($datas["category"])) ? $datas["category"] : 0;
					$return["params"]["level"] = (isset($datas["level"]) AND !empty($datas["level"])) ? $datas["level"] : 1;
					$return["params"]["orderNumber"] = $this->model->reOrder($table, "del = '0' AND category = :category", ["category" => $return["params"]["category"]]);
					$return["id"] = $this->model->myInsert($table, $return["params"]);
				}
				else
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, $return["params"], $return["id"]);
				}
				break;
			case "order":
				$category = $this->model->getDocument($datas["id"], "category");
				$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table, "del = '0' AND category = :category", ["category" => $category]);
				break;
			case "del":
				$return["id"] = $datas["id"];
				$category = $this->model->getDocument($datas["id"], "category");
				
				$this->model->myDelete($table, $datas["id"]);
				$this->model->reOrder($table, "del = '0' AND category = :category", ["category" => $return["params"]["category"]]);
				break;
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		return $return;
	}
	
	#JSON
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
