<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Service extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"emails" => $this->dbPrefix."services_emails",
				"emailsLog" => $this->dbPrefix."services_emails_log",
			"events" => $this->dbPrefix."services_events",
				"eventTypes" => $this->dbPrefix."services_events_types",
				"eventStatuses" => $this->dbPrefix."services_events_statuses",
					"eventStatusChanges" => $this->dbPrefix."services_events_statuses_changes",
			"imports" => $this->dbPrefix."services_imports",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get import
	public function getImport($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("imports"), "id", $id, $field, $delCheck);
	}
	
	public function getProgressImportFileNames($deleted = 0)
	{
		$params = [];
		$query = "SELECT fileName, count(id) AS importCount FROM ".$this->tables("imports")." WHERE progress = '1' AND fileName IS NOT NULL AND fileName != ''";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}		
		$query .= " GROUP BY fileName ORDER BY fileName";
		return $this->select($query, $params);
	}
	
	#New import
	public function newImport($fileName, $errorMessage = NULL, $importReturn = NULL, $content = NULL, $progress = 0)
	{
		if(defined("USERID")) { $user = USERID; }
		else { $user = NULL; }
		
		if(!empty($importReturn)) { $importReturn = $this->json($importReturn); }	
		
		$params = [
			"user" => $user,
			"date" => date("Y-m-d H:i:s"),
			"progress" => $progress,
			"fileName" => $fileName,
			"errorMessage" => $errorMessage,
			"importReturn" => $importReturn,
			"content" => $content,
		];
		
		return $this->myInsert($this->tables("imports"), $params);
	}
	
	#Update import
	public function editImport($id, $params)
	{	
		if(isset($params["importReturn"]) AND !empty($params["importReturn"])) { $params["importReturn"] = $this->json($params["importReturn"]); }
		if(isset($params["datas"]) AND !empty($params["datas"])) { $params["datas"] = $this->json($params["datas"]); }
		
		return $this->myUpdate($this->tables("imports"), $params, $id);
	}
	
	#Get event
	public function getEvent($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("events"), "id", $id, $field, $delCheck);
	}
	
	public function getEventByHash($hash, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("events"), "hash", $hash, $field, $delCheck);
	}
	
	public function getEventBySheetNumber($sheetNumber, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("events"), "sheetNumber", $sheetNumber, $field, $delCheck);
	}
	
	public function getEventByInvoice($invoice, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("events"), "invoice", $invoice, $field, $delCheck);
	}
	
	#Get event type
	public function getEventType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("eventTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getEventTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("eventTypes"), "url", $url, $field, $delCheck);
	}
	
	public function getEventTypes($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("eventTypes")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }			
		return $this->select($query, $params);
	}
	
	#Get event points
	public function getEventScoresByCustomer($customer, $customerProgressCode = NULL)
	{
		// $rows = $this->select("SELECT sum(score) AS score FROM ".$this->tables("events")." WHERE customer = :customer", ["customer" => $customer]);
		
		$query = "SELECT score FROM ".$this->tables("events")." WHERE del = '0' AND (customer = :customer";
		$params = ["customer" => $customer];
		if(!empty($customerProgressCode))
		{
			$query .= " OR customerProgressCode = :customerProgressCode";
			$params["customerProgressCode"] = $customerProgressCode;
		}
		$query .= ") ORDER BY id DESC LIMIT 0, 1";
		
		$rows = $this->select($query, $params);
		return (count($rows) > 0 AND !empty($rows[0]->score)) ? $rows[0]->score : 0;
	}
	
	#Get event list
	public function getEvents($customer = NULL, $selectFields = "*", $search = [], $deleted = 0, $orderBy = "dateSetout DESC, dateClosed DESC, date DESC, id DESC")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("events")." WHERE customer != '0' AND customer IS NOT NULL";
		if($customer !== NULL)
		{
			$query .= " AND customer = :customer";
			$params["customer"] = $customer;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($search))
		{
			if(isset($search["user"]) AND !empty($search["user"]))
			{
				$query .= " AND user = :user";
				$params["user"] = $search["user"];
			}
			if(isset($search["type"]) AND !empty($search["type"]))
			{
				$query .= " AND type = :type";
				$params["type"] = $search["type"];
			}
			if(isset($search["questionnaireID"]) AND !empty($search["questionnaireID"]))
			{
				$query .= " AND questionnaire = :questionnaireID";
				$params["questionnaireID"] = $search["questionnaireID"];
			}
			if(isset($search["adminTodo"])) 
			{ 
				if($search["adminTodo"]) { $query .= " AND adminTodo = '1'"; }
				else { $query .= " AND (adminTodo IS NULL OR adminTodo = '0')"; }
			}
			if(isset($search["adminTodoDateToNow"]) AND $search["adminTodoDateToNow"]) 
			{ 
				$query .= " AND adminTodoDate <= :adminTodoDateToNow";
				$params["adminTodoDateToNow"] = date("Y-m-d H:i:s");
			}							
			if(isset($search["sheetNumber"]) AND !empty($search["sheetNumber"]))
			{
				$query .= " AND sheetNumber LIKE :sheetNumber";
				$params["sheetNumber"] = "%".$search["sheetNumber"]."%";
			}
			if(isset($search["questionnaireAnswered"])) 
			{ 
				if($search["questionnaireAnswered"]) { $query .= " AND questionnaireAnswer IS NOT NULL AND questionnaireAnswer != '0'"; }
				else { $query .= " AND (questionnaireAnswer IS NULL OR questionnaireAnswer = '0')"; }
			}			
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

			if(isset($search["dateSetoutFrom"]) AND !empty($search["dateSetoutFrom"]))
			{
				$query .= " AND dateSetout >= :dateSetoutFrom";
				$params["dateSetoutFrom"] = $search["dateSetoutFrom"];
			}
			if(isset($search["dateSetoutTo"]) AND !empty($search["dateSetoutTo"]))
			{
				$query .= " AND dateSetout <= :dateSetoutTo";
				$params["dateSetoutTo"] = $search["dateSetoutTo"];
			}
			
			if(isset($search["dateClosedFrom"]) AND !empty($search["dateClosedFrom"]))
			{
				$query .= " AND dateClosed >= :dateClosedFrom";
				$params["dateClosedFrom"] = $search["dateClosedFrom"];
			}
			if(isset($search["dateClosedTo"]) AND !empty($search["dateClosedTo"]))
			{
				$query .= " AND dateClosed <= :dateClosedTo";
				$params["dateClosedTo"] = $search["dateClosedTo"];
			}
			if(isset($search["totalMin"]) AND !empty($search["totalMin"]))
			{
				$query .= " AND total >= :totalMin";
				$params["totalMin"] = $search["totalMin"];
			}
		}			
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }			
		return $this->select($query, $params);
	}
	
	#Get events for sending
	public function getEventsForEmailSending($type, $dateField, $seconds, $brand, $mainBrands = [])
	{
		#Basic query
		$date = date("Y-m-d H:i:s", strtotime("-".$seconds." seconds"));
		$query = "SELECT * FROM ".$this->tables("events")." WHERE del = '0' AND closedByWebmaster = '0' AND marketingDisabled = '0' AND type = :type AND ".$dateField." <= :date AND status IS NULL AND questionnaire IS NOT NULL AND questionnaire > '0' AND (questionnaireAnswer IS NULL OR questionnaireAnswer = '0') AND total >= '0' AND (prevEvent IS NULL OR prevEvent = '0') AND brand != 'peugeot'";
		$params = [
			"type" => $type,
			"date" => $date,
		];
		
		#Main brand
		if(!empty($brand))
		{
			$query .= " AND brand = :brand";
			$params["brand"] = $brand;
		}
		#Other brand
		else
		{
			foreach($mainBrands AS $brandItem) { $query .= " AND brand != '".$brandItem."'"; }
		}
		
		#Return
		return $this->select($query, $params);
	}
	
	#Get events for sending
	public function getEventsForExports($brand, $dateFrom, $dateTo, $premises = [], $orderBy = "dateClosed")
	{
		#Basic query
		$query = "SELECT id FROM ".$this->tables("events")." WHERE del = '0' AND (type = '1' OR type = '2') AND dateClosed >= :dateFrom AND dateClosed <= :dateTo";
		$params = [
			"dateFrom" => $dateFrom,
			"dateTo" => $dateTo,
		];
		
		#Main brand
		$mainBrands = ["nissan", "hyundai", "kia", "peugeot", "infiniti"];
		if(empty($brand) OR $brand == "general")
		{
			foreach($mainBrands AS $brandItem) { $query .= " AND brand != '".$brandItem."'"; }
		}
		#Other brand
		else
		{
			$query .= " AND brand = :brand";
			$params["brand"] = $brand;
		}
		
		#Premises
		if(!empty($premises))
		{
			$query .= " AND (";
			$i = 0;
			foreach($premises AS $premise) 
			{ 
				if($i > 0) { $query .= " OR "; }
				$query .= "premise = :premise".$i;
				$params["premise".$i] = $premise;
				$i++;
			}
			$query .= ")";
		}
		
		#Ordering
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		#Return
		return $this->select($query, $params);
	}
	
	#Get events for tracking
	public function getEventsForTracking()
	{		
		return $this->select("SELECT id FROM ".$this->tables("events")." WHERE del = '0' AND closedByWebmaster = '0' AND marketingDisabled = '0' AND type = '1' AND status = '15' AND questionnaire IS NOT NULL AND questionnaire > '0' AND (questionnaireAnswer IS NULL OR questionnaireAnswer = '0') AND total >= '0' AND wrongNumberClosed IS NULL ORDER BY dateClosed DESC");
	}
	
	#Get event brands
	public function getEventPremises($brand = "")
	{
		$params = [];
		$query = "SELECT premise FROM ".$this->tables("events")." WHERE del = '0'";
		if(!empty($brand)) 
		{ 
			$query .= " AND premise LIKE :brand";
			$params["brand"] = "%".$brand."%";
		}
		$query .= " GROUP BY premise ORDER BY premise";
		
		return $this->select($query, $params);
	}
	
	public function getEventPremises2($brand = "")
	{
		$params = [];
		$query = "SELECT premise FROM ".$this->tables("events")." WHERE del = '0'";
		if(!empty($brand)) 
		{ 
			$query .= " AND brand LIKE :brand";
			$params["brand"] = "%".$brand."%";
		}
		$query .= " GROUP BY premise ORDER BY premise";
		
		return $this->select($query, $params);
	}
	
	#Get emails
	public function getEmails($eventType = NULL, $selectFields = "*", $deleted = 0, $orderBy = "eventType, brand, email")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("emails")." WHERE id != '0'";
		if($eventType !== NULL)
		{
			$query .= " AND eventType = :eventType";
			$params["eventType"] = $eventType;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}			
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }		
		return $this->select($query, $params);
	}
	
	#Get emails logs
	public function getEmailsLogs($serviceEmail, $serviceEvent = NULL, $selectFields = "*", $deleted = 0, $orderBy = "")
	{
		$params = ["serviceEmail" => $serviceEmail];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("emailsLog")." WHERE serviceEmail = :serviceEmail";
		if($serviceEvent !== NULL)
		{
			$query .= " AND serviceEvent = :serviceEvent";
			$params["serviceEvent"] = $serviceEvent;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}			
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }		
		return $this->select($query, $params);
	}
	
	#Log email sending
	public function newEmailLog($serviceEmail, $serviceEvent)
	{
		$params = [
			"serviceEmail" => $serviceEmail,
			"serviceEvent" => $serviceEvent,
			"date" => date("Y-m-d H:i:s"),
		];	
		return $this->myInsert($this->tables("emailsLog"), $params);
	}
	
	#Get event status
	public function getEventStatus($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("eventStatuses"), "id", $id, $field, $delCheck);
	}
	
	#Get event statuses
	public function getEventStatuses($inList = NULL, $successValue = NULL, $selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("eventStatuses")." WHERE id != '0'";
		if($inList !== NULL)
		{
			$query .= " AND inList = :inList";
			$params["inList"] = $inList;
		}
		if($successValue !== NULL)
		{
			$query .= " AND successValue = :successValue";
			$params["successValue"] = $successValue;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}			
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }		
		return $this->select($query, $params);
	}
	
	#Get event status change
	public function getEventStatusChange($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("eventStatusChanges"), "id", $id, $field, $delCheck);
	}
	
	#Get event status changes
	public function getEventStatusChanges($eventID, $status = NULL, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["event" => $eventID];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("eventStatusChanges")." WHERE event = :event";
		if($status !== NULL)
		{
			$query .= " AND status = :status";
			$params["status"] = $status;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}			
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }		
		return $this->select($query, $params);
	}
	
	#JSON encode and decode
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
	
	public function jsonDecode($string)
	{
		return json_decode($string, JSON_UNESCAPED_UNICODE);
	}
}
