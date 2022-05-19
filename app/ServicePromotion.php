<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class ServicePromotion extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"promotions" => $this->dbPrefix."service_promotions",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get type
	public function getPromotion($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("promotions"), "id", $id, $field, $delCheck);
	}
	
	#Log
	public function getPromotions($activeFrom = NULL, $activeTo = NULL, $brands = NULL, $sites = NULL, $deleted = 0)
	{
		$query = "SELECT * FROM ".$this->tables("promotions")." WHERE id != '0'";
		$params = [];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if($activeFrom !== NULL)
		{
			$query .= " AND (activeFrom IS NULL OR activeFrom = '0000-00-00 00:00:00' OR activeFrom <= :activeFrom)";
			$params["activeFrom"] = $activeFrom;
		}
		
		if($activeTo !== NULL)
		{
			$query .= " AND (activeTo IS NULL OR activeTo = '0000-00-00 00:00:00' OR activeTo >= :activeTo)";
			$params["activeTo"] = $activeTo;
		}
		
		if($brands !== NULL)
		{
			$query .= "AND (";
			$i = 0;
			foreach($brands AS $brand)
			{
				if($i > 0) { $query .= " OR "; }
				$query .= "brands LIKE :brand".$brand;
				$params["brand".$brand] = "%|".$brand."|%";
				$i++;
			}
			$query .= ")";
		}
		
		if($sites !== NULL)
		{
			$query .= "AND (";
			$i = 0;
			foreach($sites AS $site)
			{
				if($i > 0) { $query .= " OR "; }
				$query .= "sites LIKE :site".$site;
				$params["site".$site] = "%|".$site."|%";
				$i++;
			}
			$query .= ")";
		}
		
		$query .= " ORDER BY id DESC";
		
		return $this->select($query, $params);
	}
}
