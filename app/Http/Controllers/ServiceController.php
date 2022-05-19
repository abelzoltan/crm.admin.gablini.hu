<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SiteController;
use App\Service;

class ServiceController extends BaseController
{
	public $hashSaltBefore = "L3BtnLMvAe";
	public $hashSaltAfter = "QDth4Et8yE";
	public $tempDir = "_temp_import_files/";
	public $questionnaireIDs = [
		"nissan" => 16,
		"hyundai" => 16,
		"kia" => 16,
		"peugeot" => 16,
		"infiniti" => 16,
		"general" => 16,
		"citroen" => 16,
	];
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Service";
		$this->model = new \App\Service($connectionData);
		$this->tempDir = base_path()."/".$this->tempDir;
		
		if(!isset($GLOBALS["site"])) { $GLOBALS["site"] = new SiteController; }
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
			"existing-event" => "Már létezik esemény azonos munkalapszámmal.",
			"existing-event-invoice" => "Már létezik esemény azonos számlaszámmal.",
			"no-valid-email" => "Nincs valid e-mail cím.",
			"no-customer" => "Ügyfél nem található és nem hozható létre.",
			"unidentifyable-car" => "Hiányzó rendszám / alvázszám.",
			"no-car" => "Autó nem található és nem hozható létre.",
			
			#Success messages
			"success-import" => "A fájl minden követelménynek megfelelt, az importálás sikeresen megtörtént.",
			"success-event" => "A szerviz-eseményt sikeresen rögzítette a rendszer.",
		];
		
		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Set hash
	public function setHash($id, $date, $import, $sheetNumber)
	{
		return sha1($this->hashSaltBefore."-".$id."-".$date."-".$import."-".$sheetNumber."-".$this->hashSaltAfter);
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
						
						if(!empty($data["eventID"])) { $row["basic"]["eventID"] = ["name" => "Szerviz esemény ID", "value" => $data["eventID"]]; }
						
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
			
			$return["adminTodoDate"] = (!empty($event->adminTodoDate) AND $event->adminTodoDate != "0000-00-00 00:00:00") ? $event->adminTodoDate : NULL;
			$return["adminTodoDateOut"] = (!empty($return["adminTodoDate"])) ? date("Y. m. d.", strtotime($return["adminTodoDate"])) : "";
			$return["adminTodoSuccess"] = ($event->adminTodoSuccess) ? true : false;
			
			#Other Datas
			$return["hash"] = $event->hash;			
			$return["text"] = $event->text;
			$return["textHTML"] = nl2br($return["text"]);
			
			$return["statusID"] = $event->status;
			
			$return["typeID"] = $event->type;
			if(!empty($return["typeID"]))
			{
				$return["type"] = $this->model->getEventType($return["typeID"]);
				$return["typeName"] = $return["type"]->name;
				$sheetNumberLabel = $return["type"]->sheetNumber;
				$adminNameLabel = $return["type"]->adminName;
			}
			else
			{
				$return["type"] = false;
				$return["typeName"] = "N/A";
				$sheetNumberLabel = "Munkalapszám / Ajánlat sorszáma";
				$adminNameLabel = "Munkatárs (Ügyintéző)";
			}
			
			$return["invoice"] = $event->invoice;
			$return["sheetNumber"] = $event->sheetNumber;
			$return["adminName"] = $event->adminName;
			$return["premise"] = $event->premise;
			$return["total"] = $event->total;
			$return["totalOut"] = (!empty($return["total"])) ? number_format($return["total"], 0, ",", " ")." Ft" : "";
			
			$return["score"] = $event->score;
			$return["scoreOut"] = (!empty($return["score"])) ? number_format($return["score"], 0, ".", " ")." pont" : "0 pont";
			
			$return["dateSetout"] = (!empty($event->dateSetout) AND $event->dateSetout != "0000-00-00") ? $event->dateSetout : NULL;
			$return["dateSetoutPublic"] = (!empty($return["dateSetout"])) ? date("Y. m. d.", strtotime($return["dateSetout"])) : "";
			
			$return["dateClosed"] = (!empty($event->dateClosed) AND $event->dateClosed != "0000-00-00") ? $event->dateClosed : NULL;
			$return["dateClosedPublic"] = (!empty($return["dateClosed"])) ? date("Y. m. d.", strtotime($return["dateClosed"])) : "";
			
			#Date form emails (needed??? - 20180720)
			if(!empty($event->dateSetout) AND $event->dateSetout != "0000-00-00") { $return["dateForEmails"] = $event->dateSetout; }
			elseif(!empty($event->dateClosed) AND $event->dateClosed != "0000-00-00") { $return["dateForEmails"] = $event->dateClosed; }
			elseif(!empty($event->date) AND $event->date != "0000-00-00 00:00:00") { $return["dateForEmails"] = date("Y-m-d", strtotime($event->date)); }
			else { $return["dateForEmails"] = date("Y-m-d", strtotime($event->created_at)); }
			
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
				if(!empty($return["userName"])) { $output["background"]["data"]["userName"] = ["name" => "Munkatárs", "val" => $return["userName"]]; }
				if(!empty($return["date"])) { $output["background"]["data"]["date"] = ["name" => "Rögzítés időpontja", "val" => $return["dateOut"]]; }
				if(!empty($return["customerName"])) { $output["background"]["data"]["customerName"] = ["name" => "Ügyfél név", "val" => $return["customerName"]]; }
				if(!empty($return["customerCode"])) { $output["background"]["data"]["customerCode"] = ["name" => $customers->codeName, "val" => $return["customerCode"]]; }
				if(!empty($return["carName"])) { $output["background"]["data"]["carName"] = ["name" => "Autó", "val" => $return["carName"]]; }
				if(!empty($return["importName"])) { $output["background"]["data"]["importName"] = ["name" => "Import fájlnév", "val" => $return["importName"]]; }
				if($return["questionnaire"] !== false) { $output["background"]["data"]["questionnaire"] = ["name" => "Csatolt kérdőív", "val" => $return["questionnaire"]["name"]." (".$return["questionnaire"]["code"].")"]; }
				
				#Event data				
				if(!empty($return["dateClosedPublic"])) { $output["event"]["data"]["dateClosed"] = ["name" => "Lezárás dátuma", "val" => $return["dateClosedPublic"]]; }
				if(!empty($return["dateSetoutPublic"])) { $output["event"]["data"]["dateSetout"] = ["name" => "Szerviz munkalap kiállításának dátuma", "val" => $return["dateSetoutPublic"]]; }
				if(!empty($return["textHTML"])) { $output["event"]["data"]["text"] = ["name" => "Hibaleírás", "val" => $return["textHTML"]]; }
				
				if(!empty($return["typeName"])) { $output["event"]["data"]["typeName"] = ["name" => "Típus", "val" => $return["typeName"]]; }
				if(!empty($return["invoice"])) { $output["event"]["data"]["invoice"] = ["name" => "Számla sorszáma", "val" => $return["invoice"]]; }
				if(!empty($return["sheetNumber"])) { $output["event"]["data"]["sheetNumber"] = ["name" => $sheetNumberLabel, "val" => $return["sheetNumber"]]; }
				if(!empty($return["adminName"])) { $output["event"]["data"]["adminName"] = ["name" => $adminNameLabel, "val" => $return["adminName"]]; }
				if(!empty($return["totalOut"])) { $output["event"]["data"]["total"] = ["name" => "Végösszeg", "val" => $return["totalOut"]]; }
				if(!empty($return["scoreOut"])) { $output["event"]["data"]["score"] = ["name" => "Eseményért járó pontok száma", "val" => $return["scoreOut"]]; }
				
				#All details
				$allDetails = $this->jsonDecode($event->allDetails);
				foreach($allDetails AS $detailKey => $detailVal) 
				{
					if(!empty($detailVal)) { $output["details"]["data"][$detailKey] = ["name" => $detailKey, "val" => $detailVal]; }
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
	
	public function getEvents($search = [], $customer = NULL, $key = "id", $deleted = 0, $orderBy = "dateSetout DESC, dateClosed DESC, date DESC, id DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		
		$rows = $this->model->getEvents($customer, $fields, $search, $deleted, $orderBy);
		
		if($rows AND count($rows) > 0)
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
			"type" => 1,
			"questionnaireAnswered" => false,
			"adminTodo" => true,
			"adminTodoDateToNow" => true,
			"questionnaireID" => $questionnaireID,
			"totalMin" => 0,
		];
		
		return $this->getEvents($search, NULL, $key, $deleted, $orderBy);
	}
	
	public function getEventsForExport($brand, $dateFrom, $dateTo, $premises = [], $orderBy = "dateClosed")
	{
		$return = [];
		$rows = $this->model->getEventsForExports($brand, $dateFrom, $dateTo, $premises, $orderBy);	
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) { $return[$row->id] = $this->getEvent($row->id, false, false);  }
		}
		return $return;
	}
	
	public function getEventsForTracking()
	{
		$return = [];
		$rows = $this->model->getEventsForTracking();	
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) { $return[$row->id] = $this->getEvent($row->id, false, false);  }
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
			$customers->log("services-event-new", NULL, NULL, ["systemText" => "Hiba: Nem létező ügyfél!", "json" => $customerID, "jsonNewDatas" => $params]);
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
			$customers->log("services-event-new", $return, $return, ["systemText" => $systemTextOnSuccess, "jsonNewDatas" => $params]);
			
			$hash = $this->setHash($return, $params["date"], $importID, $params["sheetNumber"]);
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
			$return = $this->progressImport($filePath, 0); 
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
	
	public function progressImport($filePath, $progressVal = 1)
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
							$appModel = new \App\App;
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
								if($rowCount > 0) // $rowCount == $fieldCount
								{
									#Create array from all datas
									foreach($fields AS $fieldIndex => $field) 
									{
										if(!isset($row[$fieldIndex])) { $row[$fieldIndex] = NULL; }
									}
									
									$allDetails = array_combine($fields, $row);
									
									#Important row fields
									$email = trim($row[7]);
									$phone = trim($row[5]);
									$mobile = trim($row[6]);									
									$mobileFinishedReport = trim($row[36]);									
									$name = trim($row[3]);
									$customerStatus = trim($row[41]);
									
									$progressCode = trim($row[2]);
									$progressCode = trim($progressCode, "'");
									$progressCode = trim($progressCode);
									$progressCode = trim($progressCode, "'");
									$progressCode = trim($progressCode); //multiple trim: for safe
									
									$regNumber = trim($row[26]);
									$bodyNumber = trim($row[29]);
									
									$brand = trim($row[27]);
									$modelName = trim($row[28]);
									$text = trim($row[38]);
									$premise = trim($row[0]);
									
									$dateSetout = trim($row[35]);
									if(!empty($dateSetout))
									{
										$dateSetout = str_replace([" ", "."], ["", "-"], $dateSetout);
										$dateSetout = date("Y-m-d", strtotime($dateSetout));
									}
									else { $dateSetout = NULL; }
									
									$dateClosed = trim($row[1]);
									if(!empty($dateClosed))
									{
										$dateClosed = str_replace([" ", "."], ["", "-"], $dateClosed);
										$dateClosed = date("Y-m-d", strtotime($dateClosed));
									}
									else { $dateSetout = NULL; }
									
									#Other row fields
									$score = trim($row[8]);
									if(empty($score)) { $score = 0; }
									
									$carKM = trim($row[34]);
									if(empty($carKM)) { $carKM = NULL; }
									
									$carCCM = trim($row[31]);
									if(empty($carCCM)) { $carCCM = NULL; }
									
									$carFuel = mb_convert_case(trim($row[30]), MB_CASE_LOWER, "utf-8");
									switch($carFuel)
									{
										case "benzin":
										case "benzines":
											$carFuelID = 1;
											break;
										case "dizel":
										case "dízel":
										case "disel":
										case "dísel":
										case "diesel":
										case "diezel":
											$carFuelID = 2;
											break;
										case "elektromos":
										case "electric":
											$carFuelID = 3;
											break;
										case "hybrid":
										case "hibrid":
										case "hybryd":
											$carFuelID = 4;
											break;	
										default:
											$carFuelID = NULL;
											break;
									}
									
									#Event type
									if(!empty($row[9])) { $typeCode = "javitas"; }
									elseif(!empty($row[13])) { $typeCode = "alkatresz"; }
									elseif(!empty($row[17])) { $typeCode = "uj-gepkocsi"; }
									elseif(!empty($row[21])) { $typeCode = "hasznalt-gepkocsi"; }
									else { $typeCode = NULL; }
									
									if(isset($typeCode) AND !empty($typeCode))
									{
										$type = $this->model->getEventTypeByURL($typeCode, "id");
										switch($typeCode)
										{
											case "javitas":
												$invoiceRow = 9;
												$totalRow = $invoiceRow + 1;
												$sheetNumberRow = $invoiceRow + 2;
												$adminRow = $invoiceRow + 3;
												break;
											case "alkatresz":
												$invoiceRow = 13;
												$totalRow = $invoiceRow + 1;
												$sheetNumberRow = $invoiceRow + 2;
												$adminRow = $invoiceRow + 3;
												break;
											case "uj-gepkocsi":
												$invoiceRow = 17;
												$totalRow = $invoiceRow + 1;
												$sheetNumberRow = $invoiceRow + 2;
												$adminRow = $invoiceRow + 3;
												break;	
											case "hasznalt-gepkocsi":
												$invoiceRow = 21;
												$totalRow = $invoiceRow + 1;
												$sheetNumberRow = $invoiceRow + 2;
												$adminRow = $invoiceRow + 3;
												break;
											default:
												$invoiceRow = $totalRow = $sheetNumberRow = $adminRow = NULL;
												break;
										}
										
										#Event datas by type
										if(!empty($invoiceRow))
										{
											$invoice = trim($row[$invoiceRow]);
											if(empty($invoice)) { $invoice = NULL; }
										}									
										if(!empty($sheetNumberRow))
										{
											$sheetNumber = trim($row[$sheetNumberRow]);
											if(empty($sheetNumber)) { $sheetNumber = NULL; }
										}
										if(!empty($adminRow))
										{
											$adminName = trim($row[$adminRow]);
											if(empty($adminName)) { $adminName = NULL; }
										}
										if(!empty($totalRow))
										{
											$total = trim($row[$totalRow]);
											if(empty($total)) { $total = NULL; }
										}
									}
									else { $type = $invoice = $sheetNumber = $adminName = $total = NULL; }
										
									#Check if event exists
									$existingEventID = $this->model->getEventByInvoice($invoice, "id");
									if(!empty($invoice) AND !empty($existingEventID) AND $existingEventID > 0)
									{
										$datas[$i]["success"] = false;
										$datas[$i]["msg"] = "existing-event-invoice";
									}
									else
									{											
										#Validate e-mail
										if(empty($email) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) { $email = false; }
										if($email === false) { $datas[$i]["msg"] = "no-valid-email"; }

										#Identify OR Create Customer
										if(!empty($progressCode)) { $customer = $customers->getCustomerByProgressCode($progressCode); }
										else { $customer = false; }
										
										#Phone
										if(!empty($mobileFinishedReport)) { $mobileHere = $customers->customersPhone($mobileFinishedReport); }
										elseif(!empty($mobile)) { $mobileHere = $customers->customersPhone($mobile); }
										else { $mobileHere = NULL; }
										
										$phoneHere = (!empty($phone)) ? $customers->customersPhone($phone) : NULL;
										
										#Name
										$nameDatas = $customers->importCustomersName($name);
										
										#Tire contract
										$tireContractNumber = (isset($row[42]) AND !empty($row[42])) ? trim($row[42]) : NULL;
										
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
												"phone" => $phoneHere,
												"mobile" => $mobileHere,
												"firstName" => $nameDatas["firstName"],
												"lastName" => $nameDatas["lastName"],
												"beforeName" => $nameDatas["beforeName"],
												"tireContractNumber" => $tireContractNumber,
											];
											
											#New customer
											$systemText = "Szerviz import; id: ".$importID.", adatsor ID: ".$i;
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
													"phone" => $phoneHere,
													"mobile" => $mobileHere,
													"firstName" => $nameDatas["firstName"],
													"lastName" => $nameDatas["lastName"],
													"beforeName" => $nameDatas["beforeName"],
													"tireContractNumber" => $tireContractNumber,
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
													"km" => (!empty($carKM)) ? $carKM : NULL,
													"ccm" => (!empty($carCCM)) ? $carCCM : NULL,
													"fuel" => (!empty($carFuelID)) ? $carFuelID : NULL,
												];
												
												#New car
												$systemText = "Szerviz import; id: ".$importID.", adatsor ID: ".$i;
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
												$notCallable = trim($row[39]);
												if(in_array($progressCode, $marketingDisabledCodeList)) { $marketingDisabled = 1; }
												elseif(in_array($customerID, $marketingDisabledIdList)) { $marketingDisabled = 1; }
												elseif(mb_strtolower(trim($email), "utf-8") == "info@gablini.hu") 
												{
													if(!empty($progressCode)) 
													{ 
														$customers->addMarketingDisabled($progressCode, "ServiceController::progressImport(), info@gablini.hu"); 
														$marketingDisabledCodeList[] = $progressCode;
													}
													$marketingDisabled = 1;
												}
												elseif($notCallable == "x" OR $notCallable == "X") 
												{
													/*if(!empty($progressCode)) 
													{ 
														$customers->addMarketingDisabled($progressCode, "ServiceController::progressImport(), notCallable"); 
														$marketingDisabledCodeList[] = $progressCode;
													}*/
													$marketingDisabled = 1;
												}
												elseif(strpos($brandInner, "aston") !== false) { $marketingDisabled = 1; }
												elseif($customerStatus == "Szervizflotta" OR $customerStatus == "szervizflotta") { $marketingDisabled = 1; }
												else { $marketingDisabled = 0; }
												
												$prevEvents = $this->model->select("SELECT id FROM ".$this->model->tables("events")." WHERE del = '0' AND (prevEvent IS NULL OR prevEvent = '0') AND dateSetout = :dateSetout AND customer = :customer AND type = :type AND brand = :brand", ["dateSetout" => $dateSetout, "customer" => $customerID, "type" => $type, "brand" => $brandInner]);
												$prevEvent = (count($prevEvents) > 0) ? $prevEvents[0]->id : NULL;
												
												$params = [
													"customerProgressCode" => $progressCode,
													"dateSetout" => $dateSetout,
													"dateClosed" => $dateClosed,
													"brand" => $brandInner,
													"text" => $text,
													"allDetails" => $this->json($allDetails),
													"questionnaire" => $questionnaire,
													"type" => $type,
													"invoice" => $invoice,											
													"sheetNumber" => $sheetNumber,											
													"adminName" => $adminName,											
													"premise" => $premise,											
													"total" => $total,
													"score" => $score,
													"marketingDisabled" => $marketingDisabled,
													"prevEvent" => $prevEvent,
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
												elseif($brandInner == "peugeot") { $params["adminTodo"] = 1; }
												
												if($marketingDisabled OR !empty($prevEvent)) { $params["adminTodo"] = 0; }
												$datas[$i]["eventID"] = $eventID = $this->newEvent($customerID, $carID, $importID, $params, NULL);
												
												#Create event for APP
												if(!empty($email))
												{
													$appEventParams = [
														"date" => date("Y-m-d H:i:s"),
														"name" => $name,
														"progressCode" => $params["customerProgressCode"],
														"email" => $email,
														"invoice" => $params["invoice"],
														"score" => $params["score"],
														"event" => $eventID,
														"customer" => $customerID,
													];
													$appModel->newEvent($appEventParams);
												}
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
				"fileName" => "progressImport() => ".$filePath,
				"errorMessage" => $return["errorMessage"],
			];
			include(DIR_VIEWS."emails/crm/serviceEmailImportError.php");
		}
		
		return $return;
	}
	
	#Get actual Progress CSV files
	public function progressFilesImport()
	{
		#Return data
		$return = [
			"ftpOK" => true,
			"error" => NULL,
			"files" => [],
			"fileCount" => 0,
			"actualFiles" => [],
			"actualFileCount" => 0,
		];
		
		#Get processed files
		$processedFiles = [];
		$fileNames = $this->model->getProgressImportFileNames();
		foreach($fileNames AS $fileNameRow) { $processedFiles[] = $fileNameRow->fileName; }
		
		#Connect to FTP
		$ftpConnection = ftp_connect("gablini.hu");
		if($ftpConnection !== false)
		{
			#Login to FTP
			if($ftpLogin = ftp_login($ftpConnection, env("FTP_USER"), env("FTP_PASSWORD")))
			{
				#Get CSV fiels from FTP
				$files = ftp_nlist($ftpConnection, ".");
				foreach($files AS $file)
				{
					if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) == "csv" AND mb_stripos($file, "gablini_szamlak_", 0, "utf-8") !== false)
					{
						#Log file
						$return["files"][] = $file;
						$return["fileCount"]++;
						
						#If actual
						$fileName = pathinfo($file, PATHINFO_BASENAME);
						$fileName2 = (substr($file, 0, 2) == "./") ? substr($file, 2) : $file;
						if(!in_array($file, $processedFiles) AND !in_array($fileName, $processedFiles) AND !in_array($fileName2, $processedFiles))
						{
							#Log actual file
							$return["actualFiles"][$fileName] = [
								"fileName" => $fileName,
								"error" => NULL,
								"import" => [],
							];
							$return["actualFileCount"]++;
							
							#Create local file and fill with content
							$localFile = $this->tempDir.$file;
							if(file_exists($localFile)) { unlink($localFile); }
							$fp = fopen($localFile, "w");
							$fileGet = ftp_fget($ftpConnection, $fp, $file, FTP_ASCII, 0);
							fclose($fp);
							
							#Process file
							if($fileGet)
							{
								#Service import
								$importReturn = $this->progressImport($localFile);
								$return["actualFiles"][$file]["import"]["id"] = $importReturn["id"];
								$return["actualFiles"][$file]["import"]["errorMessage"] = $importReturn["errorMessage"];
								$return["actualFiles"][$file]["import"]["file"] = $importReturn["file"];
								
								#Delete temp file
								unlink($localFile);
							}
							else { $return["actualFiles"][$file]["error"] = "download"; }
							
						}
					}
				}
				ftp_close($ftpConnection); 
			}
			else { $return["ftpOK"] = false; $return["error"] = "login"; }
		}
		else { $return["ftpOK"] = false; $return["error"] = "connection"; }
		
		#If error
		if(!$return["ftpOK"])
		{
			$emailData = [
				"fileName" => "progressFilesImport() FTP Error",
				"errorMessage" => $return["error"],
			];
			include(DIR_VIEWS."emails/crm/serviceEmailImportError.php");
		}
		elseif($return["actualFileCount"] == 0)
		{
			$emailData = [
				"fileName" => "progressFilesImport() No Actual File(s)",
				"errorMessage" => "actualFileCount == 0",
			];
			include(DIR_VIEWS."emails/crm/serviceEmailImportError.php");
		}
		
		#Return true
		return $return;
	}
	
	#Get service emails for cron
	public function emailsForCron()
	{
		#Return
		$return = [];
		
		#Get service emails
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
			foreach($logs AS $log) { $eventIDs[] = $log->serviceEvent; }
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
			$events = $this->model->getEventsForEmailSending($email->eventType, $email->dateField, $email->seconds, $email->brand, $mainBrands);
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
						// include(DIR_VIEWS."emails/crm/serviceEmailAdmin.php"); 
					}
				}
				#Customer email
				else
				{				
					foreach($events AS $event)
					{
						#Marketing disabled
						if(in_array($event->customer, $marketingDisabledIdList)) { $this->model->myUpdate($this->model->tables("events"), ["marketingDisabled" => 1], $event->id); }
						elseif($event->marketingDisabled) {  }
						else
						{
							#Email hasn't been sent AND previos email has been sent!
							if(!in_array($event->id, $eventIDs) AND (empty($email->previousEmail) OR in_array($event->id, $return[$email->previousEmail]["eventsFromLog"])))
							{
								#Store in return array
								$return[$email->id]["eventsForSend"][] = $event->id;
								$eventDetails = $this->getEvent($event->id, false, true);
								
								#Create email
								include(DIR_VIEWS."emails/crm/serviceEmail.php");
								$this->model->newEmailLog($email->id, $event->id);
							}
						}
					}
				}
			}
		}
		#Return
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
			$return["reportType"] = $row->reportType;
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
	
	public function getEventsForDailyReport($dateString = NULL)
	{
		if($dateString === NULL) { $dateString = date("Y-m-d"); }
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
			include(DIR_VIEWS."emails/crm/serviceEmailDailyReport.php"); 
		}
		return ["rows" => $rows, "stats" => $stats];
	}
	
	public function getEventsForDailyReportPremise()
	{
		$dateString = date("Y-m-d");
		$dateOut = date("Y. m. d.", strtotime($dateString));
		$dateString .= "%";
		
		$return = [];
		$premises = $this->model->select("SELECT premise FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY premise ORDER BY premise");
		foreach($premises AS $premise)
		{
			$rows = $this->model->select("SELECT s.* FROM ".$this->model->tables("eventStatusChanges")." s INNER JOIN ".$this->model->tables("events")." e ON s.event = e.id WHERE e.del = '0' AND s.del = '0' AND e.premise = :premise AND s.date LIKE :date ORDER BY event, date", ["date" => $dateString, "premise" => $premise->premise]);
			
			$stats = [];
			if(!empty($rows))
			{
				$emailController = new \App\Http\Controllers\EmailController;
				$userController = new \App\Http\Controllers\UserController;			
				$allUsers = $allEvents = [];
				$allStatuses = $this->getEventStatuses();
				
				$fullReport = false;
				include(DIR_VIEWS."emails/crm/serviceEmailDailyReport.php"); 
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
			
			include(DIR_VIEWS."emails/crm/serviceEmailMonthlyReport.php"); 
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
			$questionnaireURL = "szerviz-visszajelzes";
			switch($datas["brand"])
			{
				case "hyundai":
				case "kia":
				case "nissan":
				case "peugeot":
				case "infiniti":
				case "citroen":
					$brandName = $datas["brand"];
					$questionnaireURL .= "-".$datas["brand"];
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
						"fileName" => "szerviz_esemenyek_".$brandName."_".date("YmdHis").".csv",
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
					$headerItems = ["Mlapszám", "Alvázszám", "Dealer kód", "neve", "vezető", "Partnernév", "Par.Irsz", "Partner város", "Cím", "házszám", "Telefon/Fax", "", "E-mail", "Kategória", "F.rendszám", "Gar.kezd", "Lez.dát.", "KM.óra", "E-Mail", "Posta", "Telefon", "Adatkezelés", "Flotta", "P", "M", "E", "G", "", ""];
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
					$marketingDisabledList = $customers->getMarketingDisabledIdList();
					
					$i = 0;
					foreach($eventList AS $eventID => $event)
					{
						$customer = $customers->getCustomer($event["data"]->customer, false);
						$car = $customers->getCar($event["data"]->car, false);
						
						$statusRows = $this->model->select("SELECT id, comment FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND event = :event ORDER BY id DESC  LIMIT 0, 1", ["event" => $event["id"]]);
						$comment = (!empty($statusRows) AND isset($statusRows[0]) AND isset($statusRows[0]->id) AND !empty($statusRows[0]->id)) ? $statusRows[0]->comment : "";
						
						$answer = (!empty($event["data"]->questionnaireAnswer)) ? $qForm->getAnswer($event["data"]->questionnaireAnswer, false) : false;
						
						#Datas for export
						$allDetails = $this->jsonDecode($event["data"]->allDetails);
						
						$originalName = "";
						$name1 = $customer["data"]->firstName;
						$name2 = $customer["data"]->lastName;
						$name3 = $name1." ".$name2;
						$fleetName = "";
						
						if(!empty($allDetails["Ügyfél név"]))
						{
							$names = $customers->customersName($allDetails["Ügyfél név"]);
							$name1 = $names["firstName"];
							$name2 = $names["lastName"];
							$originalName = $name3 = $allDetails["Ügyfél név"];
						}
						if(!empty($allDetails["Kapcsolattartó neve"]))
						{
							$names = $customers->customersName($allDetails["Kapcsolattartó neve"]);
							$name1 = $names["firstName"];
							$name2 = $names["lastName"];
							$originalName = $allDetails["Ügyfél név"];
						}
						if(in_array($event["data"]->customer, $marketingDisabledList))
						{
							$fleetName = $name3;
							$name1 = $name2 = "Flotta";
						}
						
						$category = $customers->customersCategory($originalName);

						$phone = "";
						if(!empty($allDetails["Telefon 1"])) { $phone = $allDetails["Telefon 1"]; }
						if(!empty($allDetails["Mobil"])) { $phone = $allDetails["Mobil"]; }
						if(!empty($allDetails["Készrejelentés"])) { $phone = $allDetails["Készrejelentés"]; }
						
						$email = "";
						if(!empty($allDetails["vezető e-mail címe"])) { $email = $allDetails["vezető e-mail címe"]; }
						if(!empty($allDetails["E-mail"])) { $email = $allDetails["E-mail"]; }
						
						$km = $car["km"];
						if(!empty($allDetails["KM óra állás"])) { $km = $allDetails["KM óra állás"]; }
						
						$addressData = $customers->customersAddress($allDetails["Ügyfél cím"]);
						
						$row = [
							iconv("utf-8", $charset, $event["sheetNumber"]),
							iconv("utf-8", $charset, $car["bodyNumber"]),
							iconv("utf-8", $charset, "190123"),
							iconv("utf-8", $charset, $name1),
							iconv("utf-8", $charset, $name2),
							iconv("utf-8", $charset, $name3),
							iconv("utf-8", $charset, $addressData["zipCode"]),
							iconv("utf-8", $charset, $addressData["city"]),
							iconv("utf-8", $charset, $addressData["address"]),
							iconv("utf-8", $charset, $addressData["number"]),
							iconv("utf-8", $charset, $phone),
							iconv("utf-8", $charset, ""),
							iconv("utf-8", $charset, $email),
							iconv("utf-8", $charset, $category),
							iconv("utf-8", $charset, $car["regNumber"]),
							iconv("utf-8", $charset, ""), // gar. kezdete
							iconv("utf-8", $charset, $event["dateClosedPublic"]),
							iconv("utf-8", $charset, $km),
							iconv("utf-8", $charset, ""), // E-Mail
							iconv("utf-8", $charset, ""), // Posta
							iconv("utf-8", $charset, ""), // Telefon
							iconv("utf-8", $charset, ""), // Adatkezelés
							iconv("utf-8", $charset, $fleetName),
							iconv("utf-8", $charset, ""), // P
							iconv("utf-8", $charset, ""), // M
							iconv("utf-8", $charset, ""), // E
							iconv("utf-8", $charset, ""), // G
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
		// include(DIR_VIEWS."emails/crm/serviceEmailMitoExport.php"); 
		
		#Return
		return $return;
	}
	
	#Service todo export (print)
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
				"fileName" => "szerviz_teendok_".$datas["brand"]."_".date("YmdHis").".csv",
				"headerRow" => [],
				"rows" => [],
				"rowCount" => 0,
			];
			$qRow = $datas["questionnaire"];

			#Header
			$headerItems = ["Dátum", "Kiállítás dátuma", "Lezárás dátuma", "Munkalap", "Rendszám", "Partnernév", "Vezető neve", "Telefon/fax", "Készrejelentés", "Hiba", "Munkafelvevő", "Belső megjegyzés", "Állapot"];
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
					iconv("utf-8", $charset, $event["dateSetoutPublic"]),
					iconv("utf-8", $charset, $event["dateClosedPublic"]),
					iconv("utf-8", $charset, $event["sheetNumber"]),
					iconv("utf-8", $charset, $car["regNumber"]),
					iconv("utf-8", $charset, $customer["name"]),
					iconv("utf-8", $charset, ""),
					iconv("utf-8", $charset, $customer["phone"]),
					iconv("utf-8", $charset, ""),
					iconv("utf-8", $charset, $event["text"]),
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
				"questions" => [],
				"questionsUsed" => [],
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
			$queryStart = "SELECT * FROM ".$this->model->tables("events")." WHERE del = '0' AND type = '1' AND date >= :dateFrom AND date <= :dateTo";
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
				#Get questions
				if(count($return["brands"][$brand]["questions"]) == 0 AND !empty($event->questionnaire)) { $return["brands"][$brand]["questions"] = $questionnaires->getQuestions($event->questionnaire); }				
				
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
						"questions" => [],
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
					$phoneFields = ["Telefon 1", "Mobil", "Készrejelentés"];
					foreach($phoneFields AS $phoneField)
					{
						if(empty($allDetails[$phoneField]) OR mb_strlen($allDetails[$phoneField], "UTF-8") < 7 OR mb_substr_count($allDetails[$phoneField], "1", "UTF-8") >= 7) { $phoneEmptyCount++; }
					}
					$phoneEmpty = ($phoneEmptyCount == count($phoneFields));
					
					$emailField = "E-mail";				
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
						
						$answerSumHere = ($answer["data"]->questionnaire == 3) ? $answer["answerSum"] * 2 : $answer["answerSum"];
						$return["brands"][$brand]["admins"][$event->adminName]["answerSum"] += $answerSumHere;
						$return["brands"][$brand]["total"]["answerSum"] += $answerSumHere;
						$return["global"]["answerSum"] += $answerSumHere;
						
						#Answers by questions
						// $answer["answersValsForStats"][$question["questionID"]]
						if(count($answer["answersValsForStats"]))
						{
							foreach($answer["answersValsForStats"] AS $questionID => $questionVal) 
							{
								if(!in_array($questionID, $return["brands"][$brand]["questionsUsed"])) { $return["brands"][$brand]["questionsUsed"][] = $questionID; }
								
								if(!isset($return["brands"][$brand]["admins"][$event->adminName]["questions"][$questionID]))
								{
									$return["brands"][$brand]["admins"][$event->adminName]["questions"][$questionID] = [
										"sum" => 0,
										"count" => 0,
									];
								}
								
								if($answer["data"]->questionnaire == 3) { $questionVal = $questionVal * 2; }
								$return["brands"][$brand]["admins"][$event->adminName]["questions"][$questionID]["sum"] += $questionVal;
								$return["brands"][$brand]["admins"][$event->adminName]["questions"][$questionID]["count"]++;
							}
						}
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
		if(!empty($datas["global"]["eventCount"])) { include(DIR_VIEWS."emails/crm/serviceEmailWeeklyStats.php"); }
		return $datas["global"];
	}
	
	public function statEventsMonthly()
	{
		#Date
		$dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month"));
		$dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom));
		$dateOut = date("Y. m. d.", strtotime($dateFrom))." - ".date("Y. m. d.", strtotime($dateTo));
		
		$datas = $this->statEventAdmins($dateFrom, $dateTo);
		if(!empty($datas["global"]["eventCount"])) { include(DIR_VIEWS."emails/crm/serviceEmailMonthlyStats.php"); }
		return $datas["global"];
	}
	
	public function statEventsCustom($dateFrom = NULL, $dateTo = NULL, $addresses = [], $watch = false, $subject = "Munkatárs statisztikák", $attachments = [])
	{
		#Date
		if($dateFrom === NULL) { $dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month")); }
		if($dateTo === NULL) { $dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom)); }
		$dateOut = date("Y. m. d.", strtotime($dateFrom))." - ".date("Y. m. d.", strtotime($dateTo));
		
		$datas = $this->statEventAdmins($dateFrom, $dateTo);
		if(!empty($datas["global"]["eventCount"])) { include(DIR_VIEWS."emails/crm/serviceEmailStatsCustom.php"); }
		return $datas["global"];
	}
	
	public function statEventsQuarterly()
	{
		#Date
		$dateFrom = date("Y-m-01 00:00:00", strtotime("-3 months"));
		$dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom." +2 months"));
		
		#Attachments
		$fileNameQuarter = date("Y", strtotime($dateFrom))."-Q";
		switch(date("n", strtotime($dateFrom)))
		{
			case 1: $fileNameQuarter .= "1"; break;
			case 4: $fileNameQuarter .= "2"; break;
			case 7: $fileNameQuarter .= "3"; break;
			case 10: $fileNameQuarter .= "4"; break;
		}
		
		$fileName1 = date("Y-m", strtotime($dateFrom));
		$fileName2 = date("Y-m", strtotime($fileName1." +1 month"));
		$fileName3 = date("Y-m", strtotime($fileName1." +2 months"));
		
		$attachments = [
			$this->serviceEventsAdminUsersStatsCSV($dateFrom, $dateTo, $fileNameQuarter),
			base_path("_service_events_admin_users_stats/".$fileName1.".csv"),
			base_path("_service_events_admin_users_stats/".$fileName2.".csv"),
			base_path("_service_events_admin_users_stats/".$fileName3.".csv"),
		];
		
		return $this->statEventsCustom($dateFrom, $dateTo, [], false, $subject = "Munkatárs negyedéves statisztikák", $attachments);
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
		$events = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE del = '0' AND type = '1' AND date >= :dateFrom AND date <= :dateTo", $params);
		
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
		include(DIR_VIEWS."emails/crm/serviceEmailFillingsMonthlyStats.php");
		return $datas;
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
	
	#OTHERS
	public function getErrorDatasByServiceImports()
	{
		#Store datas
		$rows = $this->model->select("SELECT id FROM ".$this->model->tables("imports")." WHERE del = '0' AND id >= '327'");
		$return = [];
		foreach($rows AS $row)
		{
			$import = $this->getImport($row->id);
			if(!empty($import["data"]->content))
			{
				$importName = str_replace(["gablini_szamlak_", ".csv"], ["", ""], $import["fileName"]);
				foreach($import["out"]["rows"] AS $rowDataKey => $rowData)
				{
					if(isset($rowData["basic"]["eventID"]) AND !empty($rowData["basic"]["eventID"]["value"])) {  }
					else
					{
						$returnRow = [$importName];
						foreach($rowData["datas"] AS $dataKey => $data) { $returnRow[] = $data["value"]; }
						$return[] = $returnRow;
					}
				}
			}
		}
		
		#Output
		// echo "<pre>"; print_r($return);
		$fileName = "import_hibak.csv";
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"".$fileName."\"");
		$output = fopen("php://output", "w");
		
		foreach($return AS $row) { fputcsv($output, $row, ";");  }
		fclose($output);
		exit;
	}
	
	public function emailsForCronManualSet()
	{
		#Return
		$return = [];
		
		#Get service emails
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
			foreach($logs AS $log) { $eventIDs[] = $log->serviceEvent; }
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
			
			# -------------------------------
			#Basic query
			$query = "SELECT * FROM ".$this->model->tables("events")." WHERE del = '0' AND (import = '780' OR import = '781' OR import = '782') AND closedByWebmaster = '0' AND marketingDisabled = '0' AND type = :type AND status IS NULL AND questionnaire IS NOT NULL AND questionnaire > '0' AND (questionnaireAnswer IS NULL OR questionnaireAnswer = '0') AND total >= '0' AND (prevEvent IS NULL OR prevEvent = '0')";
			$params = ["type" => $email->eventType];
			
			#Main brand
			if(!empty($email->brand))
			{
				$query .= " AND brand = :brand";
				$params["brand"] = $email->brand;
			}
			#Other brand
			else
			{
				foreach($mainBrands AS $brandItem) { $query .= " AND brand != '".$brandItem."'"; }
			}
			# -------------------------------
			
			$events = $this->model->select($query, $params);
			if(!empty($events))
			{
				#Marketing disabled
				if(in_array($event->customer, $marketingDisabledIdList)) { $this->model->myUpdate($this->model->tables("events"), ["marketingDisabled" => 1], $event->id); }
				elseif($event->marketingDisabled) {  }
				else
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
							}
						}
					}
				}
			}
		}
		
		#Return
		return $return;
	}
	
	public function serviceEventsAdminUsersStatsCSV($dateFrom = NULL, $dateTo = NULL, $fileName = NULL)
	{
		#Date
		if(empty($dateFrom)) { $dateFrom = date("Y-m-01 00:00:00", strtotime("-1 month")); }
		if(empty($dateTo)) { $dateTo = date("Y-m-t 23:59:59", strtotime($dateFrom)); }
		
		if(empty($fileName)) { $fileName = date("Y-m", strtotime($dateFrom)); }
		
		#Premises
		$premises = [];
		$rows = $this->model->select("SELECT premise FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY premise ORDER BY premise");
		foreach($rows AS $row) { $premises[] = $row->premise; }
		
		#Admins
		$admins = [];
		$rows = $this->model->select("SELECT adminName FROM ".$this->model->tables("events")." WHERE del = '0' GROUP BY adminName ORDER BY adminName");
		foreach($rows AS $row) { $admins[] = $row->adminName; }
		
		#Points / brand
		$brandPoints = [
			"nissan" => 80,
			"peugeot" => 10,
			"kia" => 30,
			"hyundai" => 10,
			"infiniti" => 20,
			"citroen" => 10,
			"_other" => 10,
		];
		
		#Event statuses
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND date >= :dateFrom AND date <= :dateTo ORDER BY event, date", ["dateFrom" => $dateFrom, "dateTo" => $dateTo]);
		if(!empty($rows))
		{		
			$questionnaireController = new \App\Http\Controllers\QuestionnaireController;
			
			$allEvents = [];
			$eventList = [];
			foreach($rows AS $row)
			{
				if(!in_array($row->event, $allEvents)) { $allEvents[$row->event] = $this->model->getEvent($row->event); }
				$event = $allEvents[$row->event];
				$eventList[$event->premise][$event->adminName][$event->id] = $event;
			}

			$out = [["Telephely", "Munkatárs", "Események száma", "Kérdőív kitöltések száma", "Kitöltési arány", "Átlagos pontszám / kérdőív", "Összpontszám", "Kitöltések lehetséges max. pontszáma", "%"]];
			foreach($premises AS $premise)
			{
				$title = true;
				foreach($admins AS $admin)
				{
					if(isset($eventList[$premise][$admin]) AND !empty($eventList[$premise][$admin]))
					{
						$answerSum = 0;
						$answerCount = 0;
						$eventCount = count($eventList[$premise][$admin]);
						$maxPoints = 0;
						foreach($eventList[$premise][$admin] AS $eventID => $event)
						{
							if(!empty($event->questionnaireAnswer))
							{
								$answer = $questionnaireController->getAnswer($event->questionnaireAnswer, false);
								if($answer !== false) 
								{ 
									$answerCount++; 
									if(!empty($answer["answerSum"])) { $answerSum += $answer["answerSum"]; }
									$maxPointsHere = (isset($brandPoints[strtolower($event->brand)])) ? $brandPoints[strtolower($event->brand)] : $brandPoints["_other"];
									$maxPoints += $maxPointsHere;
								}
							}
						}
						$answerAvg = (!empty($answerCount)) ? $answerSum / $answerCount : "-";
						$maxPointsPercent = (!empty($maxPoints)) ? $answerSum / $maxPoints : "-";
						$out[] = [$premise, $admin, $eventCount, $answerCount, $answerCount / $eventCount, $answerAvg, $answerSum, $maxPoints, $maxPointsPercent];
					}
				}
			}
			
			#Create CSV
			$filePath = base_path("_service_events_admin_users_stats/".$fileName.".csv");
			if(file_exists($filePath)) { unlink($filePath); }
			$file = fopen($filePath, "w");
			foreach($out AS $outRow) 
			{ 
				$outRowANSII = [];
				foreach($outRow AS $outRowItem) { $outRowANSII[] = iconv("utf-8", "iso-8859-2", $outRowItem); }
				fputcsv($file, $outRowANSII, ";"); 
			}
			fclose($file);
		}
		
		return $filePath;
	}
	
	public function serviceEventsAdminUsersStatsEmail($attachment)
	{
		include(DIR_VIEWS."emails/crm/serviceEmailAdminUsersMonthlyStats.php");
	}
	
	public function serviceEventsAdminNoAnswerCSV($dateFrom = NULL, $dateTo = NULL, $fileNameEnd = "")
	{
		#Questionnaire
		$q = new \App\Http\Controllers\QuestionnaireController;
		
		#Date
		if(empty($dateFrom)) { $dateFrom = date("Y-m-d 00:00:00", strtotime("-1 week")); }
		if(empty($dateTo)) { $dateTo = date("Y-m-d 23:59:59", strtotime("-1 day")); }
		
		#CSV header row
		$out = [
			["Munkalapszám", "Dátum", "Progress kód", "Állapot", "Megjegyzés"],
		];
		
		#Statuses
		$statuses = [];
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("eventStatuses")." WHERE del = '0'");
		foreach($rows AS $row) { $statuses[$row->id] = $row; }
		
		#Status changes
		$events = [];
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("eventStatusChanges")." WHERE del = '0' AND date >= :dateFrom AND date <= :dateTo AND (status = '5' OR status = '8')", ["dateFrom" => $dateFrom, "dateTo" => $dateTo]);
		if(count($rows) > 0)
		{
			
			foreach($rows AS $row) 
			{
				if(in_array($row->event, $events)) { continue; }
				
				#Answered
				if($row->status == 8)
				{
					$events[] = $row->event;
					$eventRows = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE id = :id", ["id" => $row->event]);
					if(count($eventRows) > 0)
					{
						$event = $eventRows[0];
						if(!empty($event->questionnaireAnswer) AND $event->questionnaireAnswer > 0)
						{
							$answer = $q->getAnswer($event->questionnaireAnswer, false);
							if($answer !== false)
							{
								if(!empty($answer["answerComment"]))
								{
									$out[] = [
										$event->sheetNumber,
										date("Y. m. d.", strtotime($row->date)),
										"'".$event->customerProgressCode."'",
										$statuses[$row->status]->name,
										$answer["answerComment"],
									];
								}
							}
						}
					}
				}
				#Don't want to answer
				elseif($row->status == 5)
				{
					$events[] = $row->event;
					$eventRows = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE id = :id", ["id" => $row->event]);
					if(count($eventRows) > 0)
					{
						$event = $eventRows[0];
						$out[] = [
							$event->sheetNumber,
							date("Y. m. d.", strtotime($row->date)),
							"'".$event->customerProgressCode."'",
							$statuses[$row->status]->name,
							$row->comment,
						];
					}
				}
			}
		}
		
		#Create CSV
		$fileName = date("YmdHis").$fileNameEnd;
		$filePath = base_path("_service_events_admin_noanswer_stats/".$fileName.".csv");
		if(file_exists($filePath)) { unlink($filePath); }
		$file = fopen($filePath, "w");
		foreach($out AS $outRow) 
		{ 
			$outRowANSII = [];
			foreach($outRow AS $outRowItem) { $outRowANSII[] = iconv("utf-8", "iso-8859-2", $outRowItem); }
			fputcsv($file, $outRowANSII, ";"); 
		}
		fclose($file);
		
		return $filePath;
	}
	
	public function serviceEventsAdminNoAnswerEmail()
	{
		$fileName = $this->serviceEventsAdminNoAnswerCSV();
		include(DIR_VIEWS."emails/crm/serviceEmailAdminNoAnswerWeeklyStats.php");
		
		return $fileName;
	}
	
	public function serviceEventsAstonMartinNotification($days = 3)
	{
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("events")." WHERE type = '1' AND dateClosed LIKE :date AND (brand LIKE '%aston%' AND brand LIKE '%martin%')", ["date" => date("Y-m-d", strtotime("-".$days." days"))]);
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
			
			// include(DIR_VIEWS."emails/crm/serviceEmailAstonMartinNotification.php");
		}
	}
	
	public function tempInfinitiAnswers()
	{
		$return = [
			"header" => [
				"date" => "Dátum",
				"customerName" => "Ügyfél név",
				"customerEmail" => "Ügyfél e-mail",
				"answerByUser" => "Kitöltés",
				"hasBadValue" => "Van alacsony érték?",
				"answerSum" => "Összpontszám",
				"*" => "*",
			],
			"rows" => [],
		];
		
		$questionnaires = new \App\Http\Controllers\QuestionnaireController;		
		$rows = $questionnaires->model->select("SELECT id FROM ".$questionnaires->model->tables("answers")." WHERE questionnaire = '6' AND del = '0' AND date >= '2018-01-01'");
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row)
			{
				$answer = $questionnaires->getAnswer($row->id, false);
				if($answer !== false)
				{
					$return["rows"][$answer["id"]] = [
						"date" => $answer["dateOut"],
						"customerName" => $answer["customerData"]["Név"],
						"customerEmail" => $answer["customerData"]["E-mail cím"],
						"answerByUser" => ($answer["answerByUser"]) ? "Call Center" : "Ügyfél (online)",
						"hasBadValue" => ($answer["hasBadValue"]) ? "IGEN" : "Nem",
						"answerSum" => $answer["answerSum"],
						"*" => "*",
					];
					
					foreach($answer["answers"] AS $questionID => $answerData)
					{
						if(!isset($return["header"][$questionID])) 
						{ 
							$return["header"][$questionID] = $answerData["name"];
							if($answerData["watched"]) { $return["header"][$questionID."-badValue"] = "Alacsony?"; }
						}
						
						$return["rows"][$answer["id"]][$questionID] = strip_tags(str_replace(["<br />", "<br>", "\r", "\n"], " ", $answerData["val"]));
						if($answerData["watched"]) { $return["rows"][$answer["id"]][$questionID."-badValue"] = ($answerData["badValue"]) ? "Igen" : "Nem"; }
					}
				}
			}
		}
		
		#Output
		$fileName = date("Ymd")."_kerdoiv_valaszok.csv";
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"".$fileName."\"");
		$output = fopen("php://output", "w");
		
		$keys = array_keys($return["header"]);
		fputcsv($output, $return["header"], ";"); 
		foreach($return["rows"] AS $row) 
		{ 
			$out = [];
			foreach($keys AS $keyID => $key)
			{
				$out[$keyID] = (isset($row[$key])) ? $row[$key] : "";
			}
			fputcsv($output, $out, ";");  
		}
		fclose($output);
		
		#Return
		return $return;
	}
}
