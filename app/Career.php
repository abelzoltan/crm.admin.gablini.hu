<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Career extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"applies" => $this->dbPrefix."career_applies",
			"categories" => $this->dbPrefix."career_categories",
			"from" => $this->dbPrefix."career_from",
			"jobs" => $this->dbPrefix."career_jobs",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get job
	public function getJob($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("jobs"), "id", $id, $field, $delCheck);
	}
	
	public function getJobByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("jobs"), "url", $url, $field, $delCheck);
	}
	
	public function getJobs($deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT * FROM ".$this->tables("jobs")." WHERE id != '0'";
		$params = [];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get from
	public function getFrom($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("from"), "id", $id, $field, $delCheck);
	}
	
	public function getFromList($deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT * FROM ".$this->tables("from")." WHERE id != '0'";
		$params = [];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get category
	public function getCategory($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("categories"), "id", $id, $field, $delCheck);
	}
	
	public function getCategories($deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT * FROM ".$this->tables("categories")." WHERE id != '0'";
		$params = [];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get apply
	public function getApply($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("applies"), "id", $id, $field, $delCheck);
	}
	
	public function getApplies($deleted = 0, $orderBy = "id DESC")
	{
		$query = "SELECT * FROM ".$this->tables("applies")." WHERE id != '0'";
		$params = [];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
}
