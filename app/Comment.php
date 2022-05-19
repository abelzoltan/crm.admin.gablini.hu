<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Comment extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"comments" => $this->dbPrefix."comments",
			"types" => $this->dbPrefix."comments_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get comment
	public function getComment($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("comments"), "id", $id, $field, $delCheck);
	}
	
	public function getComments($type = NULL, $foreignKey = NULL, $user = NULL, $deleted = 0, $selectFields = "*", $orderBy = "date DESC")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("comments")." WHERE id != '0'";
		if($type !== NULL)
		{
			$query .= " AND type = :type";
			$params["type"] = $deleted;
		}
		if($foreignKey !== NULL)
		{
			$query .= " AND foreignKey = :foreignKey";
			$params["foreignKey"] = $foreignKey;
		}
		if($user !== NULL)
		{
			$query .= " AND user = :user";
			$params["user"] = $user;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
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
	
	public function getTypes($deleted = 0, $selectFields = "*", $orderBy = "url")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("types")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
}
