<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Customer extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"customers" => $this->dbPrefix."customers",
			"cars" => $this->dbPrefix."customers_cars",
			"importedDatas" => $this->dbPrefix."customers_importedDatas",
			"log" => $this->dbPrefix."customers_log",
			"logTypes" => $this->dbPrefix."customers_logTypes",
			"marketingDisabled" => $this->dbPrefix."customers_marketingDisabled",
			"phoneChanges" => $this->dbPrefix."customers_phoneChanges",
			"servicePoints" => $this->dbPrefix."customers_servicePoints",
			"servicePointTypes" => $this->dbPrefix."customers_servicePoints_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get customer
	public function getCustomer($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "id", $id, $field, $delCheck);
	}
	
	public function getCustomerByEmail($email, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "email", $email, $field, $delCheck);
	}
	
	public function getCustomerByProgressCode($progressCode, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "progressCode", $progressCode, $field, $delCheck);
	}
	
	public function getCustomerByToken($token, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "token", $token, $field, $delCheck);
	}
	
	public function getCustomerByCode($code, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "code", $code, $field, $delCheck);
	}
	
	public function getCustomerByHash($hash, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "hash", $hash, $field, $delCheck);
	}
	
	public function getCustomerByPhone($phone, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "phone", $phone, $field, $delCheck);
	}
	
	public function getCustomerByMobile($mobile, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("customers"), "mobile", $mobile, $field, $delCheck);
	}
	
	public function getCustomers($selectFields = "*", $search = [], $deleted = 0, $orderBy = "email", $limit = "0, 1000")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("customers")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		#Search
		if(!empty($search))
		{
			$searchData = $this->customerSearch($search);
			if(!empty($searchData["query"]) AND !empty($searchData["params"])) 
			{ 
				$query .= $searchData["query"]; 
				foreach($searchData["params"] AS $paramKey => $paramVal) { $params[$paramKey] = $paramVal; }
			}
		}
		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		if(!empty($limit)) { $query .= " LIMIT ".$limit; }
		
		return $this->select($query, $params);
	}
	
	#Customer search commands
	public function customerSearch($search)
	{
		#Details
		$params = [];
		$query = "";
		
		#Search
		if(isset($search["name"]) AND !empty($search["name"])) 
		{
			#String
			$searchString = $search["name"];
			$delimiter = " ";
			
			$string = trim($searchString);
			$words = explode($delimiter, $string);	
			
			if(count($words) > 0)
			{
				$fields = ["firstName", "lastName"];
				$query .= " AND (";
				foreach($words AS $i => $word)
				{
					#String format
					$searchKey = $word;
					
					#Query and params
					if($i > 0) { $query .= " AND"; }
					$query .= " (";
					foreach($fields AS $j => $field)
					{
						if($j > 0) { $query .= " OR"; }
						$paramName = $field."Txt".$i.$j;
						$query .= " ".$field." LIKE :".$paramName; 
						$params[$paramName] = "%".$searchKey."%";
					}
					$query .= ")";
				}
				$query .= ")";
			}
		}
		
		$field = "user";
		if(isset($search[$field]) AND !empty($search[$field])) 
		{
			$query .= " AND ".$field." = :".$field;
			$params[$field] = $search[$field];
		}
		
		$fields = ["email", "phone", "code"];
		foreach($fields AS $field)
		{
			if(isset($search[$field]) AND !empty($search[$field])) 
			{
				$query .= " AND ".$field." LIKE :".$field;
				$params[$field] = "%".$search[$field]."%";
			}
		}

		#Return
		return [
			"params" => $params,
			"query" => $query,
		];
	}
	
	#Get userID's for search
	public function getUsersForSearch()
	{
		return $this->select("SELECT user FROM ".$this->tables("customers")." WHERE del = '0' AND user != '0' AND user IS NOT NULL GROUP BY user");
	}
	
	#Get imported datas by customer
	public function getImportedDatasByCustomer($customer, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["customer" => $customer];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("importedDatas")." WHERE customer = :customer";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get customer count
	public function countCustomers($dateFrom = NULL, $dateTo = NULL, $deleted = 0)
	{
		$params = [];
		$query = "SELECT count(id) AS count FROM ".$this->tables("customers")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
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
		return $this->select($query, $params);
	}
	
	#Get customers from where
	public function customersFromWhere()
	{
		return $this->select("SELECT customer, fromWhere FROM ".$this->tables("importedDatas")." WHERE del = '0' AND fromWhere != '' AND fromWhere IS NOT NULL GROUP BY customer ORDER BY fromWhere", $params);
	}
	
	#Get car
	public function getCar($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("cars"), "id", $id, $field, $delCheck);
	}
	
	public function getCarByIdentifiers($customer, $regNumber, $bodyNumber, $field = "", $deleted = 1)
	{
		if(empty($field)) { $select = "*"; }
		else { $select = $field; }
		
		$query = "SELECT ".$select." FROM ".$this->tables("cars")." WHERE customer = :customer AND regNumber = :regNumber AND bodyNumber = :bodyNumber";
		$params = [
			"customer" => $customer, 
			"regNumber" => $regNumber, 
			"bodyNumber" => $bodyNumber
		];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$rows = $this->select($query, $params);
		if(count($rows) > 0) { $returnRow = $rows[0]; }
		else { $returnRow = new \stdClass(); }
		
		if(!empty($field)) { $return = $returnRow->$field; }
		else { $return = $returnRow; }
		
		return $return;
	}
	
	#Get cars by customer
	public function getCarsByCustomer($customer, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["customer" => $customer];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("cars")." WHERE customer = :customer AND (brand != '' OR name != '' OR regNumber IS NOT NULL OR bodyNumber IS NOT NULL)";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get imported datas
	public function getImportedData($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("importedDatas"), "id", $id, $field, $delCheck);
	}
	
	#Log - Get type
	public function getLogType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("logTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getLogTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("logTypes"), "url", $url, $field, $delCheck);
	}
	
	#Log
	public function log($typeName, $customer, $foreignKey, $datas = [])
	{
		$return = [
			"type" => "error",
			"info" => NULL,
			"id" => NULL,
		];
		
		$type = $this->getLogTypeByURL($typeName);
		if(isset($type->id) AND !empty($type->id))
		{
			if($type->active)
			{
				#User
				if(defined("USERID")) { $user = USERID; }
				else { $user = NULL; }
				
				#Basic datas				
				$params = [
					"type" => $type->id,
					"date" => date("Y-m-d H:i:s"),
					"user" => $user,
					"customer" => $customer,
					"foreignKey" => $foreignKey,
				];
				
				#Dinamic datas
				if(!empty($datas))
				{
					if(isset($datas["text"])) { $params["text"] = $datas["text"]; }
					if(isset($datas["systemText"])) { $params["systemText"] = $datas["systemText"]; }
					if(isset($datas["json"])) { $params["json"] = $this->json($datas["json"]); }
					if(isset($datas["jsonOldDatas"])) { $params["jsonOldDatas"] = $this->json($datas["jsonOldDatas"]); }
					if(isset($datas["jsonNewDatas"])) { $params["jsonNewDatas"] = $this->json($datas["jsonNewDatas"]); }
					
					if(isset($datas["userImportant"])) { $params["user"] = $datas["userImportant"]; }
					if(isset($datas["dateImportant"])) { $params["date"] = $datas["dateImportant"]; }
				}
				
				#Insert and return
				$return["id"] = $this->myInsert($this->tables("log"), $params);
				$return["type"] = "success";
			}
			else { $return["info"] = "inactive-type"; }
		}
		else { $return["info"] = "unknown-type"; }
		
		return $return;
	}
	
	#Log - Get Row
	public function getLog($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("log"), "id", $id, $field, $delCheck);
	}
	
	public function getLogsByCustomer($customer, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["customer" => $customer];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("log")." WHERE customer = :customer";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#marketing disabled list
	public function getMarketingDisabledList()
	{
		$return = [];
		$rows = $this->select("SELECT progressCode FROM ".$this->tables("marketingDisabled")." WHERE del = '0' GROUP BY progressCode");
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[] = $row->progressCode; }
		}
		
		return $return;
	}
	
	#Get service points
	public function getServicePointsSumByCustomer($customerID)
	{
		$return = 0;
		$rows = $this->select("SELECT id, pointChange FROM ".$this->tables("servicePoints")." WHERE customer = :customer AND del = '0'", ["customer" => $customerID]);
		if(count($rows) > 0)
		{
			foreach($rows AS $row) { $return += $row->pointChange; }
		}
		
		return $return;
	}
	
	public function getServicePointsByType($typeID, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["type" => $typeID];
		$query = "SELECT * FROM ".$this->tables("servicePoints")." WHERE type = :type";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get service point-types
	public function getServicePointType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("servicePointTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getServicePointTypeByURL($url, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("servicePointTypes"), "url", $url, $field, $delCheck);
	}
	
	#JSON encode
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
