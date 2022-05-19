<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class User extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"users" => $this->dbPrefix."users",
			"admin" => $this->dbPrefix."users_admin",
			"details" => $this->dbPrefix."users_details",
				"categories" => $this->dbPrefix."users_categories",
				"worklays" => $this->dbPrefix."users_worklays",
			"divisions" => $this->dbPrefix."users_divisions",
			"employee" => $this->dbPrefix."users_employee",
				"employeeEmails" => $this->dbPrefix."users_employee_emails",
			"forgotPasswords" => $this->dbPrefix."users_forgotPasswords",
			"groups" => $this->dbPrefix."users_groups",
			"ranks" => $this->dbPrefix."users_ranks",
			"rights" => $this->dbPrefix."users_rights",
			"positionGroups" => $this->dbPrefix."users_positionGroups",
			"positions" => $this->dbPrefix."users_positions",
			"delReasons" => $this->dbPrefix."users_delReasons",
			"tasks" => $this->dbPrefix."users_tasks",
				"tasksProgress" => $this->dbPrefix."users_tasks_progress",
			"tools" => $this->dbPrefix."users_tools",
				"toolsTypes" => $this->dbPrefix."users_tools_types",	
			"workdresses" => $this->dbPrefix."users_workdresses",
				"workdressClaims" => $this->dbPrefix."users_workdresses_claims",
					"workdressClaimStatuses" => $this->dbPrefix."users_workdresses_claims_statuses",
					"workdressClaimStatusChanges" => $this->dbPrefix."users_workdresses_claims_statusChanges",
					"workdressClaimGiveOutsTakeBacks" => $this->dbPrefix."users_workdresses_claims_giveOuts_takeBacks",
				"workdressTypes" => $this->dbPrefix."users_workdresses_types",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get user
	public function getUserByID($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("users"), "id", $id, $field, $delCheck);
	}
	
	public function getUserByEmail($email, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("users"), "email", $email, $field, $delCheck);
	}
	
	public function getUserByToken($token, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("users"), "token", $token, $field, $delCheck);
	}
	
	public function getUserByRememberToken($token, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("users"), "remember_token", $token, $field, $delCheck);
	}
	
	public function getUsers($deleted = 0, $orderBy = "email")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("users");
		
		if($deleted !== NULL) 
		{ 
			$query .= " WHERE del = :del";
			$params["del"] = $deleted;			
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		$return = $this->select($query, $params); 
		return $return;
	}
	
	#Get rank
	public function getRankByID($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("ranks"), "id", $id, $field, $delCheck);
	}
	
	public function getRankByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("ranks"), "url", $url, $field, $delCheck);
	}
	
	#Get forgot password
	public function getForgotPasswordByHash($hash, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("forgotPasswords"), "hash", $hash, $field, $delCheck);
	}
	
	#Position groups --> Positions --> Users
	public function getPositionGroups($deleted = 0, $orderBy = "orderNumber, nameOut")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("positionGroups");
		
		if($deleted !== NULL) 
		{ 
			$query .= " WHERE del = :del";
			$params["del"] = $deleted;			
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		$return = $this->select($query, $params); 
		return $return;
	}
	
	public function getPositionsByGroup($positionGroup, $deleted = 0, $orderBy = "orderNumber, nameOut")
	{
		$params = [
			"positionGroup" => $positionGroup,
		];
		$query = "SELECT * FROM ".$this->tables("positions")." WHERE positionGroup = :positionGroup";
		
		if($deleted !== NULL) 
		{ 
			$query .= " AND del = :del";
			$params["del"] = $deleted;			
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		$return = $this->select($query, $params); 
		return $return;
	}
	
	public function getUsersByPosition($position, $visible = 1)
	{
		$query = "SELECT u.id FROM ".$this->tables("users")." u INNER JOIN ".$this->tables("admin")." a ON u.id = a.userID WHERE u.del = '0' AND a.del = '0' AND a.position = :position";
		$params = ["position" => $position];
		
		if($visible !== NULL) 
		{ 
			$query .= " AND a.visible = :visible";
			$params["visible"] = $visible;			
		}
		
		$query .= " ORDER BY u.lastName, u.firstName";
		$return = $this->select($query, $params); 
		return $return;
	}
	
	#Get position
	public function getPosition($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("positions"), "id", $id, $field, $delCheck);
	}
	
	public function getPositionByNameOut($nameOut, $field = "", $deleted = 0)
	{
		#Base query
		$params = [
			"nameOut" => $nameOut,
		];
		$query = "SELECT * FROM ".$this->tables("positions")." WHERE nameOut LIKE :nameOut";
		if($deleted !== NULL) 
		{ 
			$query .= " AND del = :del";
			$params["del"] = $deleted;			
		}
		
		#Check multiple founds
		$rows = $this->select($query, $params); 
		if(count($rows) > 1)
		{
			$params["nameOut"] = str_replace("%", "", $params["nameOut"]);
			$rows2 = $this->select($query, $params); 
			if(count($rows2) > 0) { $rows = $rows2; }
		}
		
		#Out
		if(count($rows) > 0)
		{			
			if(!empty($field)) { $return = $rows[0]->$field; }
			else { $return = $rows[0]; }
		}
		else { $return = false; }
		return $return;
	}
	
	#Get admin data
	public function getUserAdminDataByUserID($userID)
	{
		$rows = $this->select("SELECT * FROM ".$this->tables("admin")." WHERE del = '0' AND userID = :userID", ["userID" => $userID]); 
		if(count($rows) > 0)
		{
			if(!empty($field)) { $return = $rows[0]->$field; }
			else { $return = $rows[0]; }
		}
		else { $return = false; }
		return $return;
	}
	
	#Get details data
	public function getUserDetailsByUserID($userID)
	{
		$rows = $this->select("SELECT * FROM ".$this->tables("details")." WHERE del = '0' AND userID = :userID", ["userID" => $userID]); 
		if(count($rows) > 0)
		{
			if(!empty($field)) { $return = $rows[0]->$field; }
			else { $return = $rows[0]; }
		}
		else { $return = false; }
		return $return;
	}
	
	#Get employee
	public function getEmployee($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("employee"), "id", $id, $field, $delCheck);
	}
	
	public function getEmployees($selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("employee")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	public function getActualEmployeeByType($emailType)
	{
		$rows = $this->select("SELECT id FROM ".$this->tables("employee")." WHERE emailType = :emailType ORDER BY date DESC LIMIT 0, 1", ["emailType" => $emailType]);
		$return = (!empty($rows) AND isset($rows[0]) AND !empty($rows[0])) ? $rows[0]->id : false;
		return $return;
	}
	
	#Get employee email
	public function getEmployeeEmail($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("employeeEmails"), "id", $id, $field, $delCheck);
	}
	
	public function getEmployeeEmails($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("employeeEmails")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get category
	public function getCategory($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("categories"), "id", $id, $field, $delCheck);
	}
	
	public function getCategories($selectFields = "*", $deleted = 0, $orderBy = "name")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("categories")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get worklay
	public function getWorklay($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("worklays"), "id", $id, $field, $delCheck);
	}
	
	public function getWorklays($selectFields = "*", $deleted = 0, $orderBy = "name")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("worklays")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get del reason
	public function getDelReason($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("delReasons"), "id", $id, $field, $delCheck);
	}
	
	public function getDelReasons($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("delReasons")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get division
	public function getDivision($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("divisions"), "id", $id, $field, $delCheck);
	}
	
	public function getDivisions($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("divisions")." WHERE id != '0'";
		$params = [];		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get task
	public function getTask($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("tasks"), "id", $id, $field, $delCheck);
	}
	
	public function getTasks($selectFields = "*", $onEntry = NULL, $onExit = NULL, $division = NULL, $deleted = 0, $orderBy = "division, orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("tasks")." WHERE id != '0'";
		$params = [];		
		if($onEntry !== NULL)
		{
			$query .= " AND onEntry = :onEntry";
			$params["onEntry"] = $onEntry;
		}
		if($onExit !== NULL)
		{
			$query .= " AND onExit = :onExit";
			$params["onExit"] = $onExit;
		}
		if($division !== NULL)
		{
			$query .= " AND division = :division";
			$params["division"] = $division;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get task progress
	public function getTaskProgress($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("tasksProgress"), "id", $id, $field, $delCheck);
	}
	
	public function getTaskProgresses($user, $selectFields = "*", $finishedDivision = NULL, $finishedHR = NULL, $deleted = 0, $orderBy = "")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("tasksProgress")." WHERE user = :user";
		$params = [];		
		$params["user"] = $user;
		if($finishedDivision !== NULL)
		{
			$query .= " AND finishedDivision = :finishedDivision";
			$params["finishedDivision"] = $finishedDivision;
		}
		if($finishedHR !== NULL)
		{
			$query .= " AND finishedHR = :finishedHR";
			$params["finishedHR"] = $finishedHR;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get workdress
	public function getWorkdress($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdresses"), "id", $id, $field, $delCheck);
	}
	
	public function getWorkdresses($type = NULL, $userCategories = NULL, $deleted = 0, $orderBy = "name")
	{
		$query = "SELECT * FROM ".$this->tables("workdresses")." WHERE id != '0'";
		$params = [];		
		if($type !== NULL)
		{
			$query .= " AND type = :type";
			$params["type"] = $type;
		}
		if($userCategories !== NULL)
		{
			$query .= " AND (";
			foreach($userCategories AS $i => $worklayID)
			{
				if($i > 0) { $query .= " OR "; }
				$query .= "userCategories LIKE :userCategory".$worklayID;
				$params["userCategory".$worklayID] = "%|".$worklayID."|%";
			}
			$query .= ")";
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		return $this->select($query, $params);
	}
	
	#Get workdress type
	public function getWorkdressType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdressTypes"), "id", $id, $field, $delCheck);
	}
	
	#Get workdress claim-status
	public function getWorkdressClaimStatus($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdressClaimStatuses"), "id", $id, $field, $delCheck);
	}
	
	#Get workdress claim-statuschange
	public function getWorkdressClaimStatusChange($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdressClaimStatusChanges"), "id", $id, $field, $delCheck);
	}
	
	#Get workdress claim
	public function getWorkdressClaim($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdressClaims"), "id", $id, $field, $delCheck);
	}
	
	#Get workdress claim's give-out/take-back
	public function getWorkdressGiveOutTakeBack($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("workdressClaimGiveOutsTakeBacks"), "id", $id, $field, $delCheck);
	}
	
	#Get tools
	public function getTool($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("tools"), "id", $id, $field, $delCheck);
	}
	
	public function getTools($user, $selectFields = "*", $type = NULL, $deleted = 0, $orderBy = "logDate DESC")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("tools")." WHERE user = :user";
		$params = [];		
		$params["user"] = $user;
		if($type !== NULL)
		{
			$query .= " AND type = :type";
			$params["type"] = $type;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get tool-types
	public function getToolType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("toolsTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getToolTypes($selectFields = "*", $deleted = 0, $orderBy = "orderNumber")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("toolsTypes")." WHERE id != '0'";
		$params = [];
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
}
