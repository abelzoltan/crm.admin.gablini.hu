<?php 
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Address extends Base
{	
	#Tables
	public function tables($name = "")
	{
		$return = [
			"addresses" => $this->dbPrefix."addresses",
			"cities" => $this->dbPrefix."addresses_cities",
			"countries" => $this->dbPrefix."addresses_countries",
			"regions" => $this->dbPrefix."addresses_regions",
			"types" => $this->dbPrefix."addresses_types",
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
	
	#Get address
	public function getAddress($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("addresses"), "id", $id, $field, $delCheck);
	}
	
	public function getAddressByKey($typeName, $foreignKey, $del = NULL)
	{
		$type = $this->getTypeByURL($typeName, "id");
		
		$query = "SELECT * FROM ".$this->tables("addresses")." WHERE type = :type AND foreignKey = :foreignKey";
		$params = [
			"type" => $type,
			"foreignKey" => $foreignKey,
		];
		
		if($del !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $del;
		}
		
		$rows = $this->select($query, $params);
		
		if(count($rows) > 0) { $return = $rows[0]; }
		else { $return = false; }
		return $return;
	}
	
	public function getAddressByKey2($type, $foreignKey, $del = NULL)
	{	
		$query = "SELECT * FROM ".$this->tables("addresses")." WHERE type = :type AND foreignKey = :foreignKey";
		$params = [
			"type" => $type,
			"foreignKey" => $foreignKey,
		];
		
		if($del !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $del;
		}
		
		$rows = $this->select($query, $params);
		
		if(count($rows) > 0) { $return = $rows[0]; }
		else { $return = false; }
		return $return;
	}
	
	#Get country
	public function getCountry($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("countries"), "id", $id, $field, $delCheck);
	}
	
	public function getCountryByCode($code, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("countries"), "code2", $code, $field, $delCheck);
	}
	
	#Get region
	public function getRegion($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("regions"), "id", $id, $field, $delCheck);
	}
	
	public function getRegionByCode($code, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("regions"), "code", $code, $field, $delCheck);
	}
	
	#Get city
	public function getCity($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("cities"), "id", $id, $field, $delCheck);
	}
	
	public function getCityByZipCode($zipCode, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("cities"), "zipCode", $zipCode, $field, $delCheck);
	}
	
	public function getCitiesByZipCode($zipCode)
	{
		return $this->select("SELECT * FROM ".$this->tables("cities")." WHERE del = '0' AND zipCode = :zipCode", ["zipCode" => $zipCode]);
	}
	
	#Get city list
	public function getCities($countryCode = "HU")
	{
		$return = [];
		$rows = $this->select("SELECT * FROM ".$this->tables("countries")." WHERE code1 = :code1", ["code1" => $countryCode]);
		if(count($rows) > 0)
		{
			$rows2 = $this->select("SELECT * FROM ".$this->tables("regions")." WHERE country = :country", ["country" => $rows[0]->id]);
			if(count($rows2) > 0)
			{
				$rows3 = $this->select("SELECT id FROM ".$this->tables("cities")." WHERE region = :region", ["region" => $rows2[0]->id]);
				if(count($rows3) > 0)
				{
					foreach($rows3 AS $row) { $return[] = $row->id; }
				}
			}
		}
		
		return $return;
	}
	
	#New address
	public function newAddress($type, $foreignKey, $cityID, $datas)
	{
		$params = [
			"type" => $type,
			"foreignKey" => $foreignKey,
			"cityID" => $cityID,
		];
		if(isset($datas["zipCode"])) { $params["zipCode"] = $datas["zipCode"]; }
		if(isset($datas["city"])) { $params["city"] = $datas["city"]; }
		if(isset($datas["address"])) { $params["address"] = $datas["address"]; }
		if(isset($datas["others"])) { $params["others"] = $datas["others"]; }
		if(isset($datas["comment"])) { $params["comment"] = $datas["comment"]; }
		
		return $this->myInsert($this->tables("addresses"), $params);
	}
}
?>