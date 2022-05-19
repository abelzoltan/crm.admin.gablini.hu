<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Hex extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"cars" => $this->dbPrefix."hex_cars",
			"datas" => $this->dbPrefix."hex_datas",
			"imports" => $this->dbPrefix."hex_imports",
			"pics" => $this->dbPrefix."hex_pics",
			"types" => $this->dbPrefix."hex_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get type
	public function getType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("types"), "id", $id, $field, $delCheck);
	}
	
	public function getTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "url", $url, $field, $delCheck);
	}
	
	public function getTypeByExportName($exportName, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "exportName", $exportName, $field, $delCheck);
	}
	
	public function getTypeByPartnerID($partnerID, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "partnerID", $partnerID, $field, $delCheck);
	}
	
	public function getTypeByHash($hash, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "hash", $hash, $field, $delCheck);
	}
	
	public function getTypes($deleted = 0, $orderNumber = "id")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("types")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get car
	public function getCar($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("cars"), "id", $id, $field, $delCheck);
	}
	
	public function getCarByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("cars"), "url", $url, $field, $delCheck);
	}
	
	public function getCarByCode($code, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("cars"), "code", $code, $field, $delCheck);
	}
	
	public function getCarsByType($type, $search = [], $deleted = 0, $orderNumber = "id")
	{
		$params = [
			"type" => $type,
		];
		$query = "SELECT * FROM ".$this->tables("cars")." WHERE type = :type";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		#Search
		if(!empty($search))
		{
			$searchData = $this->search($search);
			if(!empty($searchData["query"])) { $query .= $searchData["query"]; }
			if(!empty($searchData["params"])) { foreach($searchData["params"] AS $paramKey => $paramVal) { $params[$paramKey] = $paramVal; } }
			if(!empty($searchData["orderNumber"])) { $orderNumber = $searchData["orderNumber"]; }
		}
		
		$query .= " ORDER BY ".$orderNumber;
		return $this->select($query, $params);
	}
	
	public function getCarsByTypes($types, $search = [], $deleted = 0, $orderNumber = "id")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("cars")." WHERE id != '0'";
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		#Types
		if(count($types) > 0)
		{
			$query .= " AND (";
			foreach($types AS $i => $typeID)
			{
				if($i > 0) { $query .= " OR"; }
				$query .= " type = :type".$i;
				$params["type".$i] = $typeID;
			}
			$query .= ")";
		}
		
		#Search
		if(!empty($search))
		{
			$searchData = $this->search($search);
			if(!empty($searchData["query"])) { $query .= $searchData["query"]; }
			if(!empty($searchData["params"])) { foreach($searchData["params"] AS $paramKey => $paramVal) { $params[$paramKey] = $paramVal; } }
			if(!empty($searchData["orderNumber"])) { $orderNumber = $searchData["orderNumber"]; }
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get data
	public function getDataByCarAndURL($car, $url, $deleted = 0)
	{
		$params = [
			"car" => $car,
			"url" => $url,
		];
		$query = "SELECT * FROM ".$this->tables("datas")." WHERE car = :car AND url = :url";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		return $this->select($query, $params)[0];
	}
	
	public function getDatasByCar($car, $deleted = 0, $orderNumber = "id")
	{
		$params = [
			"car" => $car,
		];
		$query = "SELECT * FROM ".$this->tables("datas")." WHERE car = :car";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get pics
	public function getPicByCarAndBasic($car, $basic, $deleted = 0)
	{
		$params = [
			"car" => $car,
			"basic" => $basic,
		];
		$query = "SELECT * FROM ".$this->tables("pics")." WHERE car = :car AND basic = :basic";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		return $this->select($query, $params)[0];
	}
	
	public function getPicsByCar($car, $deleted = 0, $orderNumber = "id")
	{
		$params = [
			"car" => $car,
		];
		$query = "SELECT * FROM ".$this->tables("pics")." WHERE car = :car";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get brands for search
	public function getBrandsForSearch($types, $orderNumber = "brand")
	{
		$params = [];
		$query = "SELECT brand FROM ".$this->tables("cars")." WHERE del = '0'";
		
		if(count($types) > 0)
		{
			$query .= " AND (";
			foreach($types AS $i => $typeID)
			{
				if($i > 0) { $query .= " OR"; }
				$query .= " type = :type".$i;
				$params["type".$i] = $typeID;
			}
			$query .= ")";
		}
		
		$query .= "GROUP BY brand ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get prices for search
	public function getPricesForSearch($types, $orderNumber = "price")
	{
		$params = [];
		$query = "SELECT price FROM ".$this->tables("cars")." WHERE del = '0'";
		
		if(count($types) > 0)
		{
			$query .= " AND (";
			foreach($types AS $i => $typeID)
			{
				if($i > 0) { $query .= " OR"; }
				$query .= " type = :type".$i;
				$params["type".$i] = $typeID;
			}
			$query .= ")";
		}
		
		$query .= "GROUP BY price ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get years for search
	public function getYearsForSearch($types)
	{
		$params = [];
		$query = "SELECT d.value AS value FROM ".$this->tables("cars")." c INNER JOIN ".$this->tables("datas")." d ON c.id = d.car WHERE c.del = '0' AND d.del = '0' AND d.url = 'evjarat'";
		
		if(count($types) > 0)
		{
			$query .= " AND (";
			foreach($types AS $i => $typeID)
			{
				if($i > 0) { $query .= " OR"; }
				$query .= " c.type = :type".$i;
				$params["type".$i] = $typeID;
			}
			$query .= ")";
		}
		
		$query .= "GROUP BY d.value ORDER BY d.value";
		
		return $this->select($query, $params);
	}
	
	#Search commands
	public function search($search)
	{
		#Details
		$params = [];
		$query = "";
		$orderNumber = NULL;
		
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
				$fields = ["name", "brand"];
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
		
		if(isset($search["brand"]) AND !empty($search["brand"])) 
		{
			$query .= " AND brand = :brand";
			$params["brand"] = $search["brand"];
		}
		
		if(isset($search["priceFrom"]) AND !empty($search["priceFrom"])) 
		{
			$query .= " AND price >= :priceFrom";
			$params["priceFrom"] = $search["priceFrom"];
		}
		
		if(isset($search["priceTo"]) AND !empty($search["priceTo"])) 
		{
			$query .= " AND price <= :priceTo";
			$params["priceTo"] = $search["priceTo"];
		}
		
		#Year: in Controller!
		
		#Sorting
		if(isset($search["orderBy"]) AND !empty($search["orderBy"])) { $orderNumber = $search["orderBy"]; }
		
		#Return
		$return = [
			"params" => $params,
			"query" => $query,
			"orderNumber" => $orderNumber,
		];
		
		return $return;
	}
}
