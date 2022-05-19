<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SiteController;
use App\NewCarSelling;

class NewCarSellingController extends BaseController
{
	public $hashSaltBefore = "LnLMv3BtAe";
	public $hashSaltAfter = "Q4EtDth8yE";
	public $tempDir = "_temp_import_files/";
	public $questionnaireIDs = [
		/*"nissan" => 9,
		"peugeot" => 10,
		"hyundai" => 11,
		"kia" => 12,
		"infiniti" => 13,
		"citroen" => 14,
		"general" => 15,*/
		"nissan" => 17,
		"peugeot" => 17,
		"hyundai" => 17,
		"kia" => 17,
		"infiniti" => 17,
		"citroen" => 17,
		"general" => 17,
	];
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "NewCarSelling";
		$this->model = new \App\NewCarSelling($connectionData);
		
		$this->tempDir = base_path()."/".$this->tempDir;
		if(!isset($GLOBALS["site"])) { $GLOBALS["site"] = new SiteController(); }
		$this->siteList = $GLOBALS["site"]->getSites("url", "id");
	}
	
	#Messages
	public function messages($name = "")
	{
		$return = [
			#Import errors
			"no-file" => "Nincs fájl.",
			"file-not-exists" => "A fájl nem létezik.",
			"wrong-extension" => "A fájl kiterjesztése hibás.",
			"get-content" => "A fájl tartalma nem olvasható be.",
			"empty-lines" => "A fájl nem tartalmaz sorokat.",
			"no-rows" => "A fájl nem tartalmaz feldolgozható sorokat.",
			
			#Import data errors
			"different-field-number" => "Eltérő mezőszám.",
			"no-valid-email" => "Nincs valid e-mail cím.",
			"no-customer" => "Ügyfél nem található és nem hozható létre.",
			"unidentifyable-car" => "Hiányzó rendszám / alvázszám.",
			"no-car" => "Autó nem található és nem hozható létre.",
			
			#Success messages
			"success-import" => "A fájl minden követelménynek megfelelt, az importálás sikeresen megtörtént.",
			"success-event" => "Az autó eladást sikeresen rögzítette a rendszer.",
		];
		
		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Set hash
	public function setHash($id, $date, $import)
	{
		return sha1($this->hashSaltBefore."-".$id."-".$date."-".$import."-".$this->hashSaltAfter);
	}	
	
	#Get import
	public function getImport($id)
	{
		$import = $this->model->getImport($id);
		if(!empty($import) AND isset($import->id) AND !empty($import->id))
		{
			#Base
			$return = [];
			$return["data"] = $import;
			
			$return["id"] = $import->id;
			$return["date"] = $import->date;
			$return["progress"] = ($import->progress) ? true : false;
			$return["fileName"] = $import->fileName;
			
			$return["userID"] = $import->user;
			$users = new \App\Http\Controllers\UserController;
			$return["user"] = $users->getUser($import->user);
			$return["userName"] = $return["user"]["name"];
			
			#Output array
			$return["out"] = [
				"basic" => [],
				"rows" => [],
			];
			
			#Basic datas for output
			$return["out"]["basic"]["id"] = ["name" => "ID", "value" => $return["id"]];
			$return["out"]["basic"]["date"] = ["name" => "Importálás dátuma", "value" => $return["date"]];
			$return["out"]["basic"]["type"] = ["name" => "Típus", "value" => ($return["progress"]) ? "Progress export" : "CSV feltöltés"];
			$return["out"]["basic"]["userName"] = ["name" => "Munkatárs", "value" => $return["userName"]];
			if(!empty($return["fileName"])) { $return["out"]["basic"]["fileName"] = ["name" => "Feltöltött fájl neve", "value" => $return["fileName"]]; }
			
			#Success
			if(empty($import->errorMessage))
			{
				$return["success"] = true;
				$return["out"]["basic"]["successMessage"] = ["name" => "Importálás eredménye", "value" => "<span class='color-success'>".$this->messages("success-import")."</span>"];
				$details = $this->jsonDecode($import->datas);
				
				$return["header"] = $details["header"];
				$return["headerCount"] = count($details["header"]);
				$return["headerCountFormatted"] = number_format($return["headerCount"], 0, ",", " ")." db";
				$return["out"]["basic"]["headerCount"] = ["name" => "Szükséges mezők száma", "value" => $return["headerCountFormatted"]];
				
				$return["datas"] = $details["rows"];
				$return["datasCount"] = count($details["rows"]);
				$return["datasCountFormatted"] = number_format($return["datasCount"], 0, ",", " ")." db";
				if($return["datasCount"] > 0)
				{
					foreach($return["datas"] AS $dataID => $data)
					{
						$row = [
							"name" => $data["rowNumber"].". adatsor",
							"details" => $data,
							"basic" => [],
							"datas" => [],
						];
						
						$row["basic"]["row"] = ["name" => "CSV sor tartalma", "value" => implode(" | ", $data["row"])];
						
						$result = "";
						if($data["success"]) { $result .= "<span class='color-success'>".$this->messages("success-event")."</span>"; }
						if(!empty($data["msg"])) { $result .= "<span class='color-danger'>".$this->messages($data["msg"])."</span>"; }
						$row["basic"]["msg"] = ["name" => "Importálás eredménye", "value" => $result];
						
						if(!empty($data["countFields"])) { $row["basic"]["countFields"] = ["name" => "Mezők (oszlopok) száma", "value" => number_format($data["countFields"], 0, ",", " ")." db"]; }
						
						if($data["customerID"] !== NULL)
						{
							$val = $data["customerID"];
							if($data["customerIsNew"]) { $val .= " [ÚJ]"; }
							$row["basic"]["customer"] = ["name" => "Ügyfél ID", "value" => $val];
							
						}
						
						if($data["carID"] !== NULL)
						{
							$val = $data["carID"];
							if($data["carIsNew"]) { $val .= " [ÚJ]"; }
							$row["basic"]["car"] = ["name" => "Autó ID", "value" => $val];
							
						}
						
						if(!empty($data["eventID"])) { $row["basic"]["eventID"] = ["name" => "Autó eladás ID", "value" => $data["eventID"]]; }
						
						foreach($return["header"] AS $i => $headerName)
						{
							if(isset($data["row"][$i])) { $val = $data["row"][$i]; }
							else { $val = ""; }
							$row["datas"][$i] = ["name" => $headerName, "value" => $val];
						}
						
						$return["out"]["rows"][$dataID] = $row;
					}
				}
			}
			#Error
			else
			{
				$return["success"] = false;
				$return["errorMessageCode"] = $import->errorMessage;
				$return["errorMessage"] = $this->messages($import->errorMessage);
				$return["out"]["basic"]["errorMessage"] = ["name" => "Importálási hiba", "value" => "<span class='color-danger'>".$return["errorMessage"]." [Hibakód: ".$return["errorMessageCode"]."]</span>"];
			}	
		}
		else { $return = false; }
		
		return $return;
	}
	
	#Get event
	public function getEvent($id, $allDatas = true, $customerData = false)
	{
		$event = $this->model->getEvent($id);
		if(!empty($event) AND isset($event->id) AND !empty($event->id))
		{
			#Return array
			$return = [];
			$return["data"] = $event;
			
			#Basic datas
			$return["id"] = $event->id;
			$return["customerID"] = $event->customer;
			$return["questionnaireID"] = $event->questionnaire;
			$return["brand"] = $event->brand;
			$return["model"] = $event->name;
			$return["date"] = (!empty($event->date) AND $event->date != "0000-00-00 00:00:00") ? $event->date : NULL;
			$return["dateOut"] = $return["datePublic"] = (!empty($return["date"])) ? date("Y. m. d.", strtotime($return["date"])) : "";
			
			$return["dateSelling"] = (!empty($event->dateSelling) AND $event->dateSelling != "0000-00-00 00:00:00") ? $event->dateSelling : NULL;
			$return["dateSellingOut"] = $return["dateSellingPublic"] = (!empty($return["dateSelling"])) ? date("Y. m. d.", strtotime($return["dateSelling"])) : "";
			
			$return["adminTodoDate"] = (!empty($event->adminTodoDate) AND $event->adminTodoDate != "0000-00-00 00:00:00") ? $event->adminTodoDate : NULL;
			$return["adminTodoDateOut"] = (!empty($return["adminTodoDate"])) ? date("Y. m. d.", strtotime($return["adminTodoDate"])) : "";
			$return["adminTodoSuccess"] = ($event->adminTodoSuccess) ? true : false;
			
			#Other Datas
			$return["hash"] = $event->hash;		
			$return["statusID"] = $event->status;
			$return["adminName"] = $event->adminName;
			$return["premise"] = $event->premise;
			
			$return["score"] = $event->score;
			$return["scoreOut"] = (!empty($return["scoreOut"])) ? number_format($return["score"], 0, ".", " ")." pont" : "0 pont";
			
			$return["allDetails"] = $this->jsonDecode($event->allDetails);
			
			#Questionnaire for list
			if(empty($event->questionnaireAnswer))
			{
				$customers = new \App\Customer;
				$questionnaires = new \App\Questionnaire;
				$return["questionnaireLink"] = env("PATH_QUESTIONNAIRE_WEB").$questionnaires->getQuestionnaire($return["data"]->questionnaire, "url")."/".$customers->getCustomer($return["data"]->customer, "hash")."/".$return["hash"];
				$return["questionnaireLinkUser"] = (isset($GLOBALS["user"])) ? $return["questionnaireLink"]."/".$GLOBALS["user"]["token"] : "#";
			}
			else { $return["questionnaireLink"] = $return["questionnaireLinkUser"] = "#"; }
			
			if ($customerData)
			{
				$customers = new \App\Http\Controllers\CustomerController;
				$return["customerName"] = $return["customerCode"] = $return["carName"] = $return["userName"] = $return["importName"] = NULL;
				
				$return["customer"] = $customers->getCustomer($event->customer, false);
				if($return["customer"] !== false) 
				{ 
					$return["customerName"] = $return["customer"]["name"]; 
					$return["customerCode"] = $return["customer"]["code"]; 
					$return["customerProgressCode"] = $return["customer"]["progressCode"]; 
					$return["customerEmail"] = $return["customer"]["email"]; 
				}
				$return["car"] = $customers->getCar($event->car, false);
				if($return["car"] !== false) { $return["carName"] = $return["car"]["name"]; }
			}
			
			if($allDatas)
			{
				#Customer and car
				if(!$customerData)
				{
					$customers = new \App\Http\Controllers\CustomerController;
					$return["customerName"] = $return["customerCode"] = $return["carName"] = $return["userName"] = $return["importName"] = NULL;
					
					$return["customer"] = $customers->getCustomer($event->customer, false);
					if($return["customer"] !== false) 
					{ 
						$return["customerName"] = $return["customer"]["name"]; 
						$return["customerCode"] = $return["customer"]["code"]; 
						$return["customerProgressCode"] = $return["customer"]["progressCode"]; 
						$return["customerEmail"] = $return["customer"]["email"]; 
					}
					
					$return["car"] = $customers->getCar($event->car, false);
					if($return["car"] !== false) { $return["carName"] = $return["car"]["name"]; }
				}
				
				#Import datas
				$return["import"] = $this->getImport($event->import);
				if($return["import"] !== false) { $return["importName"] = $return["import"]["fileName"]; }
				
				#Questionnaire
				$questionnaires = new \App\Http\Controllers\QuestionnaireController;
				$return["questionnaire"] = (!empty($return["questionnaireID"])) ? $questionnaires->getQuestionnaire($return["questionnaireID"], false, false, false) : false;
				$return["questionnaireAnswer"] = $questionnaires->getAnswer($event->questionnaireAnswer, false);
				if($return["questionnaireAnswer"] !== false) { $return["hasQuestionnaireAnswer"] = true; }
				else 
				{ 
					$return["questionnaireAnswer"] = $questionnaires->getAnswerByIdentifiers(1, $return["customer"]["id"], $event->id, NULL, false);
					if($return["questionnaireAnswer"] !== false) { $return["hasQuestionnaireAnswer"] = true; }
					else { $return["hasQuestionnaireAnswer"] = false; }
				}
				
				#User
				$return["userID"] = $event->user;
				$users = new \App\Http\Controllers\UserController;
				$return["user"] = $users->getUser($event->user);
				if($return["user"] !== false) { $return["userName"] = $return["user"]["name"]; }
				
				#Status changes
				$return["statuses"] = $this->getEventStatusChanges($event->id);
				
				#Output datas
				$output = [
					"background" => ["name" => "Háttéradatok", "data" => []],
					"event" => ["name" => "Esemény adatai", "data" => []],
					"statuses" => ["name" => "Állapotváltozások", "data" => []],
					"questionnaire" => ["name" => "Kérdőív", "data" => []],
					"details" => ["name" => "Összes (kapott) adat", "data" => []],
				];
				
				#Background data
				if(!empty($return["hash"])) { $output["background"]["data"]["hash"] = ["name" => "Belső kód (Hash)", "val" => $return["hash"]]; }
				// if(!empty($return["userName"])) { $output["background"]["data"]["userName"] = ["name" => "Munkatárs", "val" => $return["userName"]]; }
				if(!empty($return["date"])) { $output["background"]["data"]["date"] = ["name" => "Rögzítés időpontja", "val" => $return["dateOut"]]; }
				if(!empty($return["customerName"])) { $output["background"]["data"]["customerName"] = ["name" => "Ügyfél név", "val" => $return["customerName"]]; }
				if(!empty($return["customerCode"])) { $output["background"]["data"]["customerCode"] = ["name" => $customers->codeName, "val" => $return["customerCode"]]; }
				if(!empty($return["carName"])) { $output["background"]["data"]["carName"] = ["name" => "Autó", "val" => $return["carName"]]; }
				if(!empty($return["importName"])) { $output["background"]["data"]["importName"] = ["name" => "Import fájlnév", "val" => $return["importName"]]; }
				if($return["questionnaire"] !== false) { $output["background"]["data"]["questionnaire"] = ["name" => "Csatolt kérdőív", "val" => $return["questionnaire"]["name"]." (".$return["questionnaire"]["code"].")"]; }
				
				#Event data				
				if(!empty($return["dateSellingOut"])) { $output["event"]["data"]["dateSellingOut"] = ["name" => "Eladás dátuma", "val" => $return["dateSellingOut"]]; }
				if(!empty($return["brand"])) { $output["event"]["data"]["brand"] = ["name" => "Márka", "val" => $return["brand"]]; }
				if(!empty($return["premise"])) { $output["event"]["data"]["premise"] = ["name" => "Telephely", "val" => $return["premise"]]; }
				if(!empty($return["adminName"])) { $output["event"]["data"]["adminName"] = ["name" => "Értékesítő", "val" => $return["adminName"]]; }
				if(!empty($return["scoreOut"])) { $output["event"]["data"]["score"] = ["name" => "Eseményért járó pontok száma", "val" => $return["scoreOut"]]; }
				
				#All details
				if(!empty($return["allDetails"]))
				{
					foreach($return["allDetails"] AS $detailKey => $detailVal) 
					{
						if(!empty($detailVal)) { $output["details"]["data"][$detailKey] = ["name" => $detailKey, "val" => $detailVal]; }
					}
				}
				
				#Questionnaire answer
				if($return["hasQuestionnaireAnswer"])
				{
					if(!empty($return["questionnaireAnswer"]["date"])) { $output["questionnaire"]["data"]["date"] = ["name" => "Kitöltés időpontja", "val" => $return["questionnaireAnswer"]["dateOut"]]; }
					if(!empty($return["questionnaireAnswer"]["questionnaireCode"])) { $output["questionnaire"]["data"]["questionnaireCode"] = ["name" => "Kérdőív kódja", "val" => $return["questionnaireAnswer"]["questionnaireCode"]]; }
					if(!empty($return["questionnaireAnswer"]["questionnaireName"])) { $output["questionnaire"]["data"]["questionnaireName"] = ["name" => "Kérdőív címe", "val" => $return["questionnaireAnswer"]["questionnaireName"]]; }
					
					if($return["questionnaireAnswer"]["answerByUser"]) 
					{ 
						$qAnswerUser = $users->getUser($return["questionnaireAnswer"]["data"]->user);
						$val = "Munkatárs: <em>".$qAnswerUser["name"]."</em>"; 
					}
					else
					{ 
						$customerHere = $customers->getCustomer($return["questionnaireAnswer"]["data"]->customer, false);
						$val = "Ügyfél: <em>".$customerHere["name"]."</em>";
					}
					$output["questionnaire"]["data"]["answerBy"] = ["name" => "Kitöltő", "val" => $val];
					
					if(!empty($return["questionnaireAnswer"]["customerData"]))
					{
						$output["questionnaire"]["data"]["customerDatas"] = ["name" => "<strong>BEKÜLDÖTT ÜGYFÉL ADATOK</strong>", "val" => ""];
						$i = 1;
						foreach($return["questionnaireAnswer"]["customerData"] AS $dataName => $dataVal)
						{
							$output["questionnaire"]["data"]["customerData".$i] = ["name" => $dataName, "val" => $dataVal];
							$i++;
						}
					}
					
					if(!empty($return["questionnaireAnswer"]["answers"]))
					{
						$output["questionnaire"]["data"]["answers"] = ["name" => "<strong>VÁLASZOK</strong>", "val" => ""];
						$i = 1;
						foreach($return["questionnaireAnswer"]["answers"] AS $questionID => $data)
						{
							$output["questionnaire"]["data"]["answer".$i] = ["name" => $data["name"], "val" => $data["val"]];
							$i++;
						}
					}
				}
				
				#Store output
				$return["output"] = $output;
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getEventByHash($hash, $allDatas = true, $customerData = false)
	{
		$id = $this->model->getEventByHash($hash, "id");
		if(!empty($id)) { $return = $this->getEvent($id, $allDatas, $customerData); }
		else { $return = false; }
		return $return;
	}
	
	public function getEventForQuestionnaireByHash($hash)
	{
		$id = $this->model->getEventByHash($hash, "id");
		if(!empty($id)) { $return = $this->getEvent($id, false, false); }
		else { $return = false; }
		return $return;
	}
	
	public function getEvents($search = [], $customer = NULL, $key = "id", $deleted = 0, $orderBy = "date DESC, id DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		
		$rows = $this->model->getEvents($customer, $fields, $search, $deleted, $orderBy);
		
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getEvent($row->id, false, false); 
			}
		}
		return $return;
	}
	
	public function getEventsForTodoList($questionnaireID = NULL, $key = "id", $deleted = 0, $orderBy = "adminTodoDate DESC, id DESC")
	{
		$search = [
			"questionnaireAnswered" => false,
			"adminTodo" => true,
			"adminTodoDateToNow" => true,
			"questionnaireID" => $questionnaireID,
		];
		
		return $this->getEvents($search, NULL, $key, $deleted, $orderBy);
	}
	
	public function getEventsForExport($brand, $dateFrom, $dateTo, $premises = [], $orderBy = "date")
	{
		$return = [];
		$rows = $this->model->getEventsForExports($brand, $dateFrom, $dateTo, $premises, $orderBy);	
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) { $return[$row->id] = $this->getEvent($row->id, false, false);  }
		}
		return $return;
	}
	
	public function getEventsForLog($search = [])
	{
		#Base query
		$query = "SELECT * FROM ".$this->model->tables("events")." WHERE del = '0'";
		$params = [];
		
		#Search
		if(isset($search["dateFrom"]) AND !empty($search["dateFrom"]))
		{
			$query .= " AND date >= :dateFrom";
			$params["dateFrom"] = $search["dateFrom"];
		}
		if(isset($search["dateTo"]) AND !empty($search["dateTo"]))
		{
			$query .= " AND date <= :dateTo";
			$params["dateTo"] = $search["dateTo"];
		}
		if(isset($search["marketingDisabled"]) AND $search["marketingDisabled"] !== "")
		{
			$query .= " AND marketingDisabled = :marketingDisabled";
			$params["marketingDisabled"] = $search["marketingDisabled"];
		}
		
		#Ordering
		if(isset($search["orderBy"])) { $query .= " ORDER BY ".$search["orderBy"]; }
		
		#Return
		$return = [];
		$rows = $this->model->select($query, $params);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				#Row
				$return[$row->id] = [
					"event" => $row,
					"allDetails" => $this->jsonDecode($row->allDetails),
					"emails" => [],
					"tableDatas" => [
						"ID" => $row->id,
						"Importálás ideje" => date("Y.m.d.", strtotime($row->date)),
						"Marketing?" => ($row->marketingDisabled) ? "Nem" : "<strong>IGEN</strong>",
						"Nem kell hívni?" => "",
						"Kérdőív kitöltve?" => ($row->questionnaireAnswer > 0) ? "<strong>IGEN</strong>" : "Nem",
						"1. email esedékesség" => date("Y.m.d.", strtotime($row->date." +2 days")),
						"1. email kiküldve" => "-",
						"2. email esedékesség" => date("Y.m.d.", strtotime($row->date." +4 days")),
						"2. email kiküldve" => "-",
						"3. email esedékesség" => date("Y.m.d.", strtotime($row->date." +6 days")),
						"3. email kiküldve" => "-",
						"Kiküldött e-mailek száma" => 0,
						"Hibák" => [],
						"Hibák száma" => 0,
					],
					"tableRowStyle" => "",
				];
				
				$return[$row->id]["tableDatas"]["Nem kell hívni?"] = $return[$row->id]["allDetails"]["nem kell hívni"];
				
				#Emails
				$emails = $this->model->select("SELECT * FROM ".$this->model->tables("emailsLog")." WHERE del = '0' AND sellingEvent = :sellingEvent", ["sellingEvent" => $row->id]);
				if(count($emails) > 0)
				{
					$return[$row->id]["tableDatas"]["Kiküldött e-mailek száma"] = count($emails);
					foreach($emails AS $j => $email)
					{
						if($j <= 2) 
						{ 
							$jOut = $j + 1;
							$return[$row->id]["tableDatas"][$jOut.". email kiküldve"] = date("Y.m.d.", strtotime($email->date)); 
						}
						$return[$row->id]["emails"][$email->id] = $email;
					}
				}
				
				#Style
				if($row->marketingDisabled) { $return[$row->id]["tableRowStyle"] .= "background-color: #ff8000;"; }
				else
				{
					for($j = 1; $j <= 3; $j++)
					{
						if($return[$row->id]["tableDatas"]["Kiküldött e-mailek száma"] < 3 AND $return[$row->id]["tableDatas"][$j.". email kiküldve"] != $return[$row->id]["tableDatas"][$j.". email esedékesség"] AND $return[$row->id]["tableDatas"][$j.". email esedékesség"] <= date("Y.m.d.")) { $return[$row->id]["tableRowStyle"] .= "background-color: #cc0000;"; }
					}
				}
				
				if($row->questionnaireAnswer > 0) { $return[$row->id]["tableRowStyle"] .= "background-color: #3333ff;"; }
				
				$greenOkay = 0;
				for($j = 1; $j <= 3; $j++)
				{
					if($return[$row->id]["tableDatas"][$j.". email kiküldve"] == $return[$row->id]["tableDatas"][$j.". email esedékesség"] OR $return[$row->id]["tableDatas"][$j.". email esedékesség"] > date("Y.m.d.")) { $greenOkay++; }
				}
				if($row->marketingDisabled AND !empty($return[$row->id]["tableDatas"]["Nem kell hívni?"])) { $greenOkay = 3; }
				if($row->marketingDisabled AND empty($return[$row->id]["tableDatas"]["Nem kell hívni?"])) { $greenOkay = -1; }
				
				if($greenOkay == 3) { $return[$row->id]["tableRowStyle"] .= "background-color: #73ff96;"; }
				
				#Errors
				if($row->marketingDisabled AND empty($return[$row->id]["tableDatas"]["Nem kell hívni?"])) { $return[$row->id]["tableDatas"]["Hibák"][] = "Marketing tiltás"; }
				if(!$row->marketingDisabled AND !empty($return[$row->id]["tableDatas"]["Nem kell hívni?"])) { $return[$row->id]["tableDatas"]["Hibák"][] = "Marketing engedélyezés"; }
				
				if($row->marketingDisabled)
				{
					for($j = 1; $j <= 3; $j++)
					{
						if($return[$row->id]["tableDatas"][$j.". email kiküldve"] != "-") { $return[$row->id]["tableDatas"]["Hibák"][] = $j.". e-mail kiküldés tiltás ellenére"; }
					}
				}
				else
				{	
					for($j = 1; $j <= 3; $j++)
					{
						if($return[$row->id]["tableDatas"][$j.". email kiküldve"] != $return[$row->id]["tableDatas"][$j.". email esedékesség"] )
						{
							if($return[$row->id]["tableDatas"][$j.". email esedékesség"] <= date("Y.m.d.")) { $return[$row->id]["tableDatas"]["Hibák"][] = $j.". e-mail kiküldés elmaradt"; }
						}
					}
				}
				$return[$row->id]["tableDatas"]["Hibák száma"] = count($return[$row->id]["tableDatas"]["Hibák"]);
				$return[$row->id]["tableDatas"]["Hibák"] = implode(", ", $return[$row->id]["tableDatas"]["Hibák"]);
			}
		}
		return $return;
	}
	
	#New event
	public function newEvent($customerID, $carID, $importID, $params, $systemTextOnSuccess = "")
	{
		$customers = new CustomerController;
		$customer = $customers->getCustomer($customerID, false);
		if($customer === false)
		{
			$return = false;
			$customers->log("newcar-sellings-new", NULL, NULL, ["systemText" => "Hiba: Nem létező ügyfél!", "json" => $customerID, "jsonNewDatas" => $params]);
		}
		else
		{
			if(defined("USERID")) { $params["user"] = USERID; }
			else { $params["user"] = NULL; }
			$params["date"] = $params["adminTodoDate"] = date("Y-m-d H:i:s");
			$params["customer"] = $customerID;
			$params["car"] = $carID;
			$params["import"] = $importID;
			
			$return = $this->model->myInsert($this->model->tables("events"), $params);
			$customers->log("newcar-sellings-new", $return, $return, ["systemText" => $systemTextOnSuccess, "jsonNewDatas" => $params]);
			
			$hash = $this->setHash($return, $params["date"], $importID);
			$this->model->myUpdate($this->model->tables("events"), ["hash" => $hash], $return);
		}
		
		return $return;
	}
	
	#Change event
	public function editEvent($id, $params)
	{
		return $this->model->myUpdate($this->model->tables("events"), $params, $id);
	}
	
	#Import CSV
	public function import()
	{
		#If file has uploaded
		if(isset($_FILES["file"]) AND !empty($_FILES["file"]["name"])) 
		{ 
			$filePath = $this->tempDir.$_FILES["file"]["name"];
			move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
			$return = $this->eventsImport($filePath, 0); 
			unlink($filePath);
		}
		#If there is NO FILE
		else 
		{ 
			$return = [];
			$return["id"] = $this->model->newImport(NULL, "no-file", NULL, NULL, 0); 
			$return["errorMessage"] = "no-file";
		}
		
		return $return;
	}
	
	public function eventsImport($filePath, $progressVal = 1)
	{
		#Return array
		$return = [];
		
		#If file has uploaded
		if(!empty($filePath) AND file_exists($filePath))
		{			
			#File and name
			$file = pathinfo($filePath);
			$return["file"]["name"] = $fileName = $file["basename"];
			$return["file"]["dirName"] = $dirName = $file["dirname"];
			
			#Extension
			$return["file"]["originalExtension"] = $originalExtension = $file["extension"];
			$return["file"]["extension"] = $extension = strtolower($originalExtension);
			
			#If file is CSV ------------------
			if($extension == "csv")
			{				
				#Get content => Create database row
				$content = file_get_contents($filePath);
				if($content !== false)
				{
					$content = iconv("iso-8859-2", "utf-8", $content);
					$return["id"] = $importID = $this->model->newImport($fileName, NULL, $return, $content, $progressVal); 
					
					#Get lines
					$content = str_replace(["\r", "\n"], ["XXXXXXXX", "XXXXXXXX"], $content);
					$content = str_replace("XXXXXXXXXXXXXXXX", "XXXXXXXX", $content);
					$lines = explode("XXXXXXXX", $content);
					$rows = [];				
					
					#Content has lines ------------------
					if(!empty($lines))
					{
						#Field list
						$firstLine = array_shift($lines);
						$fields = str_getcsv($firstLine, ";");
						
						#Create array
						foreach($lines AS $line) 
						{ 
							$lineArray = str_getcsv($line, ";");
							if(count($lineArray) == 0) { continue; }
							elseif(empty($lineArray[0])) { continue; }
							else { $rows[] = $lineArray; }
						}
						
						#OKAY ------------------
						if(count($rows) > 0)
						{ 								
							$customers = new CustomerController;
							$marketingDisabledIdList = $customers->getMarketingDisabledIdList();
							$marketingDisabledCodeList = $customers->model->getMarketingDisabledList();
							$fieldCount = count($fields);
							$datas = [];
							foreach($rows AS $i => $row)
							{
								#Basic row datas ------------------
								$rowCount = count($row);
								$datas[$i] = [
									"success" => true,
									"msg" => NULL,
									"row" => $row,
									"countFields" => $rowCount,
									"rowNumber" => $i + 1,
									"customerIsNew" => NULL,
									"customerID" => NULL,
									"carIsNew" => NULL,
									"carID" => NULL,
									"eventID" => NULL,
								];
								
								#If number of fields is correct
								if($rowCount == $fieldCount)
								{
									#Create array from all datas
									$allDetails = array_combine($fields, $row);
									
									#Important row fields
									$email = trim($row[6]);
									$mobile = trim($row[5]);								
									$name = trim($row[4]);
									
									$progressCode = trim($row[3]);
									$progressCode = trim($progressCode, "'");
									$progressCode = trim($progressCode);
									$progressCode = trim($progressCode, "'");
									$progressCode = trim($progressCode); //multiple trim: for safe
									
									$premise = trim($row[1]);
									$adminName = trim($row[2]);
									$brand = trim($row[7]);
									$modelName = trim($row[8]);
									$regNumber = trim($row[16]);
									$bodyNumber = trim($row[17]);							
									
									$dateSelling = trim($row[0]);
									if(!empty($dateSelling))
									{
										$dateSelling = str_replace([" ", "."], ["", "-"], $dateSelling);
										$dateSelling = date("Y-m-d", strtotime($dateSelling));
									}
									else { $dateSelling = NULL; }
									
									#Other row fields
									$score = trim($row[22]);
									if(empty($score)) { $score = 0; }
									
									#Validate e-mail
									if(empty($email) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) { $email = false; }
									if($email === false) { $datas[$i]["msg"] = "no-valid-email"; }

									#Identify OR Create Customer
									if(!empty($progressCode)) { $customer = $customers->getCustomerByProgressCode($progressCode); }
									else { $customer = false; }
									
									#Phone, Name
									$mobileHere = (!empty($mobile)) ?  $customers->customersPhone($mobile) : NULL;
									$nameDatas = $customers->importCustomersName($name);
									
									#Get customer
									if($customer !== false)
									{
										$datas[$i]["customerIsNew"] = false;
										$datas[$i]["customerID"] = $customerID = $customer["id"];
									}
									else
									{
										#Params
										$params = [
											"email" => $email,
											"progressCode" => $progressCode,
											"mobile" => $mobileHere,
											"firstName" => $nameDatas["firstName"],
											"lastName" => $nameDatas["lastName"],
											"beforeName" => $nameDatas["beforeName"],
										];
										
										#New customer
										$systemText = "Autó eladás import; id: ".$importID.", adatsor ID: ".$i;
										$datas[$i]["customerID"] = $customerID = $customers->newCustomer2($params, $systemText);
										$datas[$i]["customerIsNew"] = true;
										if($customerID !== false) { $customer = $customers->getCustomer($customerID, false); }
									}
									
									#If customer is OK									
									if($customer !== false AND $customerID !== false)
									{
										#Update customer datas
										if(!$datas[$i]["customerIsNew"])
										{
											$paramsCustomerUpdate = [
												"email" => $email,
												"mobile" => $mobileHere,
												"firstName" => $nameDatas["firstName"],
												"lastName" => $nameDatas["lastName"],
												"beforeName" => $nameDatas["beforeName"],
											];
											$customers->changeCustomer($customerID, $paramsCustomerUpdate, $customer["data"]);
											$customer = $customers->getCustomer($customerID, false);
										}
										
										#Identify OR Create Car
										$car = false;
										if(!empty($regNumber) AND !empty($bodyNumber))
										{										
											$car = $customers->getCarByIdentifiers($customerID, $regNumber, $bodyNumber, false);
											if($car !== false)
											{
												$datas[$i]["carIsNew"] = false;
												$datas[$i]["carID"] = $carID = $car["id"];
											}
											
										}
										#UNIDENTIFYABLE car
										else { $datas[$i]["msg"] = "unidentifyable-car"; }
										
										#Create new car
										if($car === false)
										{
											#Params
											$params = [
												"brand" => $brand,
												"name" => $modelName,
												"regNumber" => (!empty($regNumber)) ? $regNumber : NULL,
												"bodyNumber" => (!empty($bodyNumber)) ? $bodyNumber : NULL,
											];
											
											#New car
											$systemText = "Autó eladás import; id: ".$importID.", adatsor ID: ".$i;
											$datas[$i]["carID"] = $carID = $customers->newCar($customerID, $params, $systemText);
											$datas[$i]["carIsNew"] = true;
											if($carID !== false) { $car = $customers->getCar($carID, false); }
										}									
										
										#If car is OK
										if($car !== false AND $carID !== false)
										{
											#Questionnaire
											$brandInner = strtolower($brand);
											switch($brandInner)
											{
												case "nissan":
												case "hyundai":
												case "kia":
												case "peugeot":
												case "infiniti":
												case "citroen":
													$questionnaire = $this->questionnaireIDs[$brandInner];
													break;
												default: 
													$questionnaire = $this->questionnaireIDs["general"];
													break;
											}
											
											#CREATE EVENT
											$notCallable = trim($row[10]);
											if(!empty($progressCode) AND in_array($progressCode, $marketingDisabledCodeList)) { $marketingDisabled = 1; }
											elseif(!empty($progressCode) AND in_array($customerID, $marketingDisabledIdList)) { $marketingDisabled = 1; }
											elseif(mb_strtolower(trim($email), "utf-8") == "info@gablini.hu") 
											{
												if(!empty($progressCode)) 
												{ 
													$customers->addMarketingDisabled($progressCode, "NewCarSellingController::eventsImport(), info@gablini.hu"); 
													$marketingDisabledCodeList[] = $progressCode;
												}
												$marketingDisabled = 1;
											}
											elseif($notCallable == "x" OR $notCallable == "X") { $marketingDisabled = 1; }
											elseif(strpos($brandInner, "aston") !== false) { $marketingDisabled = 1; }
											else { $marketingDisabled = 0; }
											
											$params = [
												"dateSelling" => $dateSelling,
												"brand" => $brandInner,
												"allDetails" => $this->json($allDetails),
												"questionnaire" => $questionnaire,										
												"adminName" => $adminName,											
												"premise" => $premise,											
												"score" => $score,
												"marketingDisabled" => $marketingDisabled,
											];
											if(!$email) 
											{
												$params["adminTodo"] = 1; 
												if(empty($phone) AND empty($mobile) AND empty($mobileFinishedReport)) 
												{ 
													$params["status"] = 15; 
													$params["adminTodo"] = 0; 
												}
											}
											if($marketingDisabled OR !empty($prevEvent)) { $params["adminTodo"] = 0; }
											$datas[$i]["eventID"] = $eventID = $this->newEvent($customerID, $carID, $importID, $params, NULL);
										}
										#If problem with car
										else
										{
											$datas[$i]["success"] = false;
											$datas[$i]["msg"] = "no-car";
										}									
									}
									#If problem with customer
									else
									{
										$datas[$i]["success"] = false;
										$datas[$i]["msg"] = "no-customer";
									}									
								}
								#INCORRECT number of fields
								else
								{
									$datas[$i]["success"] = false;
									$datas[$i]["msg"] = "different-field-number";
								}
							}
							
							#Log import -----------------
							$this->model->editImport($importID, ["datas" => ["header" => $fields, "rows" => $datas]]);					
						}
						#OKAY END || No rows after creating array
						else 
						{ 
							$this->model->editImport($importID, ["errorMessage" => "no-rows"]);
							$return["errorMessage"] = "no-rows";
						}
					}
					#Content has no lines
					else 
					{ 
						$this->model->editImport($importID, ["errorMessage" => "empty-lines"]);
						$return["errorMessage"] = "empty-lines";
					}
				}
				else
				{
					$return["id"] = $this->model->newImport($fileName, "get-content", $return, NULL, $progressVal); 
					$return["errorMessage"] = "get-content";
				}
			}
			#Other extension
			else 
			{ 
				$return["id"] = $this->model->newImport($fileName, "wrong-extension", $return, NULL, $progressVal); 
				$return["errorMessage"] = "wrong-extension";
			}
		}
		#If there is NO FILE
		else 
		{ 
			$return["errorMessage"] = "file-not-exists";
			$return["id"] = $this->model->newImport(NULL, $return["errorMessage"], NULL, NULL, $progressVal); 
		}
		
		#If error
		if(!empty($return["errorMessage"]))
		{
			$emailData = [
				"fileName" => "eventsImport() => ".$filePath,
				"errorMessage" => $return["errorMessage"],
			];
			include(DIR_VIEWS."emails/crm/newcarSellingEmailImportError.php");
		}
		
		return $return;
	}
	
	#Get actual Progress CSV files
	public function eventsFilesImport()
	{
		#Return data
		$return = [
			"error" => NULL,
			"files" => [],
			"fileCount" => 0,
			"actualFiles" => [],
			"actualFileCount" => 0,
		];
		
		#Get processed files
		$processedFiles = [];
		$fileNames = $this->model->getEventsImportFileNames();
		foreach($fileNames AS $fileNameRow) { $processedFiles[] = $fileNameRow->fileName; }
		
		#Files
		$filesDir = base_path()."/../admin.gablini.hu/ajanlatado_ujauto_eladasok_export/";
		$files = scandir($filesDir);
		foreach($files AS $file)
		{
			if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) == "csv")
			{
				#Log file
				$return["files"][] = $file;
				$return["fileCount"]++;
				
				#If actual
				if(!in_array($file, $processedFiles))
				{
					#Log actual file
					$return["actualFiles"][$file] = [
						"fileName" => $file,
						"error" => NULL,
						"import" => [],
					];
					$return["actualFileCount"]++;
					
					#Create local file and fill with content
					$localFile = $this->tempDir.$file;
					if(file_exists($localFile)) { unlink($localFile); }
					$fileMove = copy($filesDir.$file, $localFile);
					
					#Process file
					if($fileMove)
					{
						#Event import
						$importReturn = $this->eventsImport($localFile);
						$return["actualFiles"][$file]["import"]["id"] = $importReturn["id"];
						$return["actualFiles"][$file]["import"]["errorMessage"] = $importReturn["errorMessage"];
						$return["actualFiles"][$file]["import"]["file"] = $importReturn["file"];
						
						#Delete temp file
						unlink($localFile);
					}
					else { $return["actualFiles"][$file]["error"] = "copy"; }
					
				}
			}
		}
		
		#If error
		if($return["actualFileCount"] == 0)
		{
			$emailData = [
				"fileName" => "eventsFilesImport() No Actual File(s)",
				"errorMessage" => "actualFileCount == 0",
			];
			include(DIR_VIEWS."emails/crm/newcarSellingEmailImportError.php");
		}
		
		#Return true
		return $return;
	}
	
	#Get emails for cron
	public function emailsForCron()
	{
		#Return
		$return = [];
		
		#Get emails
		$emails = $this->model->getEmails();
		foreach($emails AS $email)
		{
			#Return structure
			$return[$email->id] = [
				"data" => $email,
				"eventsFromLog" => NULL,
				"eventsForSend" => [],
			];
			
			#Get event ids (email has sent for them)
			$eventIDs = [];
			$logs = $this->model->getEmailsLogs($email->id);
			foreach($logs AS $log) { $eventIDs[] = $log->sellingEvent; }
			$return[$email->id]["eventsFromLog"] = $eventIDs;
			
		}
		
		#Get events for sending
		$emailController = new \App\Http\Controllers\EmailController;
		$customerController = new \App\Http\Controllers\CustomerController;
		$mainBrands = ["nissan", "hyundai", "kia", "peugeot", "infiniti", "citroen"];

		foreach($return AS $emailID => $returnRow)
		{
			$email = $returnRow["data"];
			$eventIDs = $returnRow["eventsFromLog"];
			$events = $this->model->getEventsForEmailSending($email->dateField, $email->seconds, $email->brand, $mainBrands);
			if(!empty($events))
			{
				#Admin email
				if($email->adminEmail)
				{
					#Store events
					$eventsForSend = [];
					foreach($events AS $event)
					{
						#Email hasn't been sent AND previos email has been sent!
						if(!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])))
						{
							#Store in return array
							$return[$email->id]["eventsForSend"][] = $event->id;
							
							#Store events for email
							$eventDetails = $this->getEvent($event->id, false, true);
							$eventsForSend[$event->id] = $eventDetails;
						}
					}
					
					if(!empty($eventsForSend)) 
					{ 
						#Log
						foreach($eventsForSend AS $eventID => $eventRow) 
						{ 
							$this->model->newEmailLog($email->id, $eventRow["id"]); 
							if($eventRow["data"]->questionnaire == 7) { unset($eventsForSend[$eventID]); }
							else { $this->model->myUpdate($this->model->tables("events"), ["adminTodo" => 1], $eventRow["id"]); }
						}
						
						#Create email
						// include(DIR_VIEWS."emails/crm/newcarSellingEmailAdmin.php"); 
					}
				}
				#Customer email
				else
				{				
					foreach($events AS $event)
					{
						#Email hasn't been sent AND previos email has been sent!
						if(!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])))
						{
							#Store in return array
							$return[$email->id]["eventsForSend"][] = $event->id;
							$eventDetails = $this->getEvent($event->id, false, true);
							
							#Create email
							$this->model->newEmailLog($email->id, $event->id);
							include(DIR_VIEWS."emails/crm/newcarSellingEmail.php");
						}
					}
				}
			}
		}
		
		#Return
		return $return;
	}
	
	public function emailsForCronToCheckInOutput()
	{
		#Return
		$return = [];
		$stat = [
			"marketingDisabled" => [],
			"marketingDisabledCount" => 0,
			"events" => [],
			"eventListForWatch" => [],
			"eventListForWatchCount" => 0,
		];
		
		#Get emails
		$eventIDListForWatch = [238, 239, 240, 241, 245, 247, 248, 249, 251, 252, 254, 258, 261, 265, 268, 269, 270, 272, 273, 277, 278, 285, 287, 288, 289, 291, 292, 293, 295, 298, 299, 300, 301, 303, 306, 307, 308];
		
		$emails = $this->model->getEmails();
		foreach($emails AS $email)
		{
			#Return structure
			$return[$email->id] = [
				"data" => $email,
				"eventsFromLog" => NULL,
				"eventsForSend" => [],
			];
			
			#Get event ids (email has sent for them)
			$eventIDs = [];
			$logs = $this->model->getEmailsLogs($email->id);
			foreach($logs AS $log) { $eventIDs[] = $log->sellingEvent; }
			$return[$email->id]["eventsFromLog"] = $eventIDs;
			
		}
		
		#Get events for sending
		$emailController = new \App\Http\Controllers\EmailController;
		$customerController = new \App\Http\Controllers\CustomerController;
		$marketingDisabledIdList = $customerController->getMarketingDisabledIdList();
		$mainBrands = ["nissan", "hyundai", "kia", "peugeot", "infiniti", "citroen"];

		foreach($return AS $emailID => $returnRow)
		{
			$email = $returnRow["data"];
			$eventIDs = $returnRow["eventsFromLog"];
			$events = $this->model->getEventsForEmailSending($email->dateField, $email->seconds, $email->brand, $mainBrands);
			if(!empty($events))
			{
				#Marketing disabled
				if(in_array($event->customer, $marketingDisabledIdList)) { $stat["marketingDisabled"][] = $event->id; $stat["marketingDisabledCount"]++; /*$this->model->myUpdate($this->model->tables("events"), ["marketingDisabled" => 1], $event->id);*/ }
				else
				{
					#Admin email
					if($email->adminEmail)
					{
						#Store events
						$eventsForSend = [];
						foreach($events AS $event)
						{
							if(in_array($event->id, $eventIDListForWatch))
							{
								$sendable = (!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"]))) ? "<strong style='color: #00cc00;'>KÜLDHETŐ!!!</strong>" : "";
								$stat["eventListForWatch"][$event->id][] = [
									"inEventIDs?" => (!in_array($event->id, $eventIDs)) ? "No - jó" : "Yes",
									"previousEmailEmpty?" => (empty($email->previousEmail)) ? "Yes - jó" : "No",
									"IsPrevEmail AND inEventsFromLogArray?" => (!empty($email->previousEmail) AND in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])) ? "Yes - jó" : "No",
									"sendable" => $sendable,
								];
								$stat["eventListForWatchCount"]++;
							}
							
							$stat["events"][] = $event->id;
							#Email hasn't been sent AND previos email has been sent!
							if(!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])))
							{
								#Store in return array
								$return[$email->id]["eventsForSend"][] = $event->id;
								
								#Store events for email
								$eventDetails = $this->getEvent($event->id, false, true);
								$eventsForSend[$event->id] = $eventDetails;
							}
						}
						
						if(!empty($eventsForSend)) 
						{ 
							#Log
							foreach($eventsForSend AS $eventID => $eventRow) 
							{ 
								$this->model->newEmailLog($email->id, $eventRow["id"]); 
								if($eventRow["data"]->questionnaire == 7) { unset($eventsForSend[$eventID]); }
								else { $this->model->myUpdate($this->model->tables("events"), ["adminTodo" => 1], $eventRow["id"]); }
							}
							
							#Create email
							// include(DIR_VIEWS."emails/crm/newcarSellingEmailAdmin.php"); 
						}
					}
					#Customer email
					else
					{				
						foreach($events AS $event)
						{
							if(in_array($event->id, $eventIDListForWatch))
							{
								$sendable = (!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"]))) ? "<strong style='color: #00cc00;'>KÜLDHETŐ!!!</strong>" : "";
								$stat["eventListForWatch"][$event->id][] = [
									"inEventIDs?" => (!in_array($event->id, $eventIDs)) ? "No - jó" : "Yes",
									"previousEmailEmpty?" => (empty($email->previousEmail)) ? "Yes - jó" : "No",
									"IsPrevEmail AND inEventsFromLogArray?" => (!empty($email->previousEmail) AND in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])) ? "Yes - jó" : "No",
									"sendable" => $sendable,
								];
								$stat["eventListForWatchCount"]++;
							}
							$stat["events"][] = $event->id;
							#Email hasn't been sent AND previos email has been sent!
							if(!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])))
							{
								#Store in return array
								$return[$email->id]["eventsForSend"][] = $event->id;
								$eventDetails = $this->getEvent($event->id, false, true);
								
								#Create email
								// if($event->id == 298) { include(DIR_VIEWS."emails/crm/newcarSellingEmail.php"); $this->model->newEmailLog($email->id, $event->id); }
								$this->model->newEmailLog($email->id, $event->id);
							}
						}
					}
				}
			}
		}
		
		#Return
		// $return["stat"] = $stat;
		return $return;
	}
	
	#Get event status
	public function getEventStatus($id)
	{
		$row = $this->model->getEventStatus($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			#Return array
			$return = [];
			$return["data"] = $row;
			
			#Basic datas
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["orderNumber"] = $row->orderNumber;
			
			#Other datas
			$return["inList"] = ($row->inList) ? true : false;
			$return["successValue"] = ($row->successValue) ? true : false;
		}
		else { $return = false; }
		
		return $return;
	}
	
	#Get event statuses
	public function getEventStatuses($inList = NULL, $successValue = NULL, $key = "id", $deleted = 0, $orderBy = "orderNumber")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }

		$rows = $this->model->getEventStatuses($inList, $successValue, $fields, $deleted, $orderBy);
		
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getEventStatus($row->id); 
			}
		}
		return $return;
	}
	
	#New event status change
	public function newEventStatusChange($eventID, $statusID, $comment = NULL)
	{
		$params = [
			"user" => (defined("USERID")) ? USERID : NULL,
			"date" => date("Y-m-d H:i:s"),
			"event" => $eventID,
			"status" => $statusID,
			"comment" => $comment,
		];
		return $this->model->myInsert($this->model->tables("eventStatusChanges"), $params);
	}
	
	#Get event status change
	public function getEventStatusChange($id)
	{
		$row = $this->model->getEventStatusChange($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			#Return array
			$return = [];
			$return["data"] = $row;
			
			#Basic datas
			$return["id"] = $row->id;
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($return["date"]));
			$return["comment"] = $row->comment;
			$return["userID"] = $row->user;
			$return["eventID"] = $row->event;
			$return["statusID"] = $row->status;
			
			#Other datas
			if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
			$return["user"] = $GLOBALS["users"]->getUser($return["userID"], false);
			$return["userName"] = (!empty($return["user"]["id"])) ? $return["user"]["name"] : "";
			$return["status"] = $this->getEventStatus($return["statusID"]);
			$return["statusName"] = ($return["status"] !== false) ? $return["status"]["name"] : "";
		}
		else { $return = false; }
		
		return $return;
	}
	
	#Get event status changes
	public function getEventStatusChanges($eventID, $status = NULL, $key = "id", $deleted = 0, $orderBy = "date DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }

		$rows = $this->model->getEventStatusChanges($eventID, $status, $fields, $deleted, $orderBy);
		
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getEventStatusChange($row->id); 
			}
		}
		return $return;
	}
	
	public function getEventsForDailyReport()
	{
		$dateString = date("Y-m-d");
		$dateOut = date("Y. m. d.", strtotime($dateString));
		$dateString .= "%";
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND date LIKE :date ORDER BY event, date", ["date" => $dateString]);
		
		$stats = [];
		if(!empty($rows))
		{
			$emailController = new \App\Http\Controllers\EmailController;
			$userController = new \App\Http\Controllers\UserController;			
			$allUsers = $allEvents = [];
			$allStatuses = $this->getEventStatuses();
			
			$fullReport = true;
			include(DIR_VIEWS."emails/crm/newcarSellingEmailDailyReport.php"); 
		}
		return ["rows" => $rows, "stats" => $stats];
	}
	
	public function getEventsForDailyReportBrands()
	{
		$dateString = date("Y-m-d");
		$dateOut = date("Y. m. d.", strtotime($dateString));
		$dateString .= "%";
		
		$return = [];
		$brands = $this->model->select("SELECT brand FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY brand ORDER BY brand");
		foreach($brands AS $brand)
		{
			$rows = $this->model->select("SELECT s.* FROM ".$this->model->tables("eventStatusChanges")." s INNER JOIN ".$this->model->tables("events")." e ON s.event = e.id WHERE e.del = '0' AND s.del = '0' AND e.brand = :brand AND s.date LIKE :date ORDER BY event, date", ["date" => $dateString, "brand" => $brand->brand]);
			
			$stats = [];
			if(!empty($rows))
			{
				$emailController = new \App\Http\Controllers\EmailController;
				$userController = new \App\Http\Controllers\UserController;			
				$allUsers = $allEvents = [];
				$allStatuses = $this->getEventStatuses();
				
				$fullReport = false;
				include(DIR_VIEWS."emails/crm/newcarSellingEmailDailyReport.php"); 
				$return[$premise->premise] = ["rows" => $rows, "stats" => $stats];
			}
		}
		
		return $return;
	}
	
	public function getEventsForMonthlyReport()
	{
		#Date
		$dateString = date("Y-m", strtotime("-1 month"));
		$dateOut = strftime("%Y. %B", strtotime($dateString));
		$dateString .= "-%";
		
		#Premises
		$premises = [];
		$rows = $this->model->select("SELECT premise FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY premise ORDER BY premise");
		foreach($rows AS $row) { $premises[] = $row->premise; }
		
		#Admins
		$admins = [];
		$rows = $this->model->select("SELECT adminName FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY adminName ORDER BY adminName");
		foreach($rows AS $row) { $admins[] = $row->adminName; }
		
		#Questionnaires
		$questionnaireController = new \App\Http\Controllers\QuestionnaireController;
		$questionnaires = [];
		foreach($this->questionnaireIDs AS $brand => $qID) { $questionnaires[$qID] = ["questionnaire" => $questionnaireController->getQuestionnaire($qID), "brand" => $brand]; }
		
		#Event statuses
		$stats = [];
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND date LIKE :date ORDER BY event, date", ["date" => $dateString]);
		if(!empty($rows))
		{
			$emailController = new \App\Http\Controllers\EmailController;
			$userController = new \App\Http\Controllers\UserController;			
			$customerController = new \App\Http\Controllers\CustomerController;			
			$allUsers = $allEvents = [];
			$allStatuses = $this->getEventStatuses();
			
			include(DIR_VIEWS."emails/crm/newcarSellingEmailMonthlyReport.php"); 
		}
		return ["rows" => $rows, "stats" => $stats];
	}
	
	#Mito export: get rows for CSV
	public function mitoExport($datas, $charset = "iso-8859-2")
	{
		if(!isset($datas["brand"]) OR empty($datas["brand"])) { $return = false; }
		else
		{
			#Get questionnaire URL by brand
			$questionnaireURL = "uj-auto-eladas-visszajelzes-2022";
			switch($datas["brand"])
			{
				case "hyundai":
				case "kia":
				case "nissan":
				case "peugeot":
				case "infiniti":
				case "citroen":
					$brandName = $datas["brand"];
					// $questionnaireURL .= "-".$datas["brand"];
					break;
				case "general":
					$brandName = "markafuggetlen";
					break;
				default:
					$return = false;
					break;
			}
			
			if($return !== false)
			{
				$qForm = new \App\Http\Controllers\QuestionnaireController;	
				$qRow = $qForm->getQuestionnaireByURL($questionnaireURL);
				if($qRow === false) { $return = false; }
				else
				{
					#Return
					$return = [
						"brand" => $datas["brand"],
						"brandName" => $brandName,
						"questionnaireURL" => $questionnaireURL,
						"charset" => $charset,
						"fileName" => "auto_eladasok_".$brandName."_".date("YmdHis").".csv",
						"headerRow" => [],
						"rows" => [],
						"rowCount" => 0,
					];
					
					#Date FROM and TO
					$dateFrom = (isset($datas["dateFrom"]) AND !empty($datas["dateFrom"])) ? $datas["dateFrom"] : date("Y-m-d 00:00:00", strtotime("-5 days"));
					$dateTo = (isset($datas["dateTo"]) AND !empty($datas["dateTo"])) ? $datas["dateTo"] : date("Y-m-d H:i:s");
					
					if(isset($datas["correctDates"]) AND $datas["correctDates"])
					{
						$dateFrom = date("Y-m-d 00:00:00", strtotime($datas["dateFrom"]));
						$dateTo = date("Y-m-d 23:59:59", strtotime($dateTo));
					}
					
					#Premise
					$premises = (isset($datas["premise"]) AND !empty($datas["premise"])) ? [$datas["premise"]] : [];
					
					#Header
					$headerItems = [
						
						"Ügyfél e-mail", "Ügyfél telefonszám", "Ügyfél mobil",
						"Alvázszám", "KM.óra",
					];
					$headerItems = ["Telephely", "Autó gyártmány", "Autó modell", "Rögzítve", "Eladás dátuma", "Partner név", "Vezető neve", "Értékesítő", "Rendszám", "Kitöltés megjegyzés"];
					$headerRow = [];
					foreach($headerItems AS $headerItem) { $headerRow[] = iconv("utf-8", $charset, $headerItem); }

					foreach($qRow["questions"] AS $questionID => $question)
					{
						if($qRow["inputTypes"][$question["data"]->inputType]["url"] != "hidden") { $headerRow[] = iconv("utf-8", $charset, $question["question"]); }
					}
					
					$return["headerRow"] = $headerRow;
					
					#Rows
					$customers = new \App\Http\Controllers\CustomerController;
					$eventList = $this->getEventsForExport($datas["brand"], $dateFrom, $dateTo, $premises);
					
					$i = 0;
					foreach($eventList AS $eventID => $event)
					{
						$customer = $customers->getCustomer($event["data"]->customer, false);
						$car = $customers->getCar($event["data"]->car, false);
						
						$statusRows = $this->model->select("SELECT id, comment FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND event = :event ORDER BY id DESC  LIMIT 0, 1", ["event" => $event["id"]]);
						$comment = (!empty($statusRows) AND isset($statusRows[0]) AND isset($statusRows[0]->id) AND !empty($statusRows[0]->id)) ? $statusRows[0]->comment : "";
						
						$answer = (!empty($event["data"]->questionnaireAnswer)) ? $qForm->getAnswer($event["data"]->questionnaireAnswer, false) : false;

						$row = [
							iconv("utf-8", $charset, $event["premise"]),
							iconv("utf-8", $charset, $car["brand"]),
							iconv("utf-8", $charset, $car["model"]),
							iconv("utf-8", $charset, $event["dateOut"]),
							iconv("utf-8", $charset, $event["dateSellingPublic"]),
							iconv("utf-8", $charset, $customer["name"]),
							iconv("utf-8", $charset, ""),
							iconv("utf-8", $charset, $event["adminName"]),
							iconv("utf-8", $charset, $car["regNumber"]),
							iconv("utf-8", $charset, $comment),
						];
						foreach($qRow["questions"] AS $questionID => $question)
						{
							if($qRow["inputTypes"][$question["data"]->inputType]["url"] != "hidden")
							{ 
								if($answer !== false) { $row[] = preg_replace('#<[^>]+>#', ' ', iconv("utf-8", $charset, $answer["answers"][$question["id"]]["val"])); }
								else { $row[] = ""; }
							}
						}
						$return["rows"][] = $row;
						$i++;
					}
					
					$return["rowCount"] = $i;
				}
			}
		}
		
		return $return;
	}
	
	#Mito export: create CSV files
	public function getEventsForMitoExport($days = 5)
	{
		#Date
		$timeString = strtotime("-".$days." days");
		$dateFrom = date("Y-m-d 00:00:00", $timeString);
		$dateOut = date("Y. m. d.", $timeString);
		
		#Brands
		$return = [
			"date" => $dateOut,
			"brands" => [],
		];
		$brands = ["hyundai", "kia", "nissan", "peugeot", "infiniti", "citroen", "general"];
		foreach($brands AS $brand)
		{
			#Get export
			$datas = [
				"brand" => $brand, 
				"dateFrom" => $dateFrom,
			];
			$export = $this->mitoExport($datas);
			
			if($export === false) { $returnData = $return["brands"][$brand] = false; }
			else
			{
				#Store data
				$returnData = $return["brands"][$brand] = [
					"dateOut" => $dateOut,
					"brand" => $brand,
					"brandName" => $export["brandName"],
					"questionnaireURL" => $export["questionnaireURL"],
					"rowCount" => $export["rowCount"],
					"fileName" => $export["fileName"],
					"filePathInner" => NULL,
				];
				
				#Save export files
				$dir = base_path("_temp_mito_export_files")."/";
				$returnData["filePathInner"] = $return["brands"][$brand]["filePathInner"] = $fileName = $dir.$returnData["fileName"];
				
				$file = fopen($fileName, "w");			
				fputcsv($file, $export["headerRow"], ";");
				foreach($export["rows"] AS $row) { fputcsv($file, $row, ";"); }			
				fclose($file);
			}
		}
		
		#Return
		return $return;
	}
	
	#Mito export: send email VIA cron
	public function mitoEmail($days = 5)
	{
		#Brands
		$return = $this->getEventsForMitoExport($days);
		
		#E-mail
		$emailController = new \App\Http\Controllers\EmailController;
		include(DIR_VIEWS."emails/crm/newcarSellingEmailMitoExport.php"); 
		
		#Return
		return $return;
	}
	
	#Todo export (print)
	public function todoExport($datas, $charset = "iso-8859-2")
	{
		if(!isset($datas["questionnaire"]) OR empty($datas["questionnaire"])) { $return = false; }
		elseif(!isset($datas["eventList"]) OR empty($datas["eventList"])) { $return = false; }
		else
		{
			#Return
			$return = [
				"questionnaire" => $datas["questionnaire"],
				"brand" => $datas["brand"],
				"panelBrand" => $datas["panelBrand"],
				"charset" => $charset,
				"fileName" => "auto_eladas_teendok_".$datas["brand"]."_".date("YmdHis").".csv",
				"headerRow" => [],
				"rows" => [],
				"rowCount" => 0,
			];
			$qRow = $datas["questionnaire"];

			#Header
			$headerItems = ["Dátum", "Eladás dátuma", "Rendszám", "Partnernév", "Vezető neve", "Telefon", "Értékesítő", "Belső megjegyzés", "Állapot"];
			$headerRow = [];
			foreach($headerItems AS $headerItem) { $headerRow[] = iconv("utf-8", $charset, $headerItem); }

			$j = 1;
			foreach($qRow["questions"] AS $questionID => $question)
			{
				if($qRow["inputTypes"][$question["data"]->inputType]["url"] != "hidden") 
				{ 
					$headerRow[] = iconv("utf-8", $charset, $j.". ".$question["question"]." K".$j);
					$j++;					
				}
			}
			
			$return["headerRow"] = $headerRow;
			
			#Rows
			$customers = new \App\Http\Controllers\CustomerController;
			$eventStatuses = $this->getEventStatuses();

			$i = 0;
			foreach($datas["eventList"] AS $eventID => $event)
			{
				$customer = $customers->getCustomer($event["data"]->customer, false);
				$car = $customers->getCar($event["data"]->car, false);
				
				$status = (!empty($event["statusID"]) AND isset($eventStatuses[$event["statusID"]])) ? $eventStatuses[$event["statusID"]]["name"] : "";
				
				$row = [
					iconv("utf-8", $charset, $event["dateOut"]),
					iconv("utf-8", $charset, $event["dateSellingPublic"]),
					iconv("utf-8", $charset, $car["regNumber"]),
					iconv("utf-8", $charset, $customer["name"]),
					iconv("utf-8", $charset, ""),
					iconv("utf-8", $charset, $customer["mobile"]),
					iconv("utf-8", $charset, ""),
					iconv("utf-8", $charset, $event["adminName"]),
					iconv("utf-8", $charset, ""),
					iconv("utf-8", $charset, $status),
				];
				foreach($qRow["questions"] AS $questionID => $question)
				{
					if($qRow["inputTypes"][$question["data"]->inputType]["url"] != "hidden") { $row[] = ""; }
				}
				$return["rows"][] = $row;
				$i++;
			}
			
			$return["rowCount"] = $i;
		}
		
		return $return;
	}
	
	#Statistics
	public function statEventAdmins($dateFrom = NULL, $dateTo = NULL)
	{
		#Basic
		$return = [
			"brands" => [],
			"global" => [
				"eventCount" => 0,
				"answerCount" => 0,
				"answerCountAnswers" => 0,
				"answerSum" => 0,
				"answerAvg" => 0,
				"emptyEmail" => 0,
				"emptyPhone" => 0,
				"emptyBoth" => 0,
			],
		];
		
		if(empty($dateFrom)) 
		{ 
			$minusDaysForMonday = date("N") - 1;	
			$dateFrom = date("Y-m-d 00:00:00", strtotime("-".$minusDaysForMonday." days")); 
		}
		if(empty($dateTo)) { $dateTo = date("Y-m-d 23:59:59"); }
		
		$questionnaires = new \App\Http\Controllers\QuestionnaireController;
		
		#Events
		$brands = ["nissan", "hyundai", "kia", "peugeot", "infiniti", "citroen", "márkafüggetlen"];
		foreach($brands AS $brand)
		{
			#Create brand array
			$return["brands"][$brand] = [
				"admins" => [],
				"total" => [
					"eventCount" => 0,
					"answerCount" => 0,
					"answerCountAnswers" => 0,
					"answerSum" => 0,
					"answerAvg" => 0,
					"emptyEmail" => 0,
					"emptyPhone" => 0,
					"emptyBoth" => 0,
				],
			];
			
			#Query
			$queryStart = "SELECT * FROM ".$this->model->tables("events")." WHERE del = '0' AND date >= :dateFrom AND date <= :dateTo";
			$params = [
				"dateFrom" => $dateFrom,
				"dateTo" => $dateTo,
			];
			
			if($brand == "márkafüggetlen")
			{
				$query = $queryStart;
				foreach($brands AS $brandItem) 
				{ 
					if($brandItem != $brand) { $query .= " AND brand != '".$brandItem."'"; } 
				}
			}
			else { $query = $queryStart." AND brand = '".$brand."'"; }
			$events = $this->model->select($query, $params);
			
			#Loop events
			foreach($events AS $event)
			{
				#Create admin array
				if(!isset($return["brands"][$brand]["admins"][$event->adminName])) 
				{ 
					$return["brands"][$brand]["admins"][$event->adminName] = [
						"eventCount" => 0,
						"answerCount" => 0,
						"answerCountAnswers" => 0,
						"answerSum" => 0,
						"answerAvg" => 0,
						"emptyEmail" => 0,
						"emptyPhone" => 0,
						"emptyBoth" => 0,
					]; 
				}
				
				#Global data
				$return["brands"][$brand]["admins"][$event->adminName]["eventCount"]++;
				$return["brands"][$brand]["total"]["eventCount"]++;
				$return["global"]["eventCount"]++;
				
				#E-mail and phone
				if(!$event->marketingDisabled)
				{
					$allDetails = $this->jsonDecode($event->allDetails);
					
					$phoneEmptyCount = 0;
					$phoneFields = ["telefonszám"];
					foreach($phoneFields AS $phoneField)
					{
						if(empty($allDetails[$phoneField]) OR mb_strlen($allDetails[$phoneField], "UTF-8") < 7 OR mb_substr_count($allDetails[$phoneField], "1", "UTF-8") >= 7) { $phoneEmptyCount++; }
					}
					$phoneEmpty = ($phoneEmptyCount == count($phoneFields));
					
					$emailField = "email cím";				
					if(empty($allDetails[$emailField]) OR filter_var($allDetails[$emailField], FILTER_VALIDATE_EMAIL) === false) { $emailEmpty = true; }
					else { $emailEmpty = false; }
					
					
					if($emailEmpty AND $phoneEmpty) 
					{
						$return["brands"][$brand]["admins"][$event->adminName]["emptyBoth"]++;
						$return["brands"][$brand]["total"]["emptyBoth"]++;
						$return["global"]["emptyBoth"]++;
					}
					elseif($emailEmpty) 
					{
						$return["brands"][$brand]["admins"][$event->adminName]["emptyEmail"]++;
						$return["brands"][$brand]["total"]["emptyEmail"]++;
						$return["global"]["emptyEmail"]++;
					}
					elseif($phoneEmpty)
					{
						$return["brands"][$brand]["admins"][$event->adminName]["emptyPhone"]++;
						$return["brands"][$brand]["total"]["emptyPhone"]++;
						$return["global"]["emptyPhone"]++;
					}
				}
				
				#Questionnaire answer
				if($event->questionnaireAnswer > 0)
				{
					$answer = $questionnaires->getAnswer($event->questionnaireAnswer, false);
					if($answer !== false)
					{
						$return["brands"][$brand]["admins"][$event->adminName]["answerCount"]++;
						$return["brands"][$brand]["total"]["answerCount"]++;
						$return["global"]["answerCount"]++;
						
						$return["brands"][$brand]["admins"][$event->adminName]["answerCountAnswers"] += $answer["answerCount"];
						$return["brands"][$brand]["total"]["answerCountAnswers"] += $answer["answerCount"];
						$return["global"]["answerCountAnswers"] += $answer["answerCount"];
						
						$return["brands"][$brand]["admins"][$event->adminName]["answerSum"] += $answer["answerSum"];
						$return["brands"][$brand]["total"]["answerSum"] += $answer["answerSum"];
						$return["global"]["answerSum"] += $answer["answerSum"];
					}
				}
			}
			
			#Loop users for answer avg-s
			$return["brands"][$brand]["total"]["answerAvg"] = (!empty($return["brands"][$brand]["total"]["answerCountAnswers"])) ? $return["brands"][$brand]["total"]["answerSum"] / $return["brands"][$brand]["total"]["answerCountAnswers"] : "-";
			foreach($return["brands"][$brand]["admins"] AS $adminName => $datas) { $return["brands"][$brand]["admins"][$adminName]["answerAvg"] = (!empty($datas["answerCountAnswers"])) ? $datas["answerSum"] / $datas["answerCountAnswers"] : "-"; }
		}
		
		#Answer avg-s
		$return["global"]["answerAvg"] = (!empty($return["global"]["answerCountAnswers"])) ? $return["global"]["answerSum"] / $return["global"]["answerCountAnswers"] : "-";
		
		#Output
		return $return;
	}
	
	public function statEventsWeekly()
	{
		#Date
		$dateFrom = date("Y-m-d 00:00:00", strtotime("monday last week"));
		$dateTo = date("Y-m-d 23:59:59", strtotime("sunday last week"));
		$dateOut = date("Y. m. d.", strtotime($dateFrom))." - ".date("Y. m. d.", strtotime($dateTo));
		
		$datas = $this->statEventAdmins($dateFrom, $dateTo);
		if(!empty($datas["global"]["eventCount"])) { include(DIR_VIEWS."emails/crm/newcarSellingEmailWeeklyStats.php"); }
		return $datas["global"];
	}
	
	public function statEventsMonthly()
	{
		#Date
		$dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month"));
		$dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom));
		$dateOut = date("Y. m. d.", strtotime($dateFrom))." - ".date("Y. m. d.", strtotime($dateTo));
		
		$datas = $this->statEventAdmins($dateFrom, $dateTo);
		if(!empty($datas["global"]["eventCount"])) { include(DIR_VIEWS."emails/crm/newcarSellingEmailMonthlyStats.php"); }
		return $datas["global"];
	}
	
	#Statistics
	public function statEventFillings($dateFrom = NULL, $dateTo = NULL)
	{
		#Basic
		$return = [
			"dateFrom" => NULL,
			"dateTo" => NULL,
			"all" => 0,
			"gablini" => 0,
			"fleet" => 0,
			"emailSent" => 0,
			
			"emptyEmail" => 0,
			"emptyPhone" => 0,
			"emptyBoth" => 0,
			
			"hasAnswer" => 0,
			"answerByCustomer" => 0,
			"answerByAdmin" => 0,
			"yetTodo" => 0,
			
			"statuses" => [
				"allChanges" => 0,
				"success" => 0,
			],
			
			"customersNotReached" => 0,
		];
		
		if(empty($dateFrom)) 
		{ 
			$minusDaysForMonday = date("N") - 1;	
			$dateFrom = date("Y-m-d 00:00:00", strtotime("-".$minusDaysForMonday." days")); 
		}
		if(empty($dateTo)) { $dateTo = date("Y-m-d 23:59:59"); }
		
		$return["dateFrom"] = date("Y. m. d.", strtotime($dateFrom));
		$return["dateTo"] = date("Y. m. d.", strtotime($dateTo));
		
		$statAdmin = $this->statEventAdmins($dateFrom, $dateTo);
		$return["emptyEmail"] = $statAdmin["global"]["emptyEmail"];
		$return["emptyPhone"] = $statAdmin["global"]["emptyPhone"];
		$return["emptyBoth"] = $statAdmin["global"]["emptyBoth"];
		
		#Query
		$params = [
			"dateFrom" => $dateFrom,
			"dateTo" => $dateTo,
		];		
		$events = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE del = '0' AND date >= :dateFrom AND date <= :dateTo", $params);
		
		#Loop events
		$questionnaires = new \App\Http\Controllers\QuestionnaireController;
		$gabliniCustomer = [3, 124, 3736]; // Gablini Prémium Kft. ; Autó Plussz kft. ; Gablini Kft.
		$statuses = $this->getEventStatuses();
		
		foreach($events AS $event)
		{
			if($event->marketingDisabled)
			{
				if(in_array($event->customer, $gabliniCustomer)) { $return["gablini"]++; }
				else { $return["fleet"]++; }
			}
			else
			{
				if(!empty($event->questionnaireAnswer) AND $event->questionnaireAnswer > 0) 
				{ 
					$return["hasAnswer"]++; 
					
					$answerUser = $questionnaires->model->getAnswer($event->questionnaireAnswer, "user");
					if(empty($answerUser)) { $return["answerByCustomer"]++; }
					else { $return["answerByAdmin"]++; }
				}
				
				if($event->adminTodo == 1 AND (!empty($event->status) OR $event->adminTodoDate <= $dateTo)) { $return["yetTodo"]++;  }
			}
			
			#Status changes
			$statusChanges = $this->model->getEventStatusChanges($event->id);
			if(!empty($statusChanges))
			{
				$return["statuses"]["allChanges"] += count($statusChanges);
				foreach($statusChanges AS $statusChange)
				{
					if($statuses[$statusChange->status]["successValue"]) { $return["statuses"]["success"]++; }
				}
			}
		}
		
		#Count: all, sent e-mail
		$return["all"] = count($events);
		$return["emailSent"] = $return["all"] - ($return["emptyEmail"] + $return["emptyBoth"] + $return["gablini"] + $return["fleet"]);
		$return["customersNotReached"] = $return["all"] - ($return["answerByCustomer"] + $return["statuses"]["success"] + $return["fleet"] + $return["gablini"]);
		
		#Output
		return $return;
	}
	
	public function statEventsFillingsMonthly()
	{
		#Date
		$dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month"));
		$dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom));
		
		$datas = $this->statEventFillings($dateFrom, $dateTo);
		$dateOut = $datas["dateFrom"]." - ".$datas["dateTo"];
		include(DIR_VIEWS."emails/crm/newcarSellingEmailFillingsMonthlyStats.php");
		return $datas;
	}
	
	public function eventsAstonMartinNotification($days = 3)
	{
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE date LIKE :date AND (brand LIKE '%aston%' AND brand LIKE '%martin%')", ["date" => date("Y-m-d", strtotime("-".$days." days"))." %"]);
		if(count($rows) > 0)
		{
			$customers = new \App\Customer;
			
			$events = [];
			foreach($rows AS $row)
			{
				$row->_allDetailsArray = $this->jsonDecode($row->allDetails);
				$row->_customer = $customers->getCustomer($row->customer);
				if(empty($row->_customer) OR !isset($row->_customer->id) OR empty($row->_customer->id)) { $row->_customer = new \stdClass; }
				$events[$row->id] = $row;
			}
			
			// include(DIR_VIEWS."emails/crm/newcarSellingEmailAstonMartinNotification.php");
		}
	}
	
	public function __globalStat20211125($dateFrom = "2021-01-01", $dateTo = NULL)
	{
		#Create return array
		$return = [
			"datas" => [
				"admins" => [],
				"brands" => [],
				"months" => [],
			],
			"stats" => [
				"admins" => "",
				"brands" => "",
				"months" => "",
			],
		];
		
		#Get events
		$query = "SELECT * FROM ".$this->model->tables("events")." WHERE del = '0'";
		$params = [];
		
		if($dateFrom !== NULL)
		{
			$query .= " AND date >= :dateFrom";
			$params["dateFrom"] = $dateFrom." 00:00:00";
		}
		if($dateTo !== NULL)
		{
			$query .= " AND date <= :dateTo";
			$params["dateTo"] = $dateTo." 23:59:59";
		}
		
		$events = $this->model->select($query, $params);
		
		#Loop events
		if(count($events) > 0)
		{
			$customersModel = new \App\Customer;
			$questionnairesModel = new \App\Questionnaire;
			foreach($events AS $event)
			{
				$month = date("Y-m", strtotime($event->date));
				$brand = mb_convert_case($event->brand, MB_CASE_TITLE, "utf-8");
				$admin = $event->adminName;
				
				#Store datas by admin --------------------------------------------------------------------------------------------------------------
					if(!isset($return["datas"]["admins"][$admin]))
					{
						$return["datas"]["admins"][$admin] = [
							"name" => $admin,
							"brands" => [],
							"months" => [],
							"stats" => [
								"sum" => 0,
								"marketingDisabled" => 0,
								"answeredByCustomer" => 0,
								"answeredByCallCenter" => 0,
								"noAnswer" => 0,
							],
						];
					}
					
					#Brand and month
					if(!in_array($brand, $return["datas"]["admins"][$admin]["brands"])) { $return["datas"]["admins"][$admin]["brands"][] = $brand; }
					if(!in_array($month, $return["datas"]["admins"][$admin]["months"])) { $return["datas"]["admins"][$admin]["months"][] = $month; }
					
					#Sum stat
					$return["datas"]["admins"][$admin]["stats"]["sum"]++;
					
					#Marketing disabled?
					$marketingDisabled = false;
					if($event->marketingDisabled) { $marketingDisabled = true; }
					else
					{
						$allDetails = $this->jsonDecode($event->allDetails);
						if(isset($allDetails["progressKód"]) AND !empty($allDetails["progressKód"]))
						{
							$marketingDisabledCustomers = $customersModel->select("SELECT id FROM ".$customersModel->tables("marketingDisabled")." WHERE progressCode = :progressCode", ["progressCode" => $allDetails["progressKód"]]);
							if(count($marketingDisabledCustomers) > 0) { $marketingDisabled = true; }
						}
					}
					
					#Answer exists?
					if($marketingDisabled) { $return["datas"]["admins"][$admin]["stats"]["marketingDisabled"]++; }
					else
					{
						$statName = "noAnswer";
						if(!empty($event->questionnaireAnswer))
						{
							$answers = $questionnairesModel->select("SELECT id, customer, foreignKey, user FROM ".$questionnairesModel->tables("answers")." WHERE id = :id", ["id" => $event->questionnaireAnswer]);
							if(count($answers) > 0) { $statName = (!empty($answers[0]->user)) ? "answeredByCallCenter" : "answeredByCustomer"; }
						}
						$return["datas"]["admins"][$admin]["stats"][$statName]++;
					}
				
				
				#Store datas by month --------------------------------------------------------------------------------------------------------------
				
				#Store datas by brand --------------------------------------------------------------------------------------------------------------
			}
		}
		
		#Stat by admins --------------------------------------------------------------------------------------------------------------
		setlocale(LC_ALL, "hu_HU.utf8");
		ksort($return["datas"]["admins"], SORT_LOCALE_STRING);
		
		$return["stats"]["admins"] = "Munkatárs\tMárkák\tMarketing tiltólistás\tKérdőív online kitöltve\tKérdőív Call Center által kitöltve\tNINCS kitöltés (teendős)\tÖSSZESEN<br>";
		foreach($return["datas"]["admins"] AS $adminName => $admin)
		{
			$return["stats"]["admins"] .= $admin["name"]."\t".implode(", ", $admin["brands"])."\t".$admin["stats"]["marketingDisabled"]."\t".$admin["stats"]["answeredByCustomer"]."\t".$admin["stats"]["answeredByCallCenter"]."\t".$admin["stats"]["noAnswer"]."\t".$admin["stats"]["sum"]."<br>";
		}
		
		#Stat by months --------------------------------------------------------------------------------------------------------------
		
		#Stat by brands --------------------------------------------------------------------------------------------------------------
		
		#Return
		return $return;
	}
	
	#JSON encode and decode
	public function json($array)
	{
		return $this->model->json($array);
	}
	
	public function jsonDecode($string)
	{
		return $this->model->jsonDecode($string);
	}
}
