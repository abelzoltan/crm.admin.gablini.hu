<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class App extends Cron
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"events" => $this->dbPrefix."app_events",
			"users" => $this->dbPrefix."app_users",
				"forgotPasswords" => $this->dbPrefix."app_users_forgotPasswords",
			"logins" => $this->dbPrefix."app_logins",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#User
	public function getUser($id, $delCheck = 0)
	{
		$row = $this->selectByField($this->tables("users"), "id", $id, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getUserByToken($token, $delCheck = 1)
	{
		$row = $this->selectByField($this->tables("users"), "token", $token, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getUserByEmail($email, $delCheck = 1)
	{
		$row = $this->selectByField($this->tables("users"), "email", $email, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getUserByRememberToken($rememberToken, $delCheck = 1)
	{
		$row = $this->selectByField($this->tables("users"), "rememberToken", $rememberToken, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getUserByAppEvent($appEvent, $delCheck = 1)
	{
		$row = $this->selectByField($this->tables("users"), "appEvent", $appEvent, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getUsers($dateRegistrationFrom = NULL, $dateRegistrationTo = NULL, $deleted = 0)
	{
		$query = "SELECT * FROM ".$this->tables("users")." WHERE id != '0'";
		$params = [];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"]= $deleted;
		}
		
		if($dateRegistrationFrom !== NULL)
		{
			$query .= " AND dateRegistration >= :dateRegistrationFrom";
			$params["dateRegistrationFrom"] = $dateRegistrationFrom;
		}
		if($dateRegistrationTo !== NULL)
		{
			$query .= " AND dateRegistration <= :dateRegistrationTo";
			$params["dateRegistrationTo"] = $dateRegistrationTo;
		}
		
		$rows = $this->select($query, $params);
		return (count($rows) > 0) ? $rows : false;
	}
	
	public function editUserLastLogin($id)
	{
		$date = date("Y-m-d H:i:s");
		$this->myUpdate($this->tables("users"), ["dateLastLogin" => $date], $id);
		return $date;
	}
	
	public function newUser($params)
	{
		return $this->myInsert($this->tables("users"), $params);
	}
	
	public function editUser($id, $params)
	{
		return $this->myUpdate($this->tables("users"), $params, $id);
	}
	
	#Forgot password
	public function getForgotPassword($id, $delCheck = 0)
	{
		$row = $this->selectByField($this->tables("forgotPasswords"), "id", $id, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getForgotPasswordByHash($hash, $delCheck = 0)
	{
		$row = $this->selectByField($this->tables("forgotPasswords"), "hash", $hash, "", $delCheck);		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	#Login
	public function newLogin($params)
	{
		return $this->myInsert($this->tables("logins"), $params);
	}
	
	#Events
	public function getEvent($id, $delCheck = 0)
	{
		$row = $this->selectByField($this->tables("events"), "id", $id, "", $delCheck);
		
		return ($row AND is_object($row) AND isset($row->id) AND !empty($row->id)) ? $row : false;
	}
	
	public function getEventByEmailAndInvoice($email, $invoice, $deleted = 0)
	{
		$query = "SELECT * FROM ".$this->tables("events")." WHERE email = :email AND invoice = :invoice";
		$params = [
			"email" => $email,
			"invoice" => $invoice,
		];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"]= $deleted;
		}
		
		$rows = $this->select($query, $params);
		return (count($rows) > 0 AND is_object($rows[0]) AND isset($rows[0]->id) AND !empty($rows[0]->id)) ? $rows[0] : false;
	}
	
	public function getEventsByEmailAndProgressCode($email, $progressCode, $deleted = 0)
	{
		$query = "SELECT * FROM ".$this->tables("events")." WHERE email = :email AND progressCode = :progressCode";
		$params = [
			"email" => $email,
			"progressCode" => $progressCode,
		];
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"]= $deleted;
		}
		
		return $this->select($query, $params);
	}
	
	public function newEvent($params)
	{
		return $this->myInsert($this->tables("events"), $params);
	}
}
