<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Base extends Model
{
	public $dbPrefix = "";
	public $dbConnection;
	public $dbConnectionID;
	public $DB;
	
	#Connection
	public function __construct($connectionData = [])
	{
		#Set and store connection datas
		$this->dbConnection = $connectionData;
		
		if(empty($connectionData)) { $this->dbPrefix = $this->checkDefine("MYSQL_PREFIX", ""); }
		else 
		{ 
			if(isset($connectionData["prefix"]) AND !empty($connectionData["prefix"])) { $prefix = $connectionData["prefix"]; }
			else { $prefix = ""; }
			$this->dbPrefix = $prefix; 
		}
		
		if(isset($connectionData["name"]) AND !empty($connectionData["name"])) { $name = $connectionData["name"]; }
		else { $name = ""; }
		$this->DB = DB::connection($name);
	}

	#Now
	public function now()
	{
		return date("Y-m-d H:i:s");
	}
	
	#Unique field check (f.e: token, key, ...)
	public function checkUniqueField($table, $valueKey, $value, $params, $id = 0, $primaryKey = "id")
	{
		$query = "SELECT * FROM ".$table." WHERE ".$primaryKey." != :primaryKey AND ".$valueKey." = :valueKey";
		foreach($params AS $key => $val)
		{
			$query .= " AND ".$key." = :".$key;
		}
		$params["primaryKey"] = $id;
		$params["valueKey"] = $value;
		
		return $this->select($query, $params);
	}
	
	# Database functions -------------------------------------------------------
	
	public function checkDefine($constantName, $defaultValue = "")
	{
		if(defined($constantName)) { return constant($constantName); }
		else { return $defaultValue; }
	}
	
	#Select queries
	public function select($query, $params = [])
	{		
		$stmt = $this->DB->getPdo()->prepare($query);
		$stmt->execute($params);
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000") { $return = $stmt->fetchAll(); }
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	public function selectByField($table, $column, $value, $field = "", $delCheck = 1)
	{
		if(!empty($field)) { $queryField = $field; }
		else { $queryField = "*"; }
		$query = "SELECT ".$queryField." FROM ".$table." WHERE ".$column." = :value";	
		if($delCheck) { $query .= " AND del = '0'"; }

		$stmt = $this->DB->getPdo()->prepare($query);
		$stmt->execute(["value" => $value]);
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000")
		{
			if(!empty($field)) { $return = $stmt->fetchColumn(); } 
			else { $return = $stmt->fetch(); }
		}
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	public function selectByID($table, $id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($table, "id", $id, $field, $delCheck);
	}
	
	public function selectByName($table, $name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($table, "name", $name, $field, $delCheck);
	}
	
	public function selectListFromTable($table, $search = [], $orderBy = "id DESC", $limit = NULL, $deleted = 0)
	{	
		$query = "SELECT * FROM ".$table." WHERE id != '0'";		
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
					
					if($searchItem[1] == "IN" OR $searchItem[1] == "NOT IN") { $query .= " AND ".$searchItem[0]." ".$searchItem[1]."(".implode(", ", $searchItem[2]).")"; }
					else 
					{ 
						$query .= " AND ".$searchItem[0]." ".$searchItem[1]." :".$searchItem[0].$i;  
						$params[$searchItem[0].$i] = $searchItem[2];
					}
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
	
	#Get whole table
	public function selectWholeTable($table, $del = NULL, $orderBy = "id")
	{
		$query = "SELECT * FROM ".$table;
		$params = [];
		if($del !== NULL) 
		{ 
			$query .= " WHERE del = :del";
			$params["del"] = $del; 
		}
		
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#General statements
	public function statement($query, $params = [])
	{
		$stmt = $this->DB->getPdo()->prepare($query);
		$stmt->execute($params);
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000") 
		{ 
			$queryString = $stmt->queryString;
			if(stripos($query, "select") === 0) { $return = $stmt->fetchAll(); }
			elseif(stripos($query, "insert into") === 0)
			{
				$words = explode(" ", $query);
				$table = $words[2];
				
				$stmt = $this->DB->getPdo()->prepare("SELECT * FROM ".$table." WHERE id = :id");
					$stmt->execute(["id" => $this->lastInsertId()]);
				if($row = $stmt->fetch()) { $return = $row; }
				else { $return = $queryString; }
			}
			else { $return = $queryString; } 
		}
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	#Insert	
	public function insert($table, $params)
	{
		$fields = array_keys($params);
		$fieldList = "created_at, ".implode(", ", $fields);
		$valueList = ":created_at, :".implode(", :", $fields);
		$params["created_at"] = date("Y-m-d H:i:s");

		$stmt = $this->DB->getPdo()->prepare("INSERT INTO ".$table." (".$fieldList.") VALUES (".$valueList.")");
		$stmt->execute($params);
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000") { $return = $this->DB->getPdo()->lastInsertId(); }
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	public function myInsert($table, $params)
	{
		return $this->insert($table, $params);
	}
	
	#Update
	public function myUpdate($table, $params, $id)
	{
		$fields = array_keys($params);
		$fieldListArray = [];
		foreach($fields AS $field) { $fieldListArray[] = $field." = :".$field; }
		
		$fieldList = "updated_at = :updated_at, ".implode(", ", $fieldListArray);
		$params["id"] = $id;
		$params["updated_at"] = date("Y-m-d H:i:s");
		
		$stmt = $this->DB->getPdo()->prepare("UPDATE ".$table." SET ".$fieldList." WHERE id = :id");
		$stmt->execute($params);
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000") { $return = $id; }
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	#Delete
	public function myDelete($table, $id, $really = 0)
	{
		if($really) 
		{ 
			$stmt = $this->DB->getPdo()->prepare("DELETE FROM ".$table." WHERE id = :id");
			$stmt->execute(["id" => $id]);
		}
		else 
		{ 
			$stmt = $this->DB->getPdo()->prepare("UPDATE ".$table." SET del = '1', deleted_at = :deleted_at WHERE id = :id");
			$stmt->execute(["id" => $id, "deleted_at" => date("Y-m-d H:i:s")]);
		}
		
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000") { $return = $id; }
		else { $return = self::stmtErrorInfo($errorInfo); }	
		
		return $return;
	}
	
	#Create table with default fields
	public function createTable($name)
	{
		$return = [];
		
		$query = "
			CREATE TABLE IF NOT EXISTS `".$name."` (
			  `id` int(11) NOT NULL COMMENT 'Primary Key',
			  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp on insert',
			  `updated_at` datetime DEFAULT NULL COMMENT 'Timestamp on update',
			  `deleted_at` datetime DEFAULT NULL COMMENT 'Timestamp on delete',
			  `del` int(1) DEFAULT '0' COMMENT '1 = deleted, 0 = active'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		";	
		$stmt = $this->DB->getPdo()->prepare($query);
		$stmt->execute();
		$return["create"] = self::stmtErrorInfo($stmt->errorInfo());
		
		$stmt = $this->DB->getPdo()->prepare("ALTER TABLE `".$name."` ADD PRIMARY KEY(`id`)");
		$stmt->execute();
		$return["primaryKey"] = self::stmtErrorInfo($stmt->errorInfo());
		
		$stmt = $this->DB->getPdo()->prepare("ALTER TABLE `".$name."` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key'");
		$stmt->execute();
		$return["autoIncrement"] = self::stmtErrorInfo($stmt->errorInfo());
		
		return $return;
	}
	
	#Export table
	public function exportTable($table, $headerRow = 1, $sql = "", $params = [], $charset = "iso-8859-2")
	{
		#Data
		$stmt = $this->DB->getPdo()->prepare("SHOW COLUMNS FROM ".$table);
			$stmt->execute();
		$errorInfo = $stmt->errorInfo();
		if($errorInfo[0] == "00000")
		{
			$query = "SELECT * FROM ".$table;
			if(!empty($sql)) { $query .= " ".$sql; }
			$stmt2 = $this->DB->getPdo()->prepare($query);
				$stmt2->execute($params);	
			$errorInfo2 = $stmt->errorInfo();
			if($errorInfo2[0] == "00000")
			{
				#File data
				$fileName = $table."_".date("YmdHis").".csv";
				header("Content-Type: text/csv; charset=".$charset);
				header("Content-Disposition: attachment; filename=".$fileName);
				$output = fopen('php://output', 'w');
				
				#Header row
				if($headerRow)
				{
					$headerRowOut = [];
					while($column = $stmt->fetch(PDO::FETCH_ASSOC)) 
					{ 
						if($charset == "utf-8") { $headerRowOut[] = $column["Field"]; }
						else { $headerRowOut[] = iconv("utf-8", $charset, $column["Field"]); }
					}
					fputcsv($output, $headerRowOut, ";");
				}
				
				#Rows	
				while($row = $stmt2->fetch(PDO::FETCH_ASSOC)) 
				{ 
					$rowItems = [];
					foreach($row AS $field)
					{
						if($charset == "utf-8") { $rowItems[] = $field; }
						else { $rowItems[] = iconv("utf-8", $charset, $field); }
					}
					fputcsv($output, $rowItems, ";");
				}
			}
			else { return self::stmtErrorInfo($errorInfo2); }
		}
		else { return self::stmtErrorInfo($errorInfo); }
	}
	
	#Table rows ordering: re-index, new order
	#Re-order
	public function reOrder($table, $where = "del = '0'", $params = [], $field = "orderNumber")
	{
		#Query
		$query = "SELECT * FROM ".$table;
		if(!empty($where)) { $query .= " WHERE ".$where; }
		$query .= " ORDER BY ".$field;
		
		#Rows
		$rows = $this->select($query, $params);
		$i = 1;
		if(isset($rows[0]) AND !empty($rows[0]))
		{
			foreach($rows AS $row)
			{
				$this->myUpdate($table, [$field => $i], $row->id);
				$i++;
			}
		}
		
		#Return order number of new element
		return $i;
	}
	
	#Table rows ordering: re-index + new order
	#New order
	public function newOrder($type, $currentID, $table, $where = "del = '0'", $params = [], $field = "orderNumber")
	{
		$return = "";
		
		#Re-index
		$orderReturn = $this->reOrder($table, $where, $params, $field);
		$rowCount = $orderReturn - 1;

		#Current index
		$currentIndex = $this->selectByID($table, $currentID, $field);
		
		#Check the ordering
		$okay = true;
		if($type == "up") 
		{ 
			$newIndex = $currentIndex - 1; 
			if($currentIndex <= 1) 
			{ 
				$okay = false; 
				$return = "first-element-up";
			} 
		}
		elseif($type == "down") 
		{ 
			$newIndex = $currentIndex + 1; 
			if($currentIndex >= $rowCount) 
			{ 
				$okay = false; 
				$return = "last-element-down";
			} 
		}
		else
		{
			$okay = false; 
				$return = "unknown";
		}
		
		#New order
		if($okay)
		{
			#Next/previous row
			$paramsThis = $params;
			$paramsThis["orderNumber"] = $currentIndex;
			$paramsThis["newOrderNumber"] = $newIndex;
			
			$query = "UPDATE ".$table." SET ".$field." = :orderNumber WHERE ".$field." = :newOrderNumber";
			if(!empty($where)) { $query .= " AND ".$where; }
			$this->statement($query, $paramsThis);
			
			#Current row
			$this->myUpdate($table, [$field => $newIndex], $currentID);
			
			#Return value
			$return = true;
		}
		
		#Return
		return $return;
	}
}
