<?php 
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class File extends Base
{	
	#Tables
	public function tables($name = "")
	{
		$return = [
			"files" => $this->dbPrefix."files",
			"extensions" => $this->dbPrefix."files_extensions",
			"extensionCategories" => $this->dbPrefix."files_extensions_categories",
			"log" => $this->dbPrefix."files_log",
			"types" => $this->dbPrefix."files_types",
		];
		
		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Create database
	public function createDB()
	{
		$tables = $this->tables;
		
		//Creating the tables
		
		$return = [];
		foreach($queries AS $queryName => $query)
		{
			$return[$queryName] = $this->statement($query, [], $connectionData);
		}
		
		return $return;
	}
	
	#Get type
	public function getTypeByField($column, $value, $field = "", $delCheck = 1)
	{
		$table = $this->tables("types");
		return $this->selectByField($table, $column, $value, $field, $delCheck);
	}
	
	public function getTypeByName($name, $field = "", $delCheck = 1)
	{
		return $this->getTypeByField("name", $name, $field, $delCheck);
	}
	
	public function getType($id, $field = "", $delCheck = 0)
	{
		return $this->getTypeByField("id", $id, $field, $delCheck);
	}
	
	#Get extension
	public function getExtensionByField($column, $value, $field = "", $delCheck = 1)
	{
		$table = $this->tables("extensions");
		return $this->selectByField($table, $column, $value, $field, $delCheck);
	}
	
	public function getExtensionByName($name, $field = "", $delCheck = 1)
	{
		return $this->getExtensionByField("name", $name, $field, $delCheck);
	}
	
	public function getExtension($id, $field = "", $delCheck = 0)
	{
		return $this->getExtensionByField("id", $id, $field, $delCheck);
	}
	
	public function getExtensionsByCategory($category)
	{
		return $this->getExtensionByField("category", $category, $field, $delCheck);
	}
	
	#Get extension-category
	public function getExtensionCategoryByField($column, $value, $field = "", $delCheck = 1)
	{
		$table = $this->tables("extensionCategories");
		return $this->selectByField($table, $column, $value, $field, $delCheck);
	}
	
	public function getExtensionCategoryByName($name, $field = "", $delCheck = 1)
	{
		return $this->getExtensionCategoryByField("name", $name, $field, $delCheck);
	}
	
	public function getExtensionCategory($id, $field = "", $delCheck = 0)
	{
		return $this->getExtensionCategoryByField("id", $id, $field, $delCheck);
	}
	
	#Get file
	public function getFileByField($column, $value, $field = "", $delCheck = 0)
	{
		$table = $this->tables("files");
		return $this->selectByField($table, $column, $value, $field, $delCheck);
	}
	
	public function getFileByName($name, $field = "", $delCheck = 1)
	{
		return $this->getFileByField("name", $name, $field, $delCheck);
	}
	
	public function getFile($id, $field = "", $delCheck = 0)
	{
		return $this->getFileByField("id", $id, $field, $delCheck);
	}
	
	public function getFileByToken($token, $field = "", $delCheck = 0)
	{
		return $this->getFileByField("token", $token, $field, $delCheck);
	}
	
	public function getFileByHash($token, $field = "", $delCheck = 0)
	{
		return $this->getFileByField("tokenHashed", $token, $field, $delCheck);
	}
	
	#Get file list
	public function getFileList($typeName, $foreignKey, $deleted = 0)
	{
		$return = [];
		$typeID = $this->getTypeByName($typeName, "id");
		if(!empty($typeID))
		{
			$table = $this->tables("files");
			$params = [
				"type" => $typeID,
				"foreignKey" => $foreignKey,
			];
			
			$query = "SELECT id FROM ".$table." WHERE type = :type AND foreignKey = :foreignKey";
			if($deleted !== NULL) 
			{ 
				$query .= " AND del = :del";
				$params["del"] = $deleted;			
			}
			$query .= " ORDER BY orderNumber";
			
			$return = $this->select($query, $params); 
		}
		else { $return = "error_type_null"; }
		
		return $return;
	}
	
	#Ordering
	public function reOrderFiles($type, $foreignKey)
	{
		return $this->reOrder($this->tables("files"), "del = '0' AND type = :type AND foreignKey = :foreignKey", ["type" => $type, "foreignKey" => $foreignKey], "orderNumber");
	}
	
	public function newOrderFiles($orderType, $currentID, $type, $foreignKey)
	{
		return $this->newOrder($orderType, $currentID, $this->tables("files"), "del = '0' AND type = :type AND foreignKey = :foreignKey", ["type" => $type, "foreignKey" => $foreignKey], "orderNumber");
	}
}
?>