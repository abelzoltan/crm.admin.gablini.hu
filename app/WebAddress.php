<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class WebAddress extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"addresses" => $this->dbPrefix."webAddresses",
			"categories" => $this->dbPrefix."webAddresses_categories",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get address
	public function getAddress($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("addresses"), "id", $id, $field, $delCheck);
	}
	
	#Get addresses
	public function getAddresses($search = [], $orderBy = "name", $limit = NULL, $deleted = 0)
	{
		return $this->getListFromTable($this->tables("addresses"), $search, $orderBy, $limit, $deleted);
	}
	
	#Get categories
	public function getCategory($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("categories"), "id", $id, $field, $delCheck);
	}
	
	public function getCategoryByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("categories"), "url", $url, $field, $delCheck);
	}
	
	#Get categories
	public function getCategories($search = [], $orderBy = "name", $limit = NULL, $deleted = 0)
	{
		return $this->getListFromTable($this->tables("categories"), $search, $orderBy, $limit, $deleted);
	}
	
	#Get list
	public function getListFromTable($table, $search = [], $orderBy = "id DESC", $limit = NULL, $deleted = 0)
	{	
		$query = "SELECT * FROM ".$table."  WHERE id != '0'";		
		$params = [];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";	
			$params["del"] = $deleted;
		}

		if(count($search) > 0)
		{
			$i = 0;
			foreach($search AS $searchItem)
			{
				if(count($searchItem) == 3) 
				{ 
					$query .= " AND ".$searchItem[0]." ".$searchItem[1]." :".$searchItem[0].$i; 
					$params[$searchItem[0].$i] = $searchItem[2];
				}
				elseif($searchItem[1] == "IS NULL" OR $searchItem[1] == "IS NOT NULL") { $query .= " AND ".$searchItem[0]." ".$searchItem[1]; }
				else
				{ 
					$query .= " AND ".$searchItem[0]." = :".$searchItem[0].$i; 
					$params[$searchItem[0].$i] = $searchItem[1];
				}
				
				$i++;
			}
		}

		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }	
		if(!empty($limit)) { $query .= " LIMIT ".$limit; }
		return $this->select($query, $params);
	}
}
