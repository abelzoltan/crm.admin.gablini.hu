<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Content extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"contents" => $this->Prefix."contents",
			"types" => $this->Prefix."contents_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	public function getType($name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "name", $name, $field, $delCheck);
	}
	
	public function getNews($order = "id DESC")
	{
		$type = $this->getType("hirek");
		return $this->select("SELECT * FROM ".$this->tables("contents")." WHERE del = '0' AND type = :type ORDER BY ".$order, ["type" => $type->id]);
	}
	
	public function getNewsByID($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("contents"), "id", $id, $field, $delCheck);
	}
	
	public function getNewsByOldID($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("contents"), "oldID", $id, $field, $delCheck);
	}
	
	public function getNewsByName($name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("contents"), "name", $name, $field, $delCheck);
	}
}
