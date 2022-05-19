<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\App;

class AppController extends BaseController
{	
	public $currentType;
	public $passwordAfter = "cts7Qfw7JA";
	public $forgotPasswordAfter = "7CvbYFBUmjZIK3E57pwq";
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->dateTime = date("Y-m-d H:i:s");
		$this->modelName = "App";
		$this->model = new \App\App($connectionData);
	}
	
	#Login
	public function login()
	{
		#Datas
		$token = (isset($_REQUEST["token"]) AND !empty($_REQUEST["token"])) ? $_REQUEST["token"] : NULL;
		$email = (isset($_REQUEST["email"]) AND !empty($_REQUEST["email"])) ? $_REQUEST["email"] : NULL;
		$invoice = (isset($_REQUEST["invoice"]) AND !empty($_REQUEST["invoice"])) ? $_REQUEST["invoice"] : NULL;
		
		#Login with token
		if(!empty($token))
		{
			$user = $this->model->getUserByToken($token);
			#Success
			if($user !== false)
			{
				$return = [
					"loginType" => "token",
					"success" => true,
					"message" => "Sikeres bejelentkezés!",
					"token" => $user->token,
					"firstLogin" => false,
					"loginDate" => $this->model->editUserLastLogin($user->id),
					"info" => [],
				]; 
			}
			#Error
			else
			{
				$return = [
					"loginType" => "token",
					"success" => false,
					"message" => "Sikertelen azonosítás!",
					"token" => NULL,
					"firstLogin" => NULL,
					"loginDate" => NULL,
					"info" => ["wrong-token"],
				]; 
			}
		}
		#Login with email + invoice
		elseif(!empty($email) AND !empty($invoice))
		{
			$event = $this->model->getEventByEmailAndInvoice($email, $invoice);
			#Success: there is a match
			if($event !== false)
			{
				#Existing registration --> login again
				$user = $this->model->getUserByAppEvent($event->id);
				if($user !== false)
				{
					$return = [
						"loginType" => "email",
						"success" => true,
						"message" => "Sikeres bejelentkezés!",
						"token" => $user->token,
						"firstLogin" => false,
						"loginDate" => $this->model->editUserLastLogin($user->id),
						"info" => [],
					]; 
				}
				#Not exists
				else
				{
					#Check registration with other invoice
					$userFromOtherEvents = false;
					if(!empty($event->progressCode))
					{
						$otherEvents = $this->model->getEventsByEmailAndProgressCode($event->email, $event->progressCode);
						if(count($otherEvents) > 0)
						{
							foreach($otherEvents AS $otherEvent)
							{
								$user = $this->model->getUserByAppEvent($otherEvent->id);
								if($user !== false)
								{
									$return = [
										"loginType" => "email",
										"success" => true,
										"message" => "Sikeres bejelentkezés!",
										"token" => $user->token,
										"firstLogin" => false,
										"loginDate" => $this->model->editUserLastLogin($user->id),
										"info" => ["registration-with-other-invoice"],
									];
									
									$userFromOtherEvents = true;
									break;
								}
							}
						}
					}
					
					#No other registration --> NEW USER
					if(!$userFromOtherEvents)
					{
						$userID = $this->newUser($event);
						$user = $this->model->getUserByAppEvent($event->id);
						if($user !== false)
						{
							$return = [
								"loginType" => "email",
								"success" => true,
								"message" => "Sikeres bejelentkezés!",
								"token" => $user->token,
								"firstLogin" => true,
								"loginDate" => $this->model->editUserLastLogin($user->id),
								"info" => [],
							]; 
						}
					}
				}
			}
			#Error: no match
			else
			{
				$return = [
					"loginType" => "email",
					"success" => false,
					"message" => "Sikertelen bejelentkezés!",
					"token" => NULL,
					"firstLogin" => NULL,
					"loginDate" => NULL,
					"info" => ["no-match"],
				]; 
			}
		}
		#WRONG LOGIN
		else
		{
			$info = [];
			if(empty($token)) { $info[] = "no-token"; }
			if(empty($email)) { $info[] = "no-email"; }
			if(empty($invoice)) { $info[] = "no-invoice"; }
			
			$return = [
				"loginType" => "unknown",
				"success" => false,
				"message" => "Hiányzó adatok!",
				"token" => NULL,
				"firstLogin" => NULL,
				"loginDate" => NULL,
				"info" => $info,
			]; 
		}
		
		#Log
		$appUser = (isset($user) AND $user !== false) ? $user->id : NULL;
		$this->log($token, $email, $invoice, $appUser, $return);
		
		#Firebase
		if($return["success"] AND $appUser !== NULL)
		{
			if(isset($_REQUEST["firebase_token"]) AND !empty($_REQUEST["firebase_token"]))
			{
				if($user->fireBaseToken != $_REQUEST["firebase_token"]) { $this->model->editUser($user->id, ["fireBaseToken" => $_REQUEST["firebase_token"]]); }
			}
			elseif(isset($_REQUEST["remove_firebase_token"]) AND !empty($_REQUEST["remove_firebase_token"]))
			{
				if(!empty($user->fireBaseToken)) { $this->model->editUser($user->id, ["fireBaseToken" => NULL]); }
			}
		}
		
		#Return
		return $this->json($return);
	}
	
	#Create new user (registration)
	public function newUser($event, $name = NULL, $email = NULL)
	{
		#Event
		$appEvent = $customer = NULL;
		if($event !== false)
		{
			if(!empty($name)) { $name = $event->name; }
			if(!empty($email)) { $email = $event->email; }
			$appEvent = $event->id;
			$customer = $event->customer;
		}
		
		#Details
		$params = [
			"dateRegistration" => date("Y-m-d H:i:s"),
			"name" => $name,
			"email" => $email,
			"token" => uniqid(),
			"hash" => NULL,
			"appEvent" => $appEvent,
			"customer" => $customer,
		];
		
		#Check if token is unique
		while(true)
		{
			$user = $this->model->getUserByToken($params["token"], 0);
			if($user !== false) { $params["token"] = uniqid(); }
			else { break; }
		}
		
		#Create hash
		$params["hash"] = sha1($params["dateRegistration"]." ".$params["email"]." ".$params["token"]." ".$params["appEvent"]);
		
		#Create user in database
		return $this->model->newUser($params);
	}
	
	#Hash password
	public function hashPassword($password)
	{
		return password_hash($password.$this->passwordAfter, PASSWORD_DEFAULT);
	}
	
	public function checkPassword($password, $hash)
	{
		return password_verify($password.$this->passwordAfter, $hash);
	}
	
	#Log login
	public function log($token, $email, $invoice, $appUser, $datas)
	{
		$params = [
			"date" => date("Y-m-d H:i:s"),
			"token" => $token,
			"email" => $email,
			"invoice" => $invoice,
			"success" => ($datas["success"]) ? 1 : 0,
			"appUser" => $appUser,
			"json" => $this->json($datas),
		];
		
		return $this->model->newLogin($params);
	}
	
	#Do the login (session)
	public function doLogin($json)
	{
		$datas = $this->jsonDecode($json);
		if($datas["success"] AND $datas["loginType"] == "token")
		{
			$_SESSION[SESSION_PREFIX."appUserLoggedIn"] = true;
			$_SESSION[SESSION_PREFIX."appUserDatas"] = $datas;
		}
		else { $_SESSION[SESSION_PREFIX."appUserLoggedIn"] = $_SESSION[SESSION_PREFIX."appUserDatas"] = false; }
		
		if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-login", ["text1" => $json]); }
		return $_SESSION[SESSION_PREFIX."appUserLoggedIn"];
	}
	
	#Logout
	public function logout()
	{
		if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-logout", ["vchar1" => $_SESSION[SESSION_PREFIX."appUserDatas"]["token"]]); }
		session_destroy();
	}
	
	#Ügyfélkapu login
	public function ugyfelKapuLoginProcess($user = false, $datas = [])
	{
		#Basic datas
		$return = [
			"error" => (isset($datas["error"])) ? $datas["error"] : NULL,
			"appUserID" => NULL,
			"datas" => $datas,
		];
		
		if(empty($return["error"]))
		{
			#If user exists
			if($user !== false)
			{
				$return["appUserID"] = $user->id;
				
				if($user->del) { $return["error"] = "user-deleted"; }
				else
				{
					#Check service event
					if(empty($user->appEvent))
					{
						$event = $this->model->getLastEventByEmail($user->email);
						if($event !== false) { $this->model->myUpdate($this->model->tables("users"), ["appEvent" => $event->id, "customer" => $event->customer], $user->id); }
					}
					
					#Do login
					$_SESSION[UGYFELKAPU_USER_LOGGED_IN] = true;
					$_SESSION[UGYFELKAPU_USER_ID] = $return["appUserID"];
					
					$this->model->editUserLastLogin($user->id);
				}
			}
			#If user NOT exists
			else { $return["error"] = "user-not-exists"; }
		}
		
		#Log
		$logDatas = [
			"token" => (isset($datas["token"])) ? $datas["token"] : NULL,
			"email" => (isset($datas["email"])) ? $datas["email"] : NULL,
			"invoice" => (isset($datas["invoice"])) ? $datas["invoice"] : NULL,
			"appUser" => $return["appUserID"],
		];
		$this->log($logDatas["token"], $logDatas["email"], $logDatas["invoice"], $logDatas["appUser"], $return);
		
		#Return
		return $return;
	}
	
	public function ugyfelKapuLoginByEmailAndPassword($email = NULL, $password = NULL)
	{
		$user = (!empty($email)) ? $this->model->getUserByEmail($email) : false;
		$datas = ["email" => $email];
		
		if($user !== false)
		{
			if(empty($password)) { $datas["error"] = "password-empty"; }
			elseif(!$this->checkPassword($password, $user->password)) { $datas["error"] = "password-wrong"; }
		}
		
		return $this->ugyfelKapuLoginProcess($user, $datas);
	}
	
	public function ugyfelKapuLoginByToken($token = NULL)
	{
		$user = (!empty($token)) ? $this->model->getUserByToken($token) : false;
		return $this->ugyfelKapuLoginProcess($user, ["token" => $token]);
	}
	
	#Ügyfélkapu remember me
	public function ugyfelKapuSetRememberMe()
	{
		$user = $this->model->getUser($_SESSION[UGYFELKAPU_USER_ID]);
		if($user !== false)
		{
			$rememberToken = sha1($user->id."-".date("YmdHis")."-".$user->token);
			$this->model->myUpdate($this->model->tables("users"), ["rememberToken" => $rememberToken], $user->id);
			setcookie("ugyfelkapuRememberMe", $rememberToken, time() + (86400 * 30), "/"); 
		}
	}
	
	public function ugyfelKapuCheckRememberMe()
	{
		if((!isset($_SESSION[UGYFELKAPU_USER_LOGGED_IN]) OR !$_SESSION[UGYFELKAPU_USER_LOGGED_IN]) AND isset($_COOKIE["ugyfelkapuRememberMe"]) AND !empty($_COOKIE["ugyfelkapuRememberMe"]))
		{
			$user = $this->model->getUserByRememberToken($_COOKIE["ugyfelkapuRememberMe"], "id");
			if($user !== false)
			{
				$_SESSION[UGYFELKAPU_USER_LOGGED_IN] = true;
				$_SESSION[UGYFELKAPU_USER_ID] = $user->id;
				header("Refresh:0");
				exit;
			}
		}
	}
	
	#Ügyfélkapu logout
	public function ugyfelKapuLogout()
	{
		$this->model->myUpdate($this->model->tables("users"), ["rememberToken" => NULL], $_SESSION[UGYFELKAPU_USER_ID]);
		setcookie("ugyfelkapuRememberMe", NULL, time() - 1000, "/");
		
		$_SESSION[UGYFELKAPU_USER_LOGGED_IN] = false;
		$_SESSION[UGYFELKAPU_USER_ID] = NULL;
		
		session_destroy();
	}
	
	#Ügyfélkapu registration
	public function ugyfelKapuRegistration($datas)
	{
		#Basic return array
		$return = [
			"userID" => NULL,
			"datas" => $datas,
			"errors" => [],
		];
		
		#Check POST data
		if(!isset($datas["name"]) OR empty($datas["name"])) { $return["errors"]["nameEmpty"] = "A név megadása kötelező!"; }
		if(!isset($datas["email"]) OR empty($datas["email"])) { $return["errors"]["emailEmpty"] = "Az e-mail cím megadása kötelező!"; }
		
		if(!isset($datas["password1"]) OR empty($datas["password1"]) OR !isset($datas["password2"]) OR empty($datas["password2"])) { $return["errors"]["passwordEmpty"] = "A jelszó megadása kötelező!"; }
		elseif($datas["password1"] != $datas["password2"]) { $return["errors"]["passwordMismatch"] = "A megadott jelszavak nem egyeznek!"; }
		
		if(count($return["errors"]) == 0)
		{
			#Check if user exists with email
			$existingUser = $this->model->getUserByEmail($datas["email"]);
			if($existingUser !== false) { $return["errors"]["userExists"] = "A megadott e-mail cím már szerepel az adatbázisunkban!"; }
			#OKAY
			else
			{
				#Check service event
				$event = $this->model->getLastEventByEmail($datas["email"]);
				
				#Create new user
				$userID = $return["userID"] = $this->newUser($event, $datas["name"], $datas["email"]);
				
				#Others
				$params = [
					"password" => date("YmdHis"),
					"passwordTemp" => $this->hashPassword($datas["password1"]),
					"regOnCustomerPortal" => 1,
					"regConfirmHash" => sha1($datas["email"]."-".uniqid("", true)."-".$userID."-".mt_rand(10000, 99999)),
				];
				
				$this->model->myUpdate($this->model->tables("users"), $params, $userID);
			}
		}
		
		#Return
		return $return;
	}
	
	public function ugyfelKapuRegistrationConfirm($user)
	{
		$params = [
			"password" => $user->passwordTemp,
			"passwordTemp" => NULL,
			"regConfirmHash" => "--confirmedAt--".date("YmdHis")."--".$user->regConfirmHash,
		];
		
		$this->model->myUpdate($this->model->tables("users"), $params, $user->id);
	}
	
	#Forgot password
	public function forgotPassword($email)
	{
		#Get user
		$user = (!empty($email)) ? $this->model->getUserByEmail($email) : false;
		
		#Email OK
		$lastID = NULL;
		if($user !== false)
		{
			#Error: Deleted user
			if($user->del) { $error = "del"; }
			#ACCEPTED
			else
			{
				$error = NULL;
				
				$date = date("Y-m-d H:i:s");
				$token = $user->id."-".date("YmdHis", strtotime($date))."-".$user->token."-".$user->email."-".self::random(10);
				$hash = $this->forgotPasswordHash($token);
				
				$params = [
					"appUser" => $user->id,
					"date" => $date,
					"ip" => $_SERVER["REMOTE_ADDR"],
					"token" => $token,
					"hash" => $hash,
				];
				
				$this->model->statement("UPDATE ".$this->model->tables("forgotPasswords")." SET del = '1', deleted_at = :deleted_at WHERE del = '0' AND appUser = :appUser", ["deleted_at" => $date, "appUser" => $user->id]);
				$lastID = $this->model->myInsert($this->model->tables("forgotPasswords"), $params);
			}	
			
		}
		#Error: Email
		else { $error = "email"; }
		
		#Return
		$return = [
			"error" => $error,
			"user" => $user,
			"lastID" => $lastID,
			"params" => $params,
		];
		return $return;
	}
	
	public function forgotPasswordHash($token)
	{
		return sha1($token.$this->forgotPasswordAfter);
	}
	
	public function getForgotPassword($hash)
	{
		$row = $this->model->getForgotPasswordByHash($hash);
		return ($row !== false AND !$row->del AND !$row->hadUsed AND $row->date >= date("Y-m-d H:i:s", strtotime("-3 days"))) ? $row : false;
	}
	
	public function setForgotPassword($hash, $password1, $password2)
	{	
		$row = $this->getForgotPassword($hash);
		
		if($row === false) { $error = "hash"; }
		elseif(empty($password1)) { $error = "passwordEmpty"; }
		elseif($password1 != $password2) { $error = "passwordMismatch"; }
		else
		{ 
			$error = NULL;
			$password = $this->hashPassword($password1); 
			$this->model->myUpdate($this->model->tables("users"), ["password" => $password], $row->appUser);
			$this->model->myUpdate($this->model->tables("forgotPasswords"), ["hadUsed" => 1, "hadUsedDate" => date("Y-m-d H:i:s")], $row->id);
		}
		
		return $error;
	}
	
	#Get user
	public function getUser($id, $allData = true, $delCheck = 0, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getUser($id, $delCheck); }
		if($row !== false)
		{
			$return = [
				"data" => $row,
				"id" => $row->id,
				"name" => $row->name,
				"email" => $row->email,
				"token" => $row->token,
				"hash" => $row->hash,
			];
			
			return $return;
		}
		else { return false; }
	}
	
	public function getUserByToken($token, $allData = true, $delCheck = 1)
	{
		$row = $this->model->getUserByToken($token, $delCheck);
		if($row !== false) { return $this->getUser($row->id, $allData, $delCheck, $row); }
		else { return false; }
	}
	
	#Get registration list for progression
	public function getRegistrationList()
	{
		$return = [];
		$rows = $this->model->getUsers();
		if($rows !== false)
		{
			$customers = new \App\Http\Controllers\CustomerController;
			foreach($rows AS $row)
			{
				$customer = $customers->model->getCustomer($row->customer);
				$return[$row->id] = [
					"user" => $row,
					"event" => $this->model->getEvent($row->event),
					"customer" => ($customer AND is_object($customer) AND isset($customer->id) AND !empty($customer->id)) ? $customer : false,
				];
			}
		}
		
		return $return;
	}
	
	#Create CSV content for progression
	public function createUserListCSV()
	{
		$content = [];
		$rows = $this->getRegistrationList();
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				$content[] = [
					($row["customer"] !== false) ? iconv("utf-8", "iso-8859-2", $row["customer"]->progressCode) : "",
					iconv("utf-8", "iso-8859-2", $row["user"]->name),
					iconv("utf-8", "iso-8859-2", $row["user"]->dateRegistration),
				];
			}
		}
		
		return $content;
	}
	
	public function saveProgressionCSVOnFTP()
	{
		#Return
		$return = [
			"success" => true,
			"errorMsg" => NULL,
			"rowCount" => 0,
		];
		
		#Connect to FTP
		$ftpConnection = ftp_connect("gablini.hu");
		if($ftpConnection !== false)
		{
			#Login to FTP
			if($ftpLogin = ftp_login($ftpConnection, env("FTP_USER"), env("FTP_PASSWORD")))
			{
				$content = $this->createUserListCSV();
				$return["rowCount"] = count($content);
				
				$file = fopen("php://temp", "w");
				if($return["rowCount"] > 0)
				{
					foreach($content AS $row) { fputcsv($file, $row, ";"); }
				}
				else { fwrite($file, ""); }
				rewind($file);
				
				ftp_fput($ftpConnection, "app_ugyfel_regisztraciok.csv", $file, FTP_BINARY, 0);
				fclose($file);
				ftp_close($ftpConnection);
			}
			else 
			{ 
				$return["success"] = false;
				$return["errorMsg"] = "ftp_login";
			}
		}
		else 
		{ 
			$return["success"] = false;
			$return["errorMsg"] = "ftp_connect";
		}
		
		return $return;
	}
	
	#Change loyalty card number
	public function changeLoyaltyCardNumber($userID, $datas = [])
	{
		$return = [
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
		];
		
		#Everything is OK - Database insert
		if($return["type"] == "success")
		{
			$loyaltyCard = (isset($datas["loyaltyCard"]) AND !empty($datas["loyaltyCard"])) ? $datas["loyaltyCard"] : NULL;
			$this->model->myUpdate($this->model->tables("users"), ["loyaltyCard" => $loyaltyCard], $userID);			
		}
		
		#Return
		return $return;
	}
	
	#JSON encode
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
	
	#JSON decode
	public function jsonDecode($json)
	{
		return json_decode($json, true);
	}
}