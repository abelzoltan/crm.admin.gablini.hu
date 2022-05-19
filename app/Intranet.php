<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Intranet extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"maintenance" => $this->dbPrefix."intranet_it_maintenance",
			"report" => $this->dbPrefix."intranet_it_report",
			"toner" => $this->dbPrefix."intranet_it_toner",
			"printers" => $this->dbPrefix."intranet_it_printers",
			
			"documents" => $this->dbPrefix."intranet_documents",
			
			"links" => $this->dbPrefix."intranet_links",
			
			"rooms" => $this->dbPrefix."intranet_reservations_rooms",
			"cars" => $this->dbPrefix."intranet_reservations_cars",
			
			"programs" => $this->dbPrefix."intranet_programs",
				"programUsers" => $this->dbPrefix."intranet_programs_users",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get IT maintaince
	public function getMaintenance($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("maintenance"), "id", $id, $field, $delCheck);
	}
	
	#Get IT report
	public function getReport($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("report"), "id", $id, $field, $delCheck);
	}
	
	#Get IT toner
	public function getToner($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("toner"), "id", $id, $field, $delCheck);
	}
	
	#Get IT printer
	public function getPrinter($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("printers"), "id", $id, $field, $delCheck);
	}
	
	public function getPrinters($selectFields = "*", $deleted = 0, $orderBy = "brand, name")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("printers")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get Room Reservation
	public function getRoom($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("rooms"), "id", $id, $field, $delCheck);
	}
	
	#Get Car Reservation
	public function getCar($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("cars"), "id", $id, $field, $delCheck);
	}
	
	#Get program
	public function getProgram($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("programs"), "id", $id, $field, $delCheck);
	}
	
	public function getPrograms($user = NULL, $deleted = 0, $orderBy = "programDate DESC")
	{
		$query = "SELECT * FROM ".$this->tables("programs")." WHERE id != '0'";
		$params = [];		
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
	
	#Get program user
	public function getProgramUser($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("programUsers"), "id", $id, $field, $delCheck);
	}
	
	public function getProgramUsers($program, $deleted = 0, $orderBy = "userName")
	{
		$query = "SELECT * FROM ".$this->tables("programUsers")." WHERE program = :program";
		$params = ["program" => $program];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get actuality link
	public function getActualityLink($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("links"), "id", $id, $field, $delCheck);
	}
	
	public function getActualityLinks($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("links")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get document
	public function getDocument($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("documents"), "id", $id, $field, $delCheck);
	}
	
	public function getDocuments($level = NULL, $category = NULL, $selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("documents")." WHERE id != '0'";
		$params = [];		
		if($level !== NULL)
		{
			$query .= " AND level = :level";
			$params["level"] = $level;
		}
		if($category !== NULL)
		{
			$query .= " AND category = :category";
			$params["category"] = $category;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
}
