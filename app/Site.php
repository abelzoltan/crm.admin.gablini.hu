<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Site extends Base
{	
	#Tables
	public function tables($name = "")
	{
		$return = [
			"sites" => $this->dbPrefix."sites",
			"sliders" => $this->dbPrefix."sites_sliders",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get site	
	public function getSite($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("sites"), "id", $id, $field, $delCheck);
	}
	
	public function getSiteByBaseName($baseName, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("sites"), "baseName", $baseName, $field, $delCheck);
	}
	
	public function getSiteByName($name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("sites"), "name", $name, $field, $delCheck);
	}
	
	public function getSiteByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("sites"), "url", $url, $field, $delCheck);
	}
	
	#Get all sites
	public function getSites($orderBy = "url")
	{
		return $this->select("SELECT * FROM ".$this->tables("sites")." WHERE del = '0' ORDER BY ".$orderBy);
	}
	
	#Get active slides
	public function getActiveSlides($site)
	{
		$date = date("Y-m-d H:i:s");
		return $this->select("SELECT id FROM ".$this->tables("sliders")." WHERE del = '0' AND site = :site AND (dateFrom IS NULL OR dateFrom <= :dateFrom) AND (dateTo IS NULL OR dateTo >= :dateTo) ORDER BY orderNumber, id", ["site" => $site, "dateFrom" => $date, "dateTo" => $date]);
	}
	
	#Get slide
	public function getSlide($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("sliders"), "id", $id, $field, $delCheck);
	}
	
	public function getSlides($site, $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT id FROM ".$this->tables("sliders")." WHERE site = :site";
		$params = ["site" => $site];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;

		return $this->select($query, $params);
	}
}
