<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\User;

class UserController extends BaseController
{	
	public $model;
	public $data;
	public $picDir = "//admin.gablini.hu/cdn/gablini.hu/images/munkatarsak/500/";
	
	public $passwordAfter = '$2a$07$fagsufGgrThstRfgWpdjgsZha';
	public $forgotPasswordAfter = "YxZJRe6sfHs7nQm";
	public $redirectLoginSuccess = "";
	public $redirectLoginError = "";
	public $loginAcceptedRanks;
	
	public $registrationRank = 2;
	public $registrationRequired;
	
	public $profile;
	public $profileRequired;
	
	public $nameUsed = 1;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		#Model
		$this->modelName = "User";
		$this->model = new \App\User($connectionData);
		
		#Get user
		if(!empty($userID)) { $this->data = $this->getUser($userID); }
		
		#Registration required fields
		$this->registrationRequired = ["lastName", "firstName", "email", "password1", "password2"];
		
		#Profile fields
		$this->profileData = ["lastName", "firstName", "email", "password1", "password2"];
		$this->profileRequired = ["lastName", "firstName", "email"];
	}
	
	#Hash password
	public function password($password)
	{
		return crypt($password, $this->passwordAfter);
	}
	
	#Get user by ID
	public function getUser($id = NULL, $allData = true)
	{
		if($id === NULL AND defined("USERID")) { $id = USERID; }
		$return = [];
		$return["data"] = $user = $this->model->getUserByID($id);
		$return["admin"] = $admin = $this->model->getUserAdminDataByUserID($id);
		$return["details"] = $details = $this->model->getUserDetailsByUserID($id);
		$return["rank"] = $this->model->getRankByID($user->rank);
		$return["rankNumber"] = $return["rank"]->orderNumber;
		$return["id"] = $user->id;
		$return["token"] = $user->token;
		$return["tokenHashed"] = sha1($user->token);
		$return["name1"] = $user->lastName." ".$user->firstName;
		$return["name2"] = $user->firstName." ".$user->lastName;
		$return["name"] = $return["name".$this->nameUsed];
		$return["email"] = $user->email;
		
		$file = new \App\Http\Controllers\FileController;
		$return["profilePic"] = $this->picDir.$user->id.".jpg";
		$return["profilePicAdmin"] = (defined("PATH_WEB")) ? PATH_WEB."pics/admin-profile-pic.png" : env("PATH_ROOT_WEB")."pics/admin-profile-pic.png";
		$return["documents"] = $file->getFileList("users-documents", $user->id);
		$return["innerDocuments"] = $file->getFileList("users-documents-inner", $user->id);
		if(!empty($user->pic))
		{
			$return["pic"] = $file->getFile($user->pic);
			if(file_exists($return["pic"]["path"]["inner"])) { $return["profilePic"] = $return["profilePicAdmin"] = $return["pic"]["path"]["web"]; }
		}
			
		if(isset($admin->id) AND !empty($admin->id))
		{
			$return["phone"] = $admin->phone;
			$return["phoneLink"] = str_replace(" ", "", $admin->phone);
			
			if(empty($admin->position)) { $return["position"] = new \stdClass; }
			else { $return["position"] = $this->model->getPosition($admin->position); }
			
			if(empty($admin->carBrands)) { $return["brands"] = []; }
			else { $return["brands"] = explode("|", $admin->carBrands); }
			
			if(empty($admin->premiseAddresses)) { $return["premiseAddresses"] = []; }
			else { $return["premiseAddresses"] = explode("|", $admin->premiseAddresses); }
			
			if(empty($admin->spokenLangs)) { $return["langs"] = []; }
			else { $return["langs"] = explode("|", $admin->spokenLangs); }
		}
		else
		{
			$return["phone"] = $return["phoneLink"] = "";
			$return["brands"] = $return["premiseAddresses"] = $return["langs"] = [];
			$return["position"] = new \stdClass;
		}
		
		$return["premise"] = [];
		$return["commendUser"] = [];
		if(isset($details->id) AND !empty($details->id) AND !empty($details->premiseAddress))
		{ 
			$GLOBALS["CARS"] = new \App\Http\Controllers\CarController;
			$premises = new \App\Http\Controllers\PremiseController;
			// $return["premise"] = $premises->getAddress($details->premiseAddress, false);
			if(!empty($details->commendUser)) { $return["commendUser"] = $this->getUser($details->commendUser); }
		}
		
		$return["tasks"] = $this->getTaskProgresses($user->id);
		$return["entryFinished"] = $return["tasks"]["allFinishedEntry"];
		$return["exitFinished"] = $return["tasks"]["allFinishedExit"];
		$return["category"] = $return["workLay"] = [];
		if(isset($details->id) AND !empty($details->id))
		{
			$return["category"] = (!empty($details->category)) ? $this->getCategory($details->category) : [];
			$return["workLay"] = (!empty($details->workLay)) ? $this->getWorklay($details->workLay) : [];
		}
		
		return $return;
	}
	
	public function getUserByEmail($email)
	{
		$id = $this->model->getUserByEmail($email, "id");
		return $this->getUser($id);
	}
	
	public function getUserByToken($email)
	{
		$id = $this->model->getUserByToken($email, "id");
		return $this->getUser($id);
	}
	
	public function getUsers($deleted = 0, $key = "id", $orderBy = "email")
	{
		$return = [
			"all" => [],
			"ranks" => [],
		];
		if(empty($key)) { $key = "id"; }
		$rows = $this->model->getUsers($deleted, $orderBy);		
		foreach($rows AS $row) 
		{ 
			$return["all"][$row->$key] = $return["ranks"][$row->rank][$row->$key] = $this->getUser($row->id, false); 
		}		
		return $return;
	}
	
	public function getUsersByPosition($premise = NULL, $brand = NULL, $visible = 1)
	{
		$return = [];
		$positionGroups = $this->model->getPositionGroups();
		if(count($positionGroups) > 0)
		{
			foreach($positionGroups AS $positionGroup)
			{
				$return[$positionGroup->id] = [
					"group" => $positionGroup,
					"positions" => [],
					"users" => [],
					"usersByPosition" => [],
				];
				$positions = $this->model->getPositionsByGroup($positionGroup->id);
				if(count($positions) > 0)
				{
					foreach($positions AS $position)
					{
						$return[$positionGroup->id]["positions"][$position->id] = $position;
						$users = $this->model->getUsersByPosition($position->id, $visible);
						if(count($users) > 0)
						{
							foreach($users AS $userRow)
							{
								$user = $this->getUser($userRow->id);
								
								$premises = explode("|", $user["admin"]->premiseAddresses);
								$brands = explode("|", $user["admin"]->carBrands);
								if(($premise === NULL OR in_array($premise, $premises)) AND ($brand === NULL OR in_array($brand, $brands)))
								{
									$return[$positionGroup->id]["users"][$user["data"]->id] = $user;
									$return[$positionGroup->id]["usersByPosition"][$position->id][$user["data"]->id] = $user;
								}
							}
						}
					}
				}
			}
		}
		return $return;
	}
	
	#Update user
	public function editUser($id, $datas)
	{
		return $this->model->myUpdate($this->model->tables("users"), $datas, $id);
	}
	
	#Delete user
	public function delUser($id)
	{
		return $this->model->myDelete($this->model->tables("users"), $id);
	}
	
	#Get languages
	public function getLangs()
	{
		return [
			"en" => "Angol",
			"de" => "Német",
			"fr" => "Francia",
			"ro" => "Román",
			"ru" => "Orosz",
			"cz" => "Cseh",
			"sk" => "Szlovák",
		];
	}
	
	#User profile = details out
	public function userProfile($user)
	{
		#Basic
		$GLOBALS["CARS"] = new \App\Http\Controllers\CarController;
		$return = [];
		$return["user"] = $user;
		$return["admin"] = $user["admin"];
		$return["details"] = $user["details"];
		
		#Datas
		$return["id"] = $user["id"];
		$return["name"] = $user["name"];
		$return["message"] = nl2br($user["details"]->detailsInfo);
		$return["workStart"] = $return["workLay"] = $return["premiseAddress"] = NULL;
		
		#Info email datas
		$return["infoEmailSubject"] = $user["details"]->infoEmailSubject;
		$return["infoEmailTxt"] = $user["details"]->infoEmailTxt;
		
		$return["infoEmailUserIDs"] = explode("|", $user["details"]->infoEmailUsers);
		$return["infoEmailUsers"] = [];
		if(!empty($user["details"]->infoEmailUsers))
		{
			foreach($return["infoEmailUserIDs"] AS $emailUserID) { $return["infoEmailUsers"][$emailUserID] = $this->getUser($emailUserID); }
		}
		
		#Entry email datas
		$return["entryEmailSubject"] = $user["details"]->entryEmailSubject;
		$return["entryEmailTxt"] = $user["details"]->entryEmailTxt;
		
		$return["entryEmailUserIDs"] = explode("|", $user["details"]->entryEmailUsers);
		$return["entryEmailUsers"] = [];
		if(!empty($user["details"]->entryEmailUsers))
		{
			foreach($return["entryEmailUserIDs"] AS $emailUserID) { $return["entryEmailUsers"][$emailUserID] = $this->getUser($emailUserID); }
		}
		
		#Del datas
		if(!empty($user["details"]->delReason))
		{
			$return["delReasonRow"] = $this->getDelReason($user["details"]->delReason);
			$return["delReason"] = $return["delReasonRow"]["name"];
		}
		else { $return["delReason"] = ""; }
		
		if($user["details"]->delEmail)
		{
			$return["delEmailSubject"] = $user["details"]->delEmailSubject;
			$return["delEmailTxt"] = $user["details"]->delEmailTxt;
			
			$return["delEmailUserIDs"] = explode("|", $user["details"]->delEmailUsers);
			$return["delEmailUsers"] = [];
			if(!empty($user["details"]->delEmailUsers))
			{
				foreach($return["delEmailUserIDs"] AS $emailUserID) { $return["delEmailUsers"][$emailUserID] = $this->getUser($emailUserID); }
			}
		}
		else { $return["delEmailSubject"] = $return["delEmailTxt"] = ""; }
		
		$return["delComment"] = nl2br($user["details"]->delComment);
		$return["workEnd"] = (!empty($user["details"]->workEnd)) ? date("Y. m. d.", strtotime($user["details"]->workEnd)) : NULL;
		$return["workLastDay"] = (!empty($user["details"]->workLastDay)) ? date("Y. m. d.", strtotime($user["details"]->workLastDay)) : NULL;
		
		#Details 1
		$return["details1Title"] = "Alapadatok";
		$return["details1"] = [
			["name" => "ID", "value" => $user["id"]],
			["name" => "Név", "value" => $user["name"]],
			["name" => "E-mail cím", "value" => $user["email"]],
			["name" => "Magán e-mail cím", "value" => $user["details"]->privateEmail],
			["name" => "Telefonszám", "value" => $user["admin"]->phone],
			["name" => "Rang", "value" => $user["rank"]->name],
			["name" => "Létrehozás időpontja", "value" => date("Y. m. d. H:i", strtotime($user["data"]->regDate))],
			["name" => "Token", "value" => $user["token"]],
			["name" => "Értékesítő", "value" => ($user["admin"]->saleMan) ? "IGEN" : "<em>Nem</em>"],
		];
		
		#Details2
		$details2 = [];
		if($user["admin"]->visible)
		{
			$details2[] = ["name" => "Megjelenhet a weboldalon?", "value" => "IGEN"];
			
			$out = [];
			$premises = new \App\Http\Controllers\PremiseController;
			foreach($user["premiseAddresses"] AS $premiseAddress)
			{
				$address = $premises->getAddress($premiseAddress, false);
				$out[] = $address["name"];
			}
			$details2[] = ["name" => "Telephelyek, ahol megjelenhet", "value" => implode(", ", $out)];
			
			$out = [];
			
			foreach($user["brands"] AS $brand) { $out[] = $GLOBALS["CARS"]->model->getBrand($brand, "nameOut"); }
			$details2[] = ["name" => "Márkák, amelyeknél megjelenhet", "value" => implode(", ", $out)];
		}
		else { $details2[] = ["name" => "Megjelenhet a weboldalon?", "value" => "<em>Nem</em>"]; }

		if(isset($user["position"]->id)) { $details2[] = ["name" => "Beosztás", "value" => $user["position"]->nameOut]; }
		if(!empty($user["langs"])) 
		{ 
			$out = [];
			$langs = $this->getLangs();
			foreach($user["langs"] AS $lang) { $out[] = $langs[$lang]; }
			$details2[] = ["name" => "Beszélt nyelvek", "value" => implode(", ", $out)];
		}
		$return["details2Title"] = "Weboldal adatok";
		$return["details2"] = $details2;
		
		#Details3
		$details3 = [];
		if(!empty($user["details"]->commendUser)) 
		{ 
			$commendUser = $this->getUser($user["details"]->commendUser);
			$details3[] = ["name" => "Beajánló munkatárs", "value" => $commendUser["name"]." (".$commendUser["email"].")"]; 
		}
		if(!empty($user["details"]->workStart)) 
		{ 
			$return["workStart"] = date("Y. m. d.", strtotime($user["details"]->workStart));
			$details3[] = ["name" => "Munkaviszony kezdete", "value" => $return["workStart"]];
		}
		if(!empty($user["details"]->workProbation)) { $details3[] = ["name" => "Próbaidő vége", "value" => date("Y. m. d.", strtotime($user["details"]->workProbation))]; }
		if(!empty($user["details"]->workLay)) 
		{ 
			$return["workLay"] = $user["workLay"]["name"];
			$details3[] = ["name" => "Munkakör", "value" => $return["workLay"]]; 
		}
		if(!empty($user["details"]->category)) { $details3[] = ["name" => "Belső kategória", "value" => $user["category"]["name"]]; }
		if(!empty($user["details"]->premiseAddress)) 
		{ 
			$premises = new \App\Http\Controllers\PremiseController;
			$address = $premises->getAddress($user["details"]->premiseAddress, false);
			$return["premiseAddress"] = $address["name"];
			$details3[] = ["name" => "Telephely", "value" => $address["name"]]; 
		}
		if(!empty($user["details"]->description)) { $details3[] = ["name" => "Belső megjegyzések", "value" => nl2br($user["details"]->description)]; }
		$return["details3Title"] = "Belső adatok";
		$return["details3"] = $details3;
		
		return $return;
	}
	
	#Get rank
	public function getRanksSelect($maxRank)
	{
		$return = [];
		$rows = $this->model->select("SELECT * FROM ".$this->model->tables("ranks")." WHERE del = '0' AND orderNumber <= :orderNumber ORDER BY orderNumber", ["orderNumber" => $maxRank]);
		foreach($rows AS $row) { $return[$row->id] = $row->name; }
		return $return;
	}
	
	#Update user's activity field
	public function activity($id = NULL)
	{
		if(empty($id) AND defined("USERID")) { $id = USERID; }
		$this->model->myUpdate($this->model->tables("users"), ["lastActivity" => $this->model->now()], $id);
	}
	
	#Login process
	public function login($email, $password, $redirect = false)
	{
		#Get user
		$row = $this->model->getUserByEmail($email);
		
		#Email OK
		if(isset($row->id) AND !empty($row->id))
		{
			#Error: Deleted user
			if($row->del) { $errorKey = "del"; }
			#Error: Rank
			elseif(!empty($this->loginAcceptedRanks) AND !in_array($row->rank, $this->loginAcceptedRanks)) { $errorKey = "rank"; }
			#Error: Password
			elseif($row->password != $this->password($password)) { $errorKey = "password"; }
			#ACCEPTED
			else
			{
				$errorKey = "";
				$_SESSION[USER_LOGGED_IN] = true;
				$_SESSION[USER_ID_KEY] = $row->id;
				
				$params = [
					"lastLogin" => $this->model->now(),
					"lastIP" => $_SERVER["REMOTE_ADDR"],
				];
				$this->model->myUpdate($this->model->tables("users"), $params, $row->id);
			}		
		}
		#Error: Email
		else { $errorKey = "email"; }
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			if($_SESSION[USER_LOGGED_IN]) { $id = $row->id; }
			else { $id = NULL; }
			$GLOBALS["log"]->log("users-login", ["int1" => $id, "vchar1" => $errorKey, "vchar2" => $email]);
		}

		#Redirect, return
		if($redirect AND isset($GLOBALS["URL"]))
		{
			if($_SESSION[USER_LOGGED_IN]) { $GLOBALS["URL"]->redirect($this->redirectLoginSuccess); }
			else { $GLOBALS["URL"]->redirect($this->redirectLoginError); }
		}
		else { return $errorKey; }
	}
	
	#Registration process
	public function registration($datas, $required = NULL, $rank = NULL, $loginOnSuccess = true, $redirect = false)
	{
		#Basic datas
		if($required === NULL) { $required = $this->registrationRequired; }
		if($rank === NULL) { $rank = $this->registrationRank; }
		
		$return = [
			"success" => true,
			"errors" => [],
			"datas" => $datas,
			"required" => $required,
			"missing" => [],
			"passwordMatch" => true,
			"doubleEmail" => false,
			"loginOnSuccess" => $loginOnSuccess,
			"redirect" => $redirect,
			"lastID" => NULL,
			"loginData" => NULL,
		];

		#Is something missing?
		if(count($required) > 0)
		{
			foreach($required AS $itemKey)
			{
				if(!isset($datas[$itemKey]) OR empty($datas[$itemKey]))
				{
					$return["success"] = false;
					$return["missing"][] = $itemKey;
				}
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		#Are the 2 passwords match?
		$password1 = $datas["password1"];
		$password2 = $datas["password2"];
		$datas["password1"] = $datas["password2"] = $return["datas"]["password1"] = $return["datas"]["password2"] = NULL;
		unset($datas["password1"]);
		unset($datas["password2"]);

		if($password1 == $password2) { $password = $password1; }
		else
		{
			$return["success"] = false;
			$return["passwordMatch"] = false;
			$return["errors"][] = "passwordMismatch";
		}
		
		#If email exists
		if($this->doubleEmail($datas["email"]))
		{
			$return["success"] = false;
			$return["doubleEmail"] = true;
			$return["errors"][] = "doubleEmail";
		}
		
		#If everything is OK
		if($return["success"])
		{
			#Insert
			$params = $datas;
			if(!isset($params["token"])) { $params["token"] = $this->setToken(); }
			if(!isset($params["regDate"])) { $params["regDate"] = $this->model->now(); }
			if(!isset($params["rank"])) { $params["rank"] = $rank; }
			$params["password"] = $this->password($password);
			
			$return["lastID"] = $lastID = $this->model->myInsert($this->model->tables("users"), $params);
			
			#Log
			if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-registration", ["int1" => $lastID, "text1" => $GLOBALS["log"]->json($params), "text2" => $GLOBALS["log"]->json($return)]); }
			
			if($loginOnSuccess) { $return["loginData"] = $this->login($params["email"], $password, $redirect); }
		}
		#Else - If something is wrong
		else
		{
			#Log
			if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-registration-failed", ["text1" => $GLOBALS["log"]->json($return)]); }
		}
		
		#Return
		return $return;
	}
	
	#Profile process
	public function profile($datas, $required = NULL, $userID = NULL)
	{
		#Basic datas
		if($required === NULL) { $required = $this->profileRequired; }
		if($userID === NULL) { $userID = USERID; }
		
		$return = [
			"success" => true,
			"errors" => [],
			"datas" => $datas,
			"required" => $required,
			"missing" => [],
			"passwordMatch" => true,
			"doubleEmail" => false,
		];

		#Is something missing?
		if(count($required) > 0)
		{
			foreach($required AS $itemKey)
			{
				if(!isset($datas[$itemKey]) OR empty($datas[$itemKey]))
				{
					$return["success"] = false;
					$return["missing"][] = $itemKey;
				}
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		#Are the 2 passwords match?
		$password1 = $datas["password1"];
		$password2 = $datas["password2"];
		$datas["password1"] = $datas["password2"] = $return["datas"]["password1"] = $return["datas"]["password2"] = NULL;
		unset($datas["password1"]);
		unset($datas["password2"]);

		if($password1 == $password2) { $password = $password1; }
		else
		{
			$return["success"] = false;
			$return["passwordMatch"] = false;
			$return["errors"][] = "passwordMismatch";
		}
		
		#If email exists
		if($this->doubleEmail($datas["email"], $userID))
		{
			$return["success"] = false;
			$return["doubleEmail"] = true;
			$return["errors"][] = "doubleEmail";
		}
		
		#If everything is OK
		if($return["success"])
		{
			#AdminRow
			if(isset($datas["adminRow"]) AND !empty($datas["adminRow"])) 
			{
				$adminDatas = $datas["adminRow"];
				unset($datas["adminRow"]);
				$adminRow = $this->model->getUserAdminDataByUserID($userID);
			}
			
			#Update
			$params = $datas;
			if(!empty($password)) { $params["password"] = $this->password($password); }			
			$this->model->myUpdate($this->model->tables("users"), $params, $userID);
			
			#If admin edit
			if($adminDatas AND !empty($adminDatas))
			{
				#Params
				$params2 = [];
				if(isset($adminDatas["position"])) { $params2["position"] = $adminDatas["position"]; }
				if(isset($adminDatas["visible"]) AND $adminDatas["visible"]) { $params2["visible"] = 1; } else { $params2["visible"] = 0; }
				if(isset($adminDatas["phone"])) { $params2["phone"] = $adminDatas["phone"]; }
				if(isset($adminDatas["saleMan"]) AND $adminDatas["saleMan"]) { $params2["saleMan"] = 1; } else { $params2["saleMan"] = 0; }
				
				$params2["premiseAddresses"] = $params2["carBrands"] = $params2["spokenLangs"] = "";
				if(isset($adminDatas["premiseAddresses"]) AND !empty($adminDatas["premiseAddresses"])) { $params2["premiseAddresses"] = implode("|", $adminDatas["premiseAddresses"]); }
				if(isset($adminDatas["carBrands"]) AND !empty($adminDatas["carBrands"])) { $params2["carBrands"] = implode("|", $adminDatas["carBrands"]); }
				if(isset($adminDatas["spokenLangs"]) AND !empty($adminDatas["spokenLangs"])) { $params2["spokenLangs"] = implode("|", $adminDatas["spokenLangs"]); }
				
				#Insert
				if($adminRow === false)
				{
					$params2["userID"] = $userID;
					if($params2["saleMan"]) { $params2["saleManFrom"] = date("Y-m-d"); }
					$adminID = $this->model->myInsert($this->model->tables("admin"), $params2);
				}
				#Update
				else
				{
					if($params2["saleMan"] != $adminRow->saleMan)
					{
						#SaleMan off -> ON
						if($params2["saleMan"]) { $params2["saleManFrom"] = date("Y-m-d"); }
						#SaleMan on -> OFF
						else { $params2["saleManTo"] = date("Y-m-d"); }
					}
					$this->model->myUpdate($this->model->tables("admin"), $params2, $adminRow->id);
				}
			}
			
			#Log
			if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-profile", ["int1" => $userID, "text1" => $GLOBALS["log"]->json($params), "text2" => $GLOBALS["log"]->json($return)]); }
		}
		#Else - If something is wrong
		else
		{
			#Log
			if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-profile-failed", ["int1" => $userID, "text1" => $GLOBALS["log"]->json($return)]); }
		}
		
		#Return
		return $return;
	}
	
	#Logout process
	public function logout()
	{
		if(isset($GLOBALS["log"])) { $GLOBALS["log"]->log("users-logout"); }
		session_destroy();
	}
	
	#Set token
	public function setToken($id = 0)
	{
		return self::setUniqueToken(10, $allowToUse = ["numbers", "capitalLetters"], $this->model->tables("users"), "token", [], $id);
	}
	
	#Check email
	public function doubleEmail($email, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("users"), "email", $email, ["del" => 0], $id);
		if(count($rows) > 0) { return true; }
		else { return false; }
	}
	
	#Get ranks
	public function getRanks($del = 0, $orderBy = "orderNumber")
	{
		return $this->model->selectWholeTable($this->model->tables("ranks"), $del, $orderBy);
	}
	
	#Get positions
	public function getPositions()
	{
		$return = [
			"positions" => [],
			"groups" => [],
		];
		$groups = $this->model->getPositionGroups();
		foreach($groups AS $group)
		{
			$return["groups"][$group->id] = [
				"data" => $group,
				"positions" => [],
			];
			$positions = $this->model->getPositionsByGroup($group->id);
			foreach($positions AS $position)
			{
				$return["positions"][$position->id] = $position;
				$return["groups"][$group->id]["positions"][$position->id] = $position;
			}
		}
		
		return $return;
	}
	
	#Forgot password
	public function forgotPassword($email)
	{
		#Get user
		$row = $this->model->getUserByEmail($email);
		
		#Email OK
		if(isset($row->id) AND !empty($row->id))
		{
			$id = $row->id;
			#Error: Deleted user
			if($row->del) { $errorKey = "del"; }
			#Error: Rank
			elseif(!empty($this->loginAcceptedRanks) AND !in_array($row->rank, $this->loginAcceptedRanks)) { $errorKey = "rank"; }
			#ACCEPTED
			else
			{
				$errorKey = "";
				
				$date = date("Y-m-d H:i:s");
				$token = $row->id."-".date("YmdHis", strtotime($date))."-".$row->token."-".$row->email."-".self::random(10);
				$hash = $this->forgotPasswordHash($token);
				
				$params = [
					"userID" => $row->id,
					"date" => $date,
					"ip" => $_SERVER["REMOTE_ADDR"],
					"token" => $token,
					"hash" => $hash,
				];
				
				$this->model->statement("UPDATE ".$this->model->tables("forgotPasswords")." SET del = '1', deleted_at = :deleted_at WHERE del = '0' AND userID = :userID", ["deleted_at" => $date, "userID" => $row->id]);
				$lastID = $this->model->myInsert($this->model->tables("forgotPasswords"), $params);
			}	
			
		}
		#Error: Email
		else 
		{ 
			$id = $token = $hash = $lastID = $params = NULL;
			$errorKey = "email"; 
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("users-forgot-password", ["int1" => $id, "int2" => $lastID, "vchar1" => $errorKey, "vchar2" => $email, "vchar3" => $hash]);
		}
		
		#Return
		$return = [
			"errorKey" => $errorKey,
			"user" => $this->getUser($id),
			"lastID" => $lastID,
			"params" => $params,
		];
		return $return;
	}
	
	public function getForgotPassword($hash)
	{
		$row = $this->model->getForgotPasswordByHash($hash);
		if(isset($row->date) AND $row->date >= date("Y-m-d H:i:s", strtotime("-3 days"))) 
		{ 
			if($row->hadUsed) { $id = NULL; }
			else { $id = $row->id; }
		}
		else { $id = NULL; }
		
		return $id;
	}
	
	public function setForgotPassword($hash, $password1, $password2)
	{
		$return = [
			"success" => 1,
			"errorKey" => "",
		];
		$id = $this->getForgotPassword($hash);
		
		if($id === NULL) 
		{ 
			$return["success"] = 0;
			$return["errorKey"] = "hash";
		}
		elseif($password1 == $password2) 
		{ 
			$row = $this->model->getForgotPasswordByHash($hash);
			$password = $this->password($password1); 
			$this->model->myUpdate($this->model->tables("users"), ["password" => $password], $row->userID);
			$this->model->myUpdate($this->model->tables("forgotPasswords"), ["hadUsed" => 1, "hadUsedDate" => date("Y-m-d H:i:s")], $row->id);
		}
		else
		{
			$return["success"] = 0;
			$return["errorKey"] = "passwordMismatch";
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("users-forgot-password-set", ["int1" => $return["success"], "int2" => $rid, "vchar1" => $hash, "vchar2" => $return["errorKey"]]);
		}
		
		return $return;
	}
	
	public function forgotPasswordHash($token)
	{
		return sha1($token.$this->forgotPasswordAfter);
	}
	
	#Create new user
	public function newUser($datas)
	{
		$required = ["firstName", "lastName", "rank"]; // "email"		
		$requiredDetails = ["workStart", "workLay", "category", "premiseAddress"];		
		$return = [
			"success" => true,
			"errors" => [],
			"datas" => $datas,
			"required" => $required,
			"missing" => [],
			"doubleEmail" => false,
			"userID" => NULL,
			"adminID" => NULL,
			"detailsID" => NULL,
		];

		#Is something missing?
		foreach($required AS $itemKey)
		{
			if(!isset($datas[$itemKey]) OR empty($datas[$itemKey]))
			{
				$return["success"] = false;
				$return["missing"][] = $itemKey;
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		foreach($requiredDetails AS $itemKey)
		{
			if(!isset($datas["details"][$itemKey]) OR empty($datas["details"][$itemKey]))
			{
				$return["success"] = false;
				$return["missing"][] = $itemKey;
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		#If email exists
		if(isset($datas["email"]) AND !empty($datas["email"]))
		{
			if($this->doubleEmail($datas["email"]))
			{
				$return["success"] = false;
				$return["doubleEmail"] = true;
				$return["errors"][] = "doubleEmail";
			}
		}
		
		#If everything is OK
		if($return["success"])
		{
			#Insert - User
			$regDate = date("Y-m-d H:i:s");
			$token = $this->setToken();
			$params = [
				"firstName" => $datas["firstName"],
				"lastName" => $datas["lastName"],
				"email" => $datas["email"],
				"password" => $this->password($regDate.$token),
				"token" => $token,
				"regDate" => $regDate,
				"rank" => $datas["rank"],
			];
			$return["userID"] = $userID = $this->model->myInsert($this->model->tables("users"), $params);
			
			#Admin and details data
			if(!empty($userID) AND $userID > 0)
			{
				#Admin
				$paramsAdmin = $paramsDetails = ["userID" => $userID];
				$datasAdmin = $datas["admin"];
				if(isset($datasAdmin["position"])) { $paramsAdmin["position"] = $datasAdmin["position"]; }
				if(isset($datasAdmin["premiseAddresses"])) { $paramsAdmin["premiseAddresses"] = implode("|", $datasAdmin["premiseAddresses"]); }
				if(isset($datasAdmin["carBrands"])) { $paramsAdmin["carBrands"] = implode("|", $datasAdmin["carBrands"]); }
				$paramsAdmin["visible"] = (isset($datasAdmin["visible"]) AND $datasAdmin["visible"]) ? 1 : 0;
				if(isset($datasAdmin["phone"])) { $paramsAdmin["phone"] = $datasAdmin["phone"]; }
				if(isset($datasAdmin["phoneWired"])) { $paramsAdmin["phoneWired"] = $datasAdmin["phoneWired"]; }
				if(isset($datasAdmin["phoneWiredExt"])) { $paramsAdmin["phoneWiredExt"] = $datasAdmin["phoneWiredExt"]; }
				if(isset($datasAdmin["spokenLangs"])) { $paramsAdmin["spokenLangs"] = implode("|", $datasAdmin["spokenLangs"]); }
				if(isset($datasAdmin["saleMan"]) AND $datasAdmin["saleMan"])
				{
					$paramsAdmin["saleMan"] = 1;
					$paramsAdmin["saleManFrom"] = date("Y-m-d");
				}
				else { $paramsAdmin["saleMan"] = 0; }
				$return["adminID"] = $adminID = $this->model->myInsert($this->model->tables("admin"), $paramsAdmin);
				
				#Details
				$datasDetails = $datas["details"];
				if(isset($datasDetails["privateEmail"]) AND !empty($datasDetails["privateEmail"])) { $paramsDetails["privateEmail"] = $datasDetails["privateEmail"]; }
				if(isset($datasDetails["workStart"]) AND !empty($datasDetails["workStart"])) { $paramsDetails["workStart"] = $datasDetails["workStart"]; }
				if(isset($datasDetails["workProbation"]) AND !empty($datasDetails["workProbation"])) { $paramsDetails["workProbation"] = $datasDetails["workProbation"]; }
				if(isset($datasDetails["workLay"]) AND !empty($datasDetails["workLay"])) { $paramsDetails["workLay"] = $datasDetails["workLay"]; }
				if(isset($datasDetails["workDressSize"])) { $paramsDetails["workDressSize"] = $datasDetails["workDressSize"]; }
				if(isset($datasDetails["category"]) AND !empty($datasDetails["workStart"])) { $paramsDetails["category"] = $datasDetails["category"]; }
				if(isset($datasDetails["premiseAddress"]) AND !empty($datasDetails["premiseAddress"])) { $paramsDetails["premiseAddress"] = $datasDetails["premiseAddress"]; }
				if(isset($datasDetails["description"])) { $paramsDetails["description"] = $datasDetails["description"]; }
				if(isset($datasDetails["detailsInfo"])) { $paramsDetails["detailsInfo"] = $datasDetails["detailsInfo"]; }
				if(isset($datasDetails["commendUser"]) AND !empty($datasDetails["commendUser"])) { $paramsDetails["commendUser"] = $datasDetails["commendUser"]; }
				
				if(empty($paramsDetails["workLay"])) { $paramsDetails["workLay"] = NULL; }
				if(empty($paramsDetails["category"])) { $paramsDetails["category"] = NULL; }
				$return["detailsID"] = $detailsID = $this->model->myInsert($this->model->tables("details"), $paramsDetails);
				
				#Tasks
				if(isset($datas["tasks"]) AND !empty($datas["tasks"]))
				{
					foreach($datas["tasks"] AS $taskID => $taskFields)
					{
						if($taskFields["checked"])
						{
							$paramsTask = [
								"user" => $userID,
								"task" => $taskID,
								"commentHR" => $taskFields["comment"],
							];
							$this->model->myInsert($this->model->tables("tasksProgress"), $paramsTask);
						}
					}
				}
			}
			
			#Pic
			$file = new \App\Http\Controllers\FileController;
			$fileReturn = $file->upload("pic", "users-profile", $userID);
			if($fileReturn[0]["type"] == "success") { $this->editUser($userID, ["pic" => $fileReturn[0]["fileID"]]); }
			
			$logSuccess = 1;
		}
		#Else - If something is wrong
		else
		{
			$logSuccess = 0;
			$params = $paramsAdmin = $paramsDetails = NULL;
		}
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $logSuccess, 
				"int2" => $return["userID"], 
				"int3" => $return["adminID"], 
				"int4" => $return["detailsID"], 
				"text1" => $GLOBALS["log"]->json($params), 
				"text2" => $GLOBALS["log"]->json($paramsAdmin), 
				"text3" => $GLOBALS["log"]->json($paramsDetails), 
				"text4" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("users-new", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#Edit user
	public function changeUser($datas, $id)
	{
		$required = ["firstName", "lastName", "rank"];	
		$requiredDetails = ["workLay", "category", "premiseAddress"];		
		$currentData = $this->getUser($id);
		$userID = $currentData["id"];
		$adminID = $currentData["admin"]->id;
		$detailsID = $currentData["details"]->id;
		$return = [
			"success" => true,
			"errors" => [],
			"datas" => $datas,
			"required" => $required,
			"missing" => [],
			"userID" => $userID,
			"adminID" => $adminID,
			"detailsID" => $detailsID,
		];

		#Is something missing?
		foreach($required AS $itemKey)
		{
			if(!isset($datas[$itemKey]) OR empty($datas[$itemKey]))
			{
				$return["success"] = false;
				$return["missing"][] = $itemKey;
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		foreach($requiredDetails AS $itemKey)
		{
			if(!isset($datas["details"][$itemKey]) OR empty($datas["details"][$itemKey]))
			{
				$return["success"] = false;
				$return["missing"][] = $itemKey;
			}
		}
		if(count($return["missing"]) > 0) { $return["errors"][] = "missingFields"; }
		
		#If email exists
		if(isset($datas["email"]) AND !empty($datas["email"]))
		{
			if($this->doubleEmail($datas["email"], $userID))
			{
				$return["doubleEmail"] = true;
				$return["success"] = false;
				$return["errors"][] = "doubleEmail";
			}
		}
		
		#If everything is OK
		if($return["success"])
		{
			#Update - User
			$params = [
				"firstName" => $datas["firstName"],
				"lastName" => $datas["lastName"],
				"rank" => $datas["rank"],
				"email" => $datas["email"],
			];
			$this->model->myUpdate($this->model->tables("users"), $params, $userID);
			
			#Admin
			$paramsAdmin = $paramsDetails = [];
			$datasAdmin = $datas["admin"];
			if(isset($datasAdmin["position"])) { $paramsAdmin["position"] = $datasAdmin["position"]; }
			if(isset($datasAdmin["premiseAddresses"])) { $paramsAdmin["premiseAddresses"] = implode("|", $datasAdmin["premiseAddresses"]); }
			else { $paramsAdmin["premiseAddresses"] = NULL; }
			
			if(isset($datasAdmin["carBrands"])) { $paramsAdmin["carBrands"] = implode("|", $datasAdmin["carBrands"]); }
			else { $paramsAdmin["carBrands"] = NULL; }
			
			$paramsAdmin["visible"] = (isset($datasAdmin["visible"]) AND $datasAdmin["visible"]) ? 1 : 0;
			if(isset($datasAdmin["phone"])) { $paramsAdmin["phone"] = $datasAdmin["phone"]; }
			if(isset($datasAdmin["phoneWired"])) { $paramsAdmin["phoneWired"] = $datasAdmin["phoneWired"]; }
			if(isset($datasAdmin["phoneWiredExt"])) { $paramsAdmin["phoneWiredExt"] = $datasAdmin["phoneWiredExt"]; }
			
			if(isset($datasAdmin["spokenLangs"])) { $paramsAdmin["spokenLangs"] = implode("|", $datasAdmin["spokenLangs"]); }
			else { $paramsAdmin["spokenLangs"] = NULL; }
			
			#SaleMan from now
			if(!$currentData["admin"]->saleMan AND isset($datasAdmin["saleMan"]) AND $datasAdmin["saleMan"])
			{
				$paramsAdmin["saleMan"] = 1;
				$paramsAdmin["saleManFrom"] = date("Y-m-d");
			}
			#SaleMan till now
			elseif($currentData["admin"]->saleMan AND (!isset($datasAdmin["saleMan"]) OR !$datasAdmin["saleMan"]))
			{
				$paramsAdmin["saleMan"] = 0;
				$paramsAdmin["saleManTo"] = date("Y-m-d");
			}
			if($this->model->getUserAdminDataByUserID($id) !== false) { $this->model->myUpdate($this->model->tables("admin"), $paramsAdmin, $adminID); }
			else 
			{ 
				$paramsAdmin["userID"] = $id;
				$adminID = $return["adminID"] = $this->model->myInsert($this->model->tables("admin"), $paramsAdmin); 
			}
			
			#Details
			$datasDetails = $datas["details"];
			if(isset($datasDetails["privateEmail"]) AND !empty($datasDetails["privateEmail"])) { $paramsDetails["privateEmail"] = $datasDetails["privateEmail"]; }
			if(isset($datasDetails["workLay"])) { $paramsDetails["workLay"] = $datasDetails["workLay"]; }
			if(isset($datasDetails["workDressSize"])) { $paramsDetails["workDressSize"] = $datasDetails["workDressSize"]; }
			if(isset($datasDetails["category"])) { $paramsDetails["category"] = $datasDetails["category"]; }
			if(isset($datasDetails["premiseAddress"])) { $paramsDetails["premiseAddress"] = $datasDetails["premiseAddress"]; }
			if(isset($datasDetails["description"])) { $paramsDetails["description"] = $datasDetails["description"]; }
			
			if(empty($paramsDetails["workLay"])) { $paramsDetails["workLay"] = NULL; }
			if(empty($paramsDetails["category"])) { $paramsDetails["category"] = NULL; }
			
			if($this->model->getUserDetailsByUserID($id) !== false) { $this->model->myUpdate($this->model->tables("details"), $paramsDetails, $detailsID); }
			else 
			{ 
				$paramsDetails["userID"] = $id;
				$detailsID = $return["detailsID"] = $this->model->myInsert($this->model->tables("details"), $paramsDetails);
			}
			
			#Pic
			$file = new \App\Http\Controllers\FileController;
			$fileReturn = $file->upload("pic", "users-profile", $userID);
			if($fileReturn[0]["type"] == "success") { $this->editUser($userID, ["pic" => $fileReturn[0]["fileID"]]); }
			
			$logSuccess = 1;
		}
		#Else - If something is wrong
		else
		{
			$logSuccess = 0;
			$params = $paramsAdmin = $paramsDetails = NULL;
		}
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $logSuccess, 
				"int2" => $return["userID"], 
				"int3" => $return["adminID"], 
				"int4" => $return["detailsID"], 
				"text1" => $GLOBALS["log"]->json($params), 
				"text2" => $GLOBALS["log"]->json($paramsAdmin), 
				"text3" => $GLOBALS["log"]->json($paramsDetails), 
				"text4" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("users-edit", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#Feedback user
	public function feedbackUser($datas, $user, $onExit = false)
	{		
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"division" => $datas["division"],
			"params" => [],
		];

		
		#Tasks
		foreach($datas["tasks"] AS $taskID => $task)
		{
			#Check
			$currentTask = $this->getTaskProgress($taskID);
			if($currentTask["finishedDivision"] OR $currentTask["finishedHR"]) { continue; }
			
			#Params
			$return["params"][$taskID] = $params = [
				"divisionDatas" => $task["datas"],
				"divisionComment" => $task["comment"],
				"finishedDivision" => (isset($task["finished"]) AND $task["finished"]) ? 1 : 0,
			];
			
			#Params based on type
			$logKey = "users-feedback";
			if($onExit)
			{
				$logKey .= "-del";
				if(isset($task["finished"]) AND $task["finished"]) { $return["params"][$taskID]["finishedHR"] = $params["finishedHR"] = 1; }
			}
			
			#Update
			$this->model->myUpdate($this->model->tables("tasksProgress"), $params, $taskID);
			
			#File
			if($currentTask["task"]["fileUpload"])
			{
				$file = new \App\Http\Controllers\FileController;
				$fileReturn = $file->upload("file-".$taskID, "intranet-users-task", $taskID);
				if($fileReturn[0]["type"] == "success") { $this->model->myUpdate($this->model->tables("tasksProgress"), ["file" => $fileReturn[0]["fileID"]], $taskID); }
			}
		}
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $return["userID"], 
				"int2" => $return["division"], 
				"text1" => $GLOBALS["log"]->json($return), 
			];
			$GLOBALS["log"]->log($logKey, $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#Entry user
	public function entryUser($datas, $user)
	{		
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"params" => [],
			"reopen" => 0,
			"reopenDivisions" => [],
		];

		
		#Tasks
		foreach($datas["tasks"] AS $taskID => $task)
		{
			#Check
			$currentTask = $this->getTaskProgress($taskID);
			if($currentTask["finishedDivision"] AND $currentTask["finishedHR"]) { continue; }
			
			if(!empty($task["work"]))
			{
				#Finishing task
				if($task["work"] == "close")
				{
					$return["params"][$taskID] = $params = [
						"finishedDivision" => 1,
						"finishedHR" => 1,
						"reOpenComment" => NULL,
					];
					$this->model->myUpdate($this->model->tables("tasksProgress"), $params, $taskID);
				}
				#Re-opening task
				elseif($task["work"] == "reopen")
				{
					if($currentTask["finishedDivision"])
					{
						$return["params"][$taskID] = $params = [
							"finishedDivision" => 0,
							"reOpenComment" => $task["reOpenComment"],
						];
						$this->model->myUpdate($this->model->tables("tasksProgress"), $params, $taskID);
						
						$return["reopen"]++;
						$division = $currentTask["task"]["division"];
						if(!in_array($division["id"], $return["reopenDivisions"])) { $return["reopenDivisions"][] = $division["id"]; }
					}
				}
			}
		}
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $return["userID"], 
				"text1" => $GLOBALS["log"]->json($return), 
			];
			$GLOBALS["log"]->log("users-entry", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#Exit user
	public function exitUser($datas, $user)
	{		
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"params" => NULL,
		];

		
		#Details
		$params = [];
		if(isset($datas["workEnd"]) AND !empty($datas["workEnd"])) { $params["workEnd"] = $datas["workEnd"]; }
		if(isset($datas["workLastDay"]) AND !empty($datas["workLastDay"])) { $params["workLastDay"] = $datas["workLastDay"]; }
		if(isset($datas["delReason"]) AND !empty($datas["delReason"])) { $params["delReason"] = $datas["delReason"]; }
		if(isset($datas["delComment"]) AND !empty($datas["delComment"])) { $params["delComment"] = $datas["delComment"]; }
		if(isset($datas["delEmail"]) AND !empty($datas["delEmail"]))
		{
			$params["delEmail"] = 1;
			if(!isset($datas["emailUsers"]) OR empty($datas["emailUsers"])) { $params["delEmailUsers"] = NULL; }
			else { $params["delEmailUsers"] = implode("|", $datas["emailUsers"]); }
			$params["delEmailSubject"] = $datas["delEmailSubject"];
			$params["delEmailTxt"] = $datas["delEmailTxt"];
		}
		$return["params"] = $params;
		$this->model->myUpdate($this->model->tables("details"), $params, $user["details"]->id);
		
		#Tasks
		if(isset($datas["tasks"]) AND !empty($datas["tasks"]))
		{
			foreach($datas["tasks"] AS $taskID => $taskFields)
			{
				if($taskFields["checked"])
				{
					$paramsTask = [
						"user" => $user["id"],
						"task" => $taskID,
						"commentHR" => $taskFields["comment"],
					];
					$this->model->myInsert($this->model->tables("tasksProgress"), $paramsTask);
				}
			}
		}
		
		#Delete
		$this->delUser($user["id"]);
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $return["userID"], 
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("users-exit", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#User - Info e-mail settings
	public function userInfoEmail($datas, $user)
	{		
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"params" => NULL,
		];
		
		#Details
		$params = [];
		$params["infoEmailSubject"] = $datas["subject"];
		$params["infoEmailTxt"] = $datas["body"];
		if(!isset($datas["emailUsers"]) OR empty($datas["emailUsers"])) { $params["infoEmailUsers"] = NULL; }
		else { $params["infoEmailUsers"] = implode("|", $datas["emailUsers"]); }
		$return["params"] = $params;
		$this->model->myUpdate($this->model->tables("details"), $params, $user["details"]->id);
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $return["userID"], 
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("users-info-email", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#User - Entry e-mail settings
	public function userEntryEmail($datas, $user)
	{		
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"params" => NULL,
		];
		
		#Details
		$params = [];
		$params["entryEmailSubject"] = $datas["subject"];
		$params["entryEmailTxt"] = $datas["body"];
		if(!isset($datas["emailUsers"]) OR empty($datas["emailUsers"])) { $params["entryEmailUsers"] = NULL; }
		else { $params["entryEmailUsers"] = implode("|", $datas["emailUsers"]); }
		$return["params"] = $params;
		$this->model->myUpdate($this->model->tables("details"), $params, $user["details"]->id);
		
		#Log
		if(isset($GLOBALS["log"])) 
		{ 
			$logParams = [
				"int1" => $return["userID"], 
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("users-entry-email", $logParams); 
		}
		
		#Return
		return $return;
	}
	
	#Get employee
	public function getEmployee($id, $allData = true)
	{
		$row = $this->model->getEmployee($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["emailType"] = $row->emailType;
			$return["emailTypeRow"] = $this->getEmployeeEmail($return["emailType"]);
			$return["emailSubject"] = $row->emailSubject;
			$return["emailText"] = $row->emailText;
			
			$return["emailSent"] = $row->emailSent;
			$return["emailSentDate"] = $row->emailSentDate;
			$return["emailSentDateOut"] = ($return["emailSentDate"]) ? date("Y. m. d. H:i", strtotime($return["emailSentDate"])) : NULL;
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($return["date"]));
			$return["admin"] = $row->admin;
			
			#Users
			$return["users"] = [];
			$return["user1"] = $row->user1;
			$return["user2"] = $row->user2;
			$return["user3"] = $row->user3;
			$return["user4"] = $row->user4;
			$return["user5"] = $row->user5;
			
			$return["user1Data"] = $return["user2Data"] = $return["user3Data"] = $return["user4Data"] = $return["user5Data"] = [];
			if(!empty($row->user1Data)) { $return["users"][] = $return["user1Data"] = $this->jsonDecode($row->user1Data); }
			if(!empty($row->user2Data)) { $return["users"][] = $return["user2Data"] = $this->jsonDecode($row->user2Data); }
			if(!empty($row->user3Data)) { $return["users"][] = $return["user3Data"] = $this->jsonDecode($row->user3Data); }
			if(!empty($row->user4Data)) { $return["users"][] = $return["user4Data"] = $this->jsonDecode($row->user4Data); }
			if(!empty($row->user5Data)) { $return["users"][] = $return["user5Data"] = $this->jsonDecode($row->user5Data); }
			
			if($allData)
			{
				#Emailusers
				$return["emailUserIDs"] = explode("|", $row->emailUsers);
				$return["emailUsers"] = [];
				if(!empty($row->emailUsers))
				{
					foreach($return["emailUserIDs"] AS $emailUserID) { $return["emailUsers"][$emailUserID] = $this->getUser($emailUserID); }
				}
				
				#Users
				$return["userRows"] = [];
				if(!empty($return["user1"])) { $return["user1Row"] = $return["userRows"][$return["user1"]] = $this->getUser($return["user1"]); }
				if(!empty($return["user2"])) { $return["user2Row"] = $return["userRows"][$return["user2"]] = $this->getUser($return["user2"]); }
				if(!empty($return["user3"])) { $return["user3Row"] = $return["userRows"][$return["user3"]] = $this->getUser($return["user3"]); }
				if(!empty($return["user4"])) { $return["user4Row"] = $return["userRows"][$return["user4"]] = $this->getUser($return["user4"]); }
				if(!empty($return["user5"])) { $return["user5Row"] = $return["userRows"][$return["user5"]] = $this->getUser($return["user5"]); }
			}
		}
		
		return $return;
	}
	
	public function getEmployees()
	{
		$return = [];
		$rows = $this->model->getEmployees("id");		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getEmployee($row->id, false); }
		}
		
		return $return;
	}
	
	public function getActualEmployees()
	{
		$return = [];
		$emails = $this->getEmployeeEmails();
		foreach($emails AS $emailID => $email)
		{
			$employeeID = $this->model->getActualEmployeeByType($email["id"]);
			if($employeeID !== false)
			{
				$employee = $this->getEmployee($employeeID);
				if(!empty($employee["users"]))
				{
					$return[$employeeID] = [
						"email" => $email,
						"employee" => $employee,
						"name" => $employee["emailSubject"],
					];
				}
			}
		}
		
		return $return;
	}
	
	#Get employee email
	public function getEmployeeEmail($id)
	{
		$row = $this->model->getEmployeeEmail($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["subject"] = $row->subject;
			$return["text"] = $row->text;
			$return["orderNumber"] = $row->orderNumber;
		}
		
		return $return;
	}
	
	public function getEmployeeEmails()
	{
		$return = [];
		$rows = $this->model->getEmployeeEmails("id");		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getEmployeeEmail($row->id); }
		}
		
		return $return;
	}
	
	public function employeeWork($work, $datas)
	{
		$return = [
			"work" => $work,
			"datas" => $datas,
		];
		$return["table"] = $table = $this->model->tables("employee");
		switch($work)
		{
			case "new":
			case "edit":
				#Basic
				$return["params"] = [];
				$users = [];
				
				#Users
				if(isset($datas["user"]) AND !empty($datas["user"]))
				{
					foreach($datas["user"] AS $i => $userHere)
					{
						if(isset($userHere["id"]) AND !empty($userHere["id"]))
						{
							$field = "user".$i;
							$field2 = $field."Data";
							$userHereRow = $this->getUser($userHere["id"]);
							$return["params"][$field] = $userHere["id"];
							$users[] = $return["params"][$field2] = [
								"id" => $userHere["id"],
								"name" => $userHereRow["name"],
								"email" => $userHereRow["email"],
								"positionID" => $userHereRow["position"]->id,
								"position" => $userHereRow["position"]->nameOut,
								"premiseID" => $userHereRow["details"]->premiseAddress,
								"premise" => $userHereRow["premise"]["data"]->nameOut,
								"text" => $userHere["text"],
							];
							$return["params"][$field2] = $this->json($return["params"][$field2]);
						}
					}
				}
				
				#Email
				if(isset($datas["emailType"]) AND !empty($datas["emailType"]))
				{
					#Email datas
					$return["params"]["emailType"] = $datas["emailType"];
					$email = $this->getEmployeeEmail($datas["emailType"]);
					$emailSubject = $email["subject"];
					$emailText = $email["text"];
					
					#Set variables
					setlocale(LC_ALL,"hu_HU.UTF8");
					mb_internal_encoding("UTF-8");
					$monthName = mb_convert_case(strftime("%B"), MB_CASE_LOWER);
					
					if(date("n") <= 6) 
					{
						$halfYear = "I";
						$halfYear2 = "első";
					}
					else
					{
						$halfYear = "II";
						$halfYear2 = "második";
					}
					
					$names = "";
					$emailTable = "";
					$texts = "";
					foreach($users AS $i => $user)
					{
						if(!empty($name)) { $names .= ", "; }
						$names .= $user["name"];
						$texts .= "<p>".$user["name"]." ".$user["text"].".</p>";
						$emailTable .= '
							<tr>
								<td style="width: 23%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;">'.$user["name"].':</td>
								<td style="width: 23%; padding: 5px 1%; border: 1px solid #000;">'.$user["position"].'</td>
								<td style="width: 13%; padding: 5px 1%; border: 1px solid #000;">'.$user["premise"].'</td>
								<td style="width: 33%; padding: 5px 1%; border: 1px solid #000;">'.$user["text"].'</td>
							</tr>
						';
					}
					
					$variables = [
						"year" => date("Y"),
						"monthName" => $monthName,
						"monthName2" => mb_convert_case($monthName, MB_CASE_TITLE),
						"halfYear" => $halfYear,
						"halfYear2" => $halfYear2,
						"table" => $emailTable,
						"names" => $names,
						"texts" => $texts,
					];
					
					#Change variables
					$variablesFrom = [];
					$variablesTo = [];
					foreach($variables AS $variableKey => $variableVal)
					{
						$variablesFrom[] = "{".$variableKey."}";
						$variablesTo[] = $variableVal;
					}
					$return["params"]["emailSubject"] = str_replace($variablesFrom, $variablesTo, $emailSubject);
					$return["params"]["emailText"] = str_replace($variablesFrom, $variablesTo, $emailText);
				}
				
				#Command
				if($work == "new")
				{
					$return["params"]["admin"] = USERID;
					$return["params"]["date"] = date("Y-m-d H:i:s");
					$return["id"] = $this->model->myInsert($table, $return["params"]);
				}
				else
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, $return["params"], $return["id"]);
				}
				break;
			case "email":
				$return["id"] = $datas["id"];
				$return["params"] = [
					"emailSubject" => $datas["emailSubject"],
					"emailText" => $datas["emailText"],
					"emailUsers" => (isset($datas["emailUsers"]) AND !empty($datas["emailUsers"])) ? implode("|", $datas["emailUsers"]) : NULL,
				];	
				$this->model->myUpdate($table, $return["params"], $return["id"]);
				break;
			case "del":
				$return["id"] = $datas["id"];
				$this->model->myDelete($table, $datas["id"]);
				break;
			case "send":
				$return["id"] = $datas["id"];
				$this->model->myUpdate($table, ["emailSent" => 1, "emailSentDate" => date("Y-m-d H:i:s")], $return["id"]);
				break;	
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		return $return;
	}
	
	#Get category
	public function getCategory($id)
	{
		$row = $this->model->getCategory($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["orderNumber"] = $row->orderNumber;
		}
		
		return $return;
	}
	
	public function getCategories($orderBy = "name", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getCategories("id", $deleted, $orderBy);		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getCategory($row->id); }
		}
		
		return $return;
	}
	
	#Get worklay
	public function getWorklay($id)
	{
		$row = $this->model->getWorklay($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["orderNumber"] = $row->orderNumber;
		}
		
		return $return;
	}
	
	public function getWorklays($orderBy = "name", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getWorklays("id", $deleted, $orderBy);		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorklay($row->id); }
		}
		
		return $return;
	}
	
	public function categoryAndWorklayWork($tableName, $work, $datas)
	{
		$return = [
			"work" => $work,
			"datas" => $datas,
		];
		$table = $this->model->tables($tableName);
		switch($work)
		{
			case "new":
			case "edit":
				$return["params"] = ["name" => $datas["name"]];
				
				if($work == "new")
				{
					$return["params"]["orderNumber"] = $this->model->reOrder($table);
					$return["id"] = $this->model->myInsert($table, $return["params"]);
				}
				else
				{
					$return["id"] = $datas["id"];
					$this->model->myUpdate($table, $return["params"], $return["id"]);
				}
				break;
			case "order":
				$return["order"] = $this->model->newOrder($datas["orderType"], $datas["id"], $table);
				break;
			case "del":
				$return["id"] = $datas["id"];
				$this->model->myDelete($table, $datas["id"]);
				break;
			default:
				$return["errors"]["others"] = "unknown-worktype";
				$return["type"] = "error";
				break;
		}
		return $return;
	}
	
	#Get users by category
	public function getUsersByCategories()
	{
		$return = [];
		$rows = $this->getCategories();
		$users = $this->getUsers();
		if(!empty($rows))
		{
			foreach($rows AS $row) 
			{
				$return[$row["id"]] = [
					"id" => $row["id"],
					"name" => $row["name"],
					"data" => $row["data"],
					"users" => [],
				];
			}
			$return["-"] = [
				"id" => NULL,
				"name" => "N/A",
				"data" => NULL,
				"users" => [],
			];
			
			if(!empty($users["all"]))
			{
				foreach($users["all"] AS $user) 
				{
					$key = (!empty($user["details"]->category)) ? $user["details"]->category : "-";
					$return[$key]["users"][$user["id"]] = $user;
				}
			}
			
		}
		return $return;
	}
	
	#Get commend users for cron
	public function getCommends()
	{
		$users = $this->model->tables("users");
		$admin = $this->model->tables("admin");
		$details = $this->model->tables("details");
		$workStart = date("Y-m-d", strtotime("-6 months"));
		
		$return = [];
		$rows = $this->model->select("SELECT u.id FROM ".$users." u INNER JOIN ".$details." d ON d.userID = u.id WHERE u.del = '0' AND d.del = '0' AND d.workStart = :workStart AND d.commendUser != '0' AND d.commendUser IS NOT NULL", ["workStart" => $workStart]);		
		if(!empty($rows)) 
		{ 
			foreach($rows AS $row) 
			{
				$userHere = $this->getUser($row->id);
				if(!empty($userHere["commendUser"]) AND !$userHere["commendUser"]["data"]->del) { $return[$row->id] = $userHere; }
			}
		}

		return $return;
	}
	
	#Get probation users for cron
	public function getProbationUsers()
	{
		$users = $this->model->tables("users");
		$admin = $this->model->tables("admin");
		$details = $this->model->tables("details");
		$probationDate = date("Y-m-d", strtotime("+5 days"));
		
		$return = [
			"date" => $probationDate,
			"users" => [],
		];
		$rows = $this->model->select("SELECT u.id FROM ".$users." u INNER JOIN ".$details." d ON d.userID = u.id WHERE u.del = '0' AND d.del = '0' AND d.workProbation = :workProbation", ["workProbation" => $probationDate]);		
		if(!empty($rows)) 
		{ 
			foreach($rows AS $row) { $return["users"][$row->id] = $this->getUser($row->id); }
		}

		return $return;
	}
	
	#Get del reason
	public function getDelReason($id)
	{
		$row = $this->model->getDelReason($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["emailText"] = $row->emailText;
			$return["orderNumber"] = $row->orderNumber;
		}
		
		return $return;
	}
	
	public function getDelReasons($orderBy = "orderNumber", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getDelReasons("id", $deleted, $orderBy);		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getDelReason($row->id); }
		}
		
		return $return;
	}
	
	#Get division
	public function getDivision($id)
	{
		$row = $this->model->getDivision($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["email"] = $row->email;
			$return["orderNumber"] = $row->orderNumber;
		}
		return $return;
	}
	
	public function getDivisions($orderBy = "orderNumber", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getDivisions("id", $deleted, $orderBy);		
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getDivision($row->id); }
		}
		
		return $return;
	}
	
	#Get task
	public function getTask($id, $division = NULL)
	{
		$row = $this->model->getTask($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["orderNumber"] = $row->orderNumber;
			
			#Division
			$return["division"] = ($division === NULL) ? $this->getDivision($row->division) : $division;
			
			#Entry and exit
			$return["entry"] = ($row->onEntry) ? true : false;
			$return["exit"] = ($row->onExit) ? true : false;
			
			#Comments
			$return["descriptionForHR"] = $row->descriptionForHR;
			$return["descriptionForDivision"] = $row->descriptionForDivision;
			
			#File
			$return["fileName"] = $row->fileName;
			$return["file"] = (!empty($return["fileName"]) AND defined("PATH_WEB")) ? PATH_WEB."anyagok/intranet/munkatarsak/".$return["fileName"] : false;
			$return["fileUpload"] = ($row->fileUpload) ? true : false;
		}
		return $return;
	}
	
	public function getTasksForEntryAndExit()
	{
		#Return array
		$return = [
			"all" => [],
			"entry" => [],
			"exit" => [],
			"divisions" => [],
		];
		
		#Divisions
		$divisions = $this->getDivisions();
		foreach($divisions AS $divisionID => $division) 
		{ 
			$return["divisions"][$division["id"]] = [
				"data" => $division,
				"tasks" => [],
				"tasksEntry" => [],
				"tasksExit" => [],
			];
		}
		
		$rows = $this->model->getTasks("id, division");
		if(!empty($rows))
		{
			foreach($rows AS $row) 
			{ 
				$task = $this->getTask($row->id, $divisions[$row->division]);
				
				$return["all"][$task["id"]] = $task;
				$return["divisions"][$task["division"]["id"]]["tasks"][$task["id"]] = $task;
				if($task["entry"]) 
				{ 
					$return["entry"][$task["id"]] = $task; 
					$return["divisions"][$task["division"]["id"]]["tasksEntry"][$task["id"]] = $task;
				}
				if($task["exit"])
				{ 
					$return["exit"][$task["id"]] = $task; 
					$return["divisions"][$task["division"]["id"]]["tasksExit"][$task["id"]] = $task;
				}
			}
		}
		
		return $return;
	}
	
	#Get task
	public function getTaskProgress($id, $division = NULL)
	{
		$row = $this->model->getTaskProgress($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["taskID"] = $row->task;
			$return["task"] = $this->getTask($row->task, $division);
			$return["commentHR"] = $row->commentHR;
			$return["divisionDatas"] = $row->divisionDatas;
			$return["divisionComment"] = $row->divisionComment;
			$return["reOpenComment"] = $row->reOpenComment;
			
			#Finished
			$return["finishedDivision"] = ($row->finishedDivision) ? true : false;
			$return["finishedHR"] = ($row->finishedHR) ? true : false;
			$return["finished"] = ($return["finishedDivision"] AND $return["finishedHR"]) ? true : false;
			
			#File
			$return["fileID"] = $row->file;
			$return["file"] = NULL;
			if(!empty($return["fileID"]))
			{
				$files = new \App\Http\Controllers\FileController;
				$return["file"] = $files->getFile($return["fileID"]);
			}
		}
		return $return;
	}
	
	public function getTaskProgresses($user)
	{
		#Return array
		$return = [
			"all" => [],
			"entry" => [],
			"exit" => [],
			"divisions" => [],
			"allFinished" => true,
			"allFinishedEntry" => true,
			"allFinishedExit" => true,
		];
		
		$rows = $this->model->getTaskProgresses($user, "id, task");
		if(!empty($rows))
		{
			foreach($rows AS $row) 
			{ 
				$taskProgress = $this->getTaskProgress($row->id);
				$task = $taskProgress["task"];
				
				$return["all"][$taskProgress["id"]] = $taskProgress;
				$return["divisions"][$task["division"]["id"]]["tasks"][$taskProgress["id"]] = $taskProgress;
				if($task["entry"]) 
				{ 
					$return["entry"][$taskProgress["id"]] = $taskProgress; 
					$return["divisions"][$task["division"]["id"]]["tasksEntry"][$taskProgress["id"]] = $taskProgress;
					if(!$taskProgress["finished"]) { $return["allFinished"] = $return["allFinishedEntry"] = false; }
				}
				if($task["exit"])
				{ 
					$return["exit"][$taskProgress["id"]] = $taskProgress; 
					$return["divisions"][$task["division"]["id"]]["tasksExit"][$taskProgress["id"]] = $taskProgress;
					if(!$taskProgress["finished"]) { $return["allFinished"] = $return["allFinishedExit"] = false; }
				}
			}
		}
		
		return $return;
	}
	
	#Get tool
	public function getTool($id)
	{
		$row = $this->model->getTool($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["comment"] = $row->comment;
			$return["quantity"] = $row->quantity;
			$return["code"] = ($row->type == 1) ? $row->code : "";
			
			#User and date
			$return["userID"] = $row->user;
			$return["user"] = (!empty($return["userID"])) ? $this->getUser($return["userID"]) : false;
			$return["userName"] = ($return["user"] !== false) ? $return["user"]["name"] : "-";
			
			$return["date"] = $row->date;
			$return["dateOut"] = (!empty($return["date"])) ? date("Y. m. d. H:i", strtotime($return["date"])) : "";
			
			#Log user and date
			$return["logDate"] = $row->logDate;
			$return["logDateOut"] = (!empty($return["logDate"])) ? date("Y. m. d. H:i", strtotime($return["logDate"])) : "";
			
			$return["logUserID"] = $row->logUser;
			$return["logUser"] = (!empty($return["logUserID"])) ? $this->getUser($return["logUserID"]) : false;
			$return["logUserName"] = ($return["logUser"] !== false) ? $return["logUser"]["name"] : "-";
			
			#Type
			$return["typeID"] = $row->type;
			$return["type"] = (!empty($return["typeID"])) ? $this->getToolType($return["typeID"]) : false;
			$return["typeName"] = ($return["type"] !== false) ? $return["type"]["name"] : "-";
			
			#Documents
			$file = new \App\Http\Controllers\FileController;
			$return["documents"] = $file->getFileList("users-tools-documents", $return["id"]);
			
			#Details
			$return["details"] = [
				["name" => "Munkatárs", "value" => $return["userName"]],
				["name" => "Rögzítve", "value" => $return["logDateOut"]],
				["name" => "Rögzítő", "value" => $return["logUserName"]],
				["name" => "Típus", "value" => $return["typeName"]],
				["name" => "Megnevezés", "value" => $return["name"]],
				["name" => "Megjegyzés", "value" => $return["comment"]],
				["name" => "Mennyiség", "value" => $return["quantity"]],
				["name" => "Átadási Bizonylat sorszáma", "value" => $return["code"]],
			];
		}
		return $return;
	}
	
	public function getTools($user, $type = NULL, $deleted = 0, $orderBy = "logDate DESC")
	{
		$return = [];
		$rows = $this->model->getTools($user, $selectFields = "id", $type, $deleted, $orderBy);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getTool($row->id); }
		}
		
		return $return;
	}
	
	#Get tool type
	public function getToolType($id)
	{
		$row = $this->model->getToolType($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["name"] = $row->name;
			$return["orderNumber"] = $row->name;
		}
		return $return;
	}
	
	public function getToolTypes($deleted = 0, $orderBy = "orderNumber")
	{
		$return = [];
		$rows = $this->model->getToolTypes("id", $deleted, $orderBy);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getToolType($row->id); }
		}
		
		return $return;
	}
	
	#Create new tool
	public function newTool($data, $userID, $logUserID)
	{
		#Code
		if(isset($data["type"]) AND $data["type"] == 1)
		{
			$tools = $this->getTools($userID, 1, NULL);
			$codeCount = count($tools) + 1;
			$code = date("Y")."/AB".sprintf("%05d", $codeCount);
		}
		else { $code = NULL; }
		
		#Params and Insert
		$params = [
			"logDate" => date("Y-m-d H:i:s"),
			"logUser" => $logUserID,
			"user" => $userID,
			"type" => (isset($data["type"]) AND !empty($data["type"])) ? $data["type"] : 4,
			"name" => (isset($data["name"]) AND !empty($data["name"])) ? $data["name"] : "",
			"comment" => (isset($data["comment"]) AND !empty($data["comment"])) ? $data["comment"] : "",
			"quantity" => (isset($data["quantity"]) AND !empty($data["quantity"])) ? $data["quantity"] : "",
			"date" => (isset($data["date"]) AND !empty($data["date"])) ? $data["date"] : NULL,
			"code" => $code,
		];
		$toolID = $this->model->myInsert($this->model->tables("tools"), $params);
		return $toolID;
	}
	
	#Delete tool
	public function delTool($toolID)
	{
		if(isset($GLOBALS["user"])) { $this->model->myUpdate($this->model->tables("tools"), ["delUser" => $GLOBALS["user"]["id"]], $toolID); }
		return $this->model->myDelete($this->model->tables("tools"), $toolID);
	}	
	
	#Get workdress
	public function getWorkdress($id, $row = NULL, $type = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdress($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			
			if($type !== NULL) { $return["type"] = $type; }
			else { $return["type"] = ($row->type > 0) ? $this->getWorkdressType($row->type) : false; }
			$return["typeName"] = ($return["type"] !== false) ? $return["type"]["name"] : "N/A";
			
			$return["name"] = $row->name;
			$return["fullName"] = $return["typeName"]." - ".$return["name"];
			
			$return["defaultQuantity"] = (!empty($row->defaultQuantity)) ? $row->defaultQuantity : 0;
			$return["defaultQuantityOut"] = number_format($row->defaultQuantity, 0, ",", " ")." db";
			
			$return["wearOutTime"] = (!empty($row->wearOutTime)) ? $this->formatWearOutTimeStringOut($row->wearOutTime) : "";
			
			#Item numbers
			$return["itemNumbersString"] = $row->itemNumbers;
			$return["itemNumbers"] = (!empty($return["itemNumbersString"])) ? explode("|", $return["itemNumbersString"]) : [];
			
			#Sizes
			$return["sizesString"] = $row->sizes;
			$return["sizes"] = (!empty($return["sizesString"])) ? explode("|", $return["sizesString"]) : [];
			
			#User categories
			$return["userCategoriesString"] = $row->userCategories;
			$return["userCategoriesStringTrimmed"] = trim($return["userCategoriesString"], "|");
			$return["userCategories"] = (!empty($return["userCategoriesStringTrimmed"])) ? explode("|", $return["userCategoriesStringTrimmed"]) : [];
		}
		return $return;
	}
	
	public function getWorkdresses($type = NULL, $userCategories = NULL, $orderBy = "type, name", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getWorkdresses($type, $userCategories, $deleted, $orderBy);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdress($row->id, $row); }
		}
		
		return $return;
	}
	
	public function getWorkdressListByTypes($userCategories = NULL)
	{
		$return = [];
		$types = $this->getWorkdressTypes();
		if(count($types) > 0)
		{
			foreach($types AS $type)
			{
				$workdresses = $this->getWorkdresses($type["data"]->id, $userCategories);
				if(count($workdresses) > 0) 
				{
					$return[$type["data"]->id] = [
						"typeData" => $type,
						"workdresses" => $workdresses,
					];
				}
			}
		}
		
		return $return;
	}	
	
	#Get workdress type
	public function getWorkdressType($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdressType($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["url"] = $row->url;
			$return["name"] = $row->name;
		}
		return $return;
	}
	
	public function getWorkdressTypes($search = [], $orderBy = "id", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressTypes"), $search, $orderBy, $limit, $deleted);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdressType($row->id, $row); }
		}
		
		return $return;
	}
	
	#Get workdress claim-status
	public function getWorkdressClaimStatus($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdressClaimStatus($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["url"] = $row->url;
			$return["name"] = $row->name;
		}
		return $return;
	}
	
	public function getWorkdressClaimStatuses($search = [], $orderBy = "orderNumber", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressClaimStatuses"), $search, $orderBy, $limit, $deleted);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdressClaimStatus($row->id, $row); }
		}
		
		return $return;
	}
	
	#Get workdress claim-statuschange
	public function getWorkdressClaimStatusChange($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdressClaimStatusChange($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["date"] = (!empty($row->date)) ? date("Y. m. d. H:i", strtotime($row->date)) : "";
			
			#Status
			$return["status"] = ($row->status > 0) ? $this->getWorkdressClaimStatus($row->status) : false;
			$return["statusName"] = ($return["status"] !== false) ? $return["status"]["name"] : "N/A";
			
			#Previous status
			$return["prevStatus"] = ($row->prevStatus > 0) ? $this->getWorkdressClaimStatus($row->prevStatus) : false;
			$return["prevStatusName"] = ($return["prevStatus"] !== false) ? $return["prevStatus"]["name"] : "-";
			
			#user
			$return["user"] = $this->model->getUserByID($row->user);			
			if(isset($return["user"]->id) AND !empty($return["user"]->id))
			{
				$return["userName"] = $return["user"]->lastName." ".$return["user"]->firstName;
				if($return["user"]->del) { $return["userName"] .= " [KILÉPETT!]"; }
			}
			else { $return["userName"] = "N/A"; }
		}
		return $return;
	}
	
	public function getWorkdressClaimStatusChanges($search = [], $orderBy = "date DESC", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressClaimStatusChanges"), $search, $orderBy, $limit, $deleted);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdressClaimStatusChange($row->id, $row); }
		}
		
		return $return;
	}
	
	#Get workdress claim
	public function getWorkdressClaim($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdressClaim($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["userID"] = $row->user;
			$return["workdressID"] = $row->workdress;
			
			#Status
			$return["status"] = ($row->status > 0) ? $this->getWorkdressClaimStatus($row->status) : false;
			$return["statusName"] = ($return["status"] !== false) ? $return["status"]["name"] : "N/A";
			
			#Details
			$return["size"] = $row->size;
			$return["date"] = (!empty($row->date)) ? date("Y. m. d. H:i", strtotime($row->date)) : "";
			
			$return["quantity"] = (!empty($row->quantity)) ? $row->quantity : 0;
			$return["quantityOut"] = number_format($row->quantity, 0, ",", " ")." db";
			
			#Foreign rows
			$return["user"] = $this->model->getUserByID($row->user);
			$return["workdress"] = $this->getWorkdress($row->workdress);
			
			if(isset($return["user"]->id) AND !empty($return["user"]->id))
			{
				$return["userName"] = $return["user"]->lastName." ".$return["user"]->firstName;
				if($return["user"]->del) { $return["userName"] .= " [KILÉPETT!]"; }
			}
			else { $return["userName"] = "N/A"; }
		}
		return $return;
	}
	
	public function getWorkdressClaims($search = [], $orderBy = "date DESC", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressClaims"), $search, $orderBy, $limit, $deleted);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdressClaim($row->id, $row); }
		}
		
		return $return;
	}
	
	public function getWorkdressClaimGlobalList($search = [])
	{
		$where = [];
		if(isset($search["search"]) AND $search["search"])
		{
			if(isset($search["user"]) AND !empty($search["user"])) { $where[] = ["user", $search["user"]]; }
			if(isset($search["workdress"]) AND !empty($search["workdress"])) { $where[] = ["workdress", $search["workdress"]]; }
			if(isset($search["dateFrom"]) AND !empty($search["dateFrom"])) { $where[] = ["date", ">=", $search["dateFrom"]]; }
			if(isset($search["dateTo"]) AND !empty($search["dateTo"])) { $where[] = ["date", "<=", $search["dateTo"]." 23:59:59"]; }
			if(isset($search["statuses"]) AND !empty($search["statuses"])) { $where[] = ["status", "IN", $search["statuses"]]; }
		}
		
		return $this->getWorkdressClaims($where);
	}
	
	#Get workdress claim's give-out/take-back
	public function getWorkdressGiveOutTakeBack($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getWorkdressGiveOutTakeBack($id); }
		
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["date"] = (!empty($row->date)) ? date("Y. m. d. H:i", strtotime($row->date)) : "";
			$return["typeName"] = ($row->type == "GO") ? "Átadás" : "Visszavétel";
			
			#User
			$return["user"] = $this->model->getUserByID($row->user);			
			if(isset($return["user"]->id) AND !empty($return["user"]->id))
			{
				$return["userName"] = $return["user"]->lastName." ".$return["user"]->firstName;
				if($return["user"]->del) { $return["userName"] .= " [KILÉPETT!]"; }
			}
			else { $return["userName"] = "N/A"; }
			
			#Claims
			$return["claimsString"] = $row->claims;
			$return["claimsStringTrimmed"] = trim($return["claimsString"], "|");
			$return["claimIDs"] = (!empty($return["claimsStringTrimmed"])) ? explode("|", $return["claimsStringTrimmed"]) : [];
			
			$return["claims"] = [];
			if(count($return["claimIDs"]) > 0)
			{
				foreach($return["claimIDs"] AS $claimID) { $return["claims"][$claimID] = $this->getWorkdressClaim($claimID); }
			}
			
			#Uploaded files
			$files = new \App\Http\Controllers\FileController;
			$return["documentsType"] = "users-workdresses-claims-giveouts-takebacks";
			$return["documents"] = $files->getFileList($return["documentsType"], $row->id);	
		}
		return $return;
	}
	
	public function getWorkdressGiveOutsTakeBacks($search = [], $orderBy = "date DESC", $deleted = 0)
	{
		$return = [];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressClaimGiveOutsTakeBacks"), $search, $orderBy, $limit, $deleted);
		if(!empty($rows))
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getWorkdressGiveOutTakeBack($row->id, $row); }
		}
		
		return $return;
	}
	
	public function getWorkdressGiveOutOrTakeBackByClaim($type, $claimID, $orderBy = "date DESC", $deleted = 0)
	{
		$search = [
			["type", $type],
			["claims", "LIKE", "%|".$claimID."|%"],
		];
		
		$rows = $this->getWorkdressGiveOutsTakeBacks($search, $orderBy, $deleted);
		return (!empty($rows)) ? $rows[array_keys($rows)[0]] : false;
	}
	
	#Export tools
	public function exportWorkdressList()
	{
		#File data
		$charset = "iso-8859-2";
		$fileName = "munkaruhak_vedoeszkozok_".date("Ymd").".csv";
		
		header("Content-Type: text/csv; charset=".$charset);
		header("Content-Disposition: attachment; filename=\"".$fileName."\"");
		$output = fopen("php://output", "w");
		
		#Header row
		$headerRow = [
			"Típus",
			"Megnevezés",
			"Munkatárs kategória",
			"Méretek",
			"Alapértelmezett mennyiség",
			"Elhasználódás (csere)",
		];
		$headerRowOut = [];
		foreach($headerRow AS $headerRowItem) { $headerRowOut[] = mb_convert_encoding($headerRowItem, $charset, "utf-8"); }
		fputcsv($output, $headerRowOut, ";");
		
		#Rows
		$types = $this->getWorkdressListByTypes();
		if(count($types) > 0)
		{
			#User categories
			$categories = $this->getCategories("id", NULL);
			
			#Loops types & dresses
			foreach($types AS $typeID => $datas)
			{
				foreach($datas["workdresses"] AS $workdress)
				{
					$row = [
						"typeName" => $workdress["typeName"],
						"name" => $workdress["name"],
						"categoryName" => "[[ NINCS HOZZÁRENDELT KATEGÓRIA ]]",
						"sizes" => implode(", ", $workdress["sizes"]),
						"defaultQuantity" => ($workdress["defaultQuantity"] > 0) ? $workdress["defaultQuantity"] : "",
						"wearOutTime" => (!empty($workdress["wearOutTime"])) ? $workdress["wearOutTime"] : "",
					];
					
					if(count($workdress["userCategories"]) > 0)
					{
						foreach($workdress["userCategories"] AS $catID)
						{
							$row2 = $row;
							$row2["categoryName"] = (isset($categories[$catID])) ? $categories[$catID]["name"] : "Ismeretlen (id: ".$catID.")";
							
							$rowOut = [];
							foreach($row2 AS $rowItem) { $rowOut[] = mb_convert_encoding($rowItem, $charset, "utf-8"); }
							fputcsv($output, $rowOut, ";");
						}
					}
					else
					{
						$rowOut = [];
						foreach($row AS $rowItem) { $rowOut[] = mb_convert_encoding($rowItem, $charset, "utf-8"); }
						fputcsv($output, $rowOut, ";");
					}
				}
			}
		}
		#Rows NOT found
		else { fputcsv($output, [mb_convert_encoding("Nem található munkaruha!", $charset, "utf-8")], ";"); }
		
		#Close file
		fclose($output);
		exit;
	}
	
	#Create new claim
	public function claimWorkdress($datas, $user, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"claims" => [],
		];
		
		#Details
		if(isset($datas["workdressClaims"]) AND !empty($datas["workdressClaims"]))
		{
			$date = date("Y-m-d H:i:s");
			foreach($datas["workdressClaims"] AS $workdressInputKey => $workdress)
			{
				if(isset($workdress["workdressID"]) AND !empty($workdress["workdressID"]) AND isset($workdress["turnOn"]) AND $workdress["turnOn"] AND isset($workdress["quantity"]) AND $workdress["quantity"] > 0)
				{
					$params = [
						"user" => $user["id"],
						"workdress" => $workdress["workdressID"],
						"status" => 1,
						"date" => $date,
						"size" => (isset($workdress["size"]) AND !empty($workdress["size"])) ? $workdress["size"] : NULL,
						"quantity" => $workdress["quantity"],
						"comment" => (isset($workdress["comment"]) AND !empty($workdress["comment"])) ? $workdress["comment"] : NULL,
					];
					$claimID = $this->model->myInsert($this->model->tables("workdressClaims"), $params);
					
					$return["claims"][] = $claimID;
					
					$params2 = [
						"date" => $params["date"],
						"workdressClaim" => $claimID,
						"user" => $loginUser["id"],
						"status" => $params["status"],
						"comment" => $params["comment"],
					];
					$this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params2);
				}
			}
		}
		
		#Send e-mail
		if(!empty($return["claims"]))
		{
			$userHere = $this->userProfile($user);
			
			$claims = [];
			foreach($return["claims"] AS $claimID) { $claims[$claimID] = $this->getWorkdressClaim($claimID); }
			
			$email = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/intranet/users-workdresses-claim-new.php");
		}
		
		#Return
		return $return;
	}
	
	#Delete claim
	public function deleteWorkdressClaim($workdressClaim, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"claimID" => $workdressClaim["id"],
			"id" => NULL,
		];
		
		$date = date("Y-m-d H:i:s");
		$this->model->myUpdate($this->model->tables("workdressClaims"), ["deleted_at" => $date, "del" => 1], $workdressClaim["id"]);
		
		$params = [
			"date" => date("Y-m-d H:i:s"),
			"workdressClaim" => $workdressClaim["id"],
			"user" => $loginUser["id"],
			"status" => $workdressClaim["data"]->status,
			"prevStatus" => $workdressClaim["data"]->status,
			"comment" => "RENDSZERÜZENET: Igénylés törölve.",
		];
		$return["id"] = $this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params);
		
		return $return;
	}
	
	#Add comment to claim
	public function workdressClaimAddComment($datas, $workdressClaim, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"claimID" => $workdressClaim["id"],
			"id" => NULL,
		];
		
		if(isset($datas["comment"]) AND !empty($datas["comment"]))
		{
			$params = [
				"date" => date("Y-m-d H:i:s"),
				"workdressClaim" => $workdressClaim["id"],
				"user" => $loginUser["id"],
				"status" => $workdressClaim["data"]->status,
				"prevStatus" => $workdressClaim["data"]->status,
				"comment" => $datas["comment"],
			];
			$return["id"] = $this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params);
		}
		
		return $return;
	}
	
	#Set status of claims to ORDERED
	public function orderClaimedWorkdress($datas, $user, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"claims" => [],
		];
		
		#Details
		if(isset($datas["workdressClaims"]) AND !empty($datas["workdressClaims"]))
		{
			$date = date("Y-m-d H:i:s");
			foreach($datas["workdressClaims"] AS $claimID => $claimData)
			{
				if($claimData["turnOn"])
				{
					$params = [
						"status" => 2,
						"comment" => (isset($claimData["comment"]) AND !empty($claimData["comment"])) ? $claimData["comment"] : NULL,
					];
					if(isset($claimData["size"])) { $params["size"] = $claimData["size"]; }
					if(isset($claimData["quantity"])) { $params["quantity"] = $claimData["quantity"]; }
					$this->model->myUpdate($this->model->tables("workdressClaims"), $params, $claimID);
					$return["claims"][] = $claimID;
					
					$params2 = [
						"date" => $date,
						"workdressClaim" => $claimID,
						"user" => $loginUser["id"],
						"status" => $params["status"],
						"prevStatus" => (isset($claimData["status"]) AND !empty($claimData["status"])) ? $claimData["status"] : NULL,
						"comment" => $params["comment"],
					];
					$this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params2);
				}
			}
		}
		
		#Send e-mail
		if(!empty($return["claims"]))
		{
			$userHere = $this->userProfile($user);
			
			$claims = [];
			foreach($return["claims"] AS $claimID) { $claims[$claimID] = $this->getWorkdressClaim($claimID); }
			
			$email = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/intranet/users-workdresses-claim-status-order.php");
		}
		
		#Return
		return $return;
	}
	
	#Set status of claims to GIVEN OUT
	public function giveOutClaimedWorkdress($datas, $user, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"giveOutTakeBackID" => NULL,
			"claims" => [],
		];
		
		#Details
		$claims = [];
		$date = date("Y-m-d H:i:s");
		if(isset($datas["workdressClaims"]) AND !empty($datas["workdressClaims"]))
		{
			foreach($datas["workdressClaims"] AS $claimID => $claimData)
			{
				if($claimData["turnOn"])
				{
					#Update status and further datas
					$params = [
						"status" => 3,
						"comment" => (isset($claimData["comment"]) AND !empty($claimData["comment"])) ? $claimData["comment"] : NULL,
					];
					if(isset($claimData["size"])) { $params["size"] = $claimData["size"]; }
					if(isset($claimData["itemNumber"])) { $params["itemNumber"] = $claimData["itemNumber"]; }
					
					$this->model->myUpdate($this->model->tables("workdressClaims"), $params, $claimID);
					$return["claims"][] = $claimID;
					
					#Log the status-change
					$params2 = [
						"date" => $date,
						"workdressClaim" => $claimID,
						"user" => $loginUser["id"],
						"status" => $params["status"],
						"prevStatus" => (isset($datas["status"]) AND !empty($datas["status"])) ? $datas["status"] : NULL,
						"comment" => (isset($claimData["comment"]) AND !empty($claimData["comment"])) ? $claimData["comment"] : NULL,
					];
					$this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params2);
					
					#Set wearing out date
					$claims[$claimID] = $this->getWorkdressClaim($claimID);
					if($claims[$claimID] !== false AND $claims[$claimID]["workdress"] !== false AND !empty($claims[$claimID]["workdress"]["data"]->wearOutTime))
					{
						$wearOutDate = date("Y-m-d", strtotime("+".$claims[$claimID]["workdress"]["data"]->wearOutTime));
						$this->model->myUpdate($this->model->tables("workdressClaims"), ["wearOutDate" => $wearOutDate], $claimID);
						$claims[$claimID]["data"]->wearOutDate = $wearOutDate;
					}
				}
			}
		}
		
		if(!empty($return["claims"]))
		{
			#Create give-out form
			$params3 = [
				"user" => $user["id"],
				"date" => $date,
				"type" => "GO",
				"claims" => "|".implode("|", $return["claims"])."|",
			];
			$params3["code"] = $this->createCodeForWorkdressClaimGiveOutOrTakeBack($params3["type"]);
			$return["giveOutTakeBackID"] = $this->model->myInsert($this->model->tables("workdressClaimGiveOutsTakeBacks"), $params3);
			
			#Send e-mail
			$userHere = $this->userProfile($user);
			
			$email = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/intranet/users-workdresses-claim-status-given-out.php");
		}
		
		#Return
		return $return;
	}
	
	#Set status of claims to TAKEN BACK
	public function takeBackClaimedWorkdress($datas, $user, $loginUser)
	{
		$return = [
			"datas" => $datas,
			"userID" => $user["id"],
			"giveOutTakeBackID" => NULL,
			"claims" => [],
		];
		
		#Details
		$date = date("Y-m-d H:i:s");
		if(isset($datas["workdressClaims"]) AND !empty($datas["workdressClaims"]))
		{
			foreach($datas["workdressClaims"] AS $claimID => $claimData)
			{
				if($claimData["turnOn"])
				{
					#Update status and further datas
					$params = [
						"status" => 4,
						"comment" => (isset($claimData["comment"]) AND !empty($claimData["comment"])) ? $claimData["comment"] : NULL,
					];
					
					$this->model->myUpdate($this->model->tables("workdressClaims"), $params, $claimID);
					$return["claims"][] = $claimID;
					
					#Log the status-change
					$params2 = [
						"date" => $date,
						"workdressClaim" => $claimID,
						"user" => $loginUser["id"],
						"status" => $params["status"],
						"prevStatus" => (isset($datas["status"]) AND !empty($datas["status"])) ? $datas["status"] : NULL,
						"comment" => (isset($claimData["comment"]) AND !empty($claimData["comment"])) ? $claimData["comment"] : NULL,
					];
					$this->model->myInsert($this->model->tables("workdressClaimStatusChanges"), $params2);
				}
			}
		}
		
		if(!empty($return["claims"]))
		{
			#Create give-out form
			$params3 = [
				"user" => $user["id"],
				"date" => $date,
				"type" => "TB",
				"claims" => "|".implode("|", $return["claims"])."|",
			];
			$params3["code"] = $this->createCodeForWorkdressClaimGiveOutOrTakeBack($params3["type"]);
			$return["giveOutTakeBackID"] = $this->model->myInsert($this->model->tables("workdressClaimGiveOutsTakeBacks"), $params3);
			
			#Send e-mail
			$userHere = $this->userProfile($user);
			
			$claims = [];
			foreach($return["claims"] AS $claimID) { $claims[$claimID] = $this->getWorkdressClaim($claimID); }
			
			$email = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/intranet/users-workdresses-claim-status-taken-back.php");
		}
		
		#Return
		return $return;
	}
	
	#Format wearing out time-string
	public function formatWearOutTimeStringOut($string = "")
	{
		return str_replace(
			["years", "year", "months", "month", "weeks", "week", "days", "day"],
			["év", "év", "hónap", "hónap", "hét", "hét", "nap", "nap"],
			$string
		);
	}
	
	#Create Give-out OR Take-back form's code
	public function createCodeForWorkdressClaimGiveOutOrTakeBack($type)
	{
		$year = date("Y");
		$code = $year."/MR";
		
		if($type == "GO") { $code .= "AB"; }
		elseif($type == "TB") { $code .= "AVB"; }
		else { $code .= "NA"; }
		
		$search = [
			["type", $type],
			["date", "LIKE", $year."-%"],
		];
		$rows = $this->model->selectListFromTable($this->model->tables("workdressClaimGiveOutsTakeBacks"), $search, "id", NULL, 0);
		
		$code .= sprintf("%05d", count($rows) + 1);
		return $code;
	}
	
	#Get list of claims by wearing out date
	public function getWorkdressClaimsByWearOutDate($date = NULL)
	{
		if($date === NULL) { $date = date("Y-m-d"); }
		
		$search = [
			["status", 3],
			["wearOutDate", $date],
		];
		return $this->getWorkdressClaims($search, "user, workdress, date");
	}
	
	#Send notification about claims worn out
	public function sendNotificationAboutWorkdressClaimsWearOut($date = NULL)
	{	
		#Get claims
		$claims = $this->getWorkdressClaimsByWearOutDate($date);
		$claimsCount = count($claims);
		
		#Send e-mail
		if($claimsCount > 0)
		{
			$email = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/intranet/users-workdresses-notification-claims-worn-out.php");
		}
		
		#Return
		return $claimsCount;
	}
	
	public function sendNotificationAboutWorkdressClaimsWearOut1WeekBefore()
	{
		$date = date("Y-m-d", strtotime("+1 week"));
		$dateOut = "(1 hét múlva)";
		return $this->sendNotificationAboutWorkdressClaimsWearOut($date);
	}
	
	#Workdress give-out/take-back texts
	public function getWorkdressFormTexts()
	{
		return [
			"title" => [
				"atadas" => [
					"name" => "Átadási Bizonylat",
					"types" => ["GO"],
				],
				"atvetel" => [
					"name" => "Átvételi Bizonylat",
					"types" => ["TB"],
				],
			],
			"companyType" => [
				"atado" => [
					"url" => "atado",
					"name" => "Átadó",
					"types" => ["GO"],
				],
				"atvevo" => [
					"url" => "atvevo",
					"name" => "Átvevő",
					"types" => ["TB"],
				],
			],
			"company" => [
				"gablini" => [
					"name" => "Gablini Kft.",
					"zipCode" => "1141",
					"city" => "Budapest",
					"address" => "Nótárius utca 5-7.",
					"types" => ["GO", "TB"],
				],
			],
			"text" => [
				"gablini-vagyonkezelo-zrt-atadas" => [
					"name" => "Ezen átadás ív aláírásával elismerem, hogy a munkaruhák/védőeszközök továbbra is a Gablini Vagyonkezelő Zrt. tulajdonát képezi. A munkaruhák/védőeszközök nem megfelelő használatából eredő károsodás esetén a felmerülő költségeket vállalom.",
					"types" => ["GO"],
				],
				"gablini-vagyonkezelo-zrt-atvetel" => [
					"name" => "Ezen átvételi ív aláírásával a Gablini Vagyonkezelő Zrt. elismeri, hogy a korábban kiadott munkaruhákat és védőeszközöket megfelelő állapotban visszavette.",
					"types" => ["TB"],
				],
			],
			"tableHeaderCol1" => [
				"raktar" => [
					"name" => "Raktár",
					"types" => ["GO", "TB"],
				],
				"kiadas-helye" => [
					"name" => "Kiadás helye",
					"types" => ["GO", "TB"],
				],
			],
			"tableHeaderCol2" => [
				"tipus" => [
					"name" => "Típus",
					"types" => ["GO", "TB"],
				],	
			],
			"tableHeaderCol3" => [
				"megnevezes" => [
					"name" => "Megnevezés",
					"types" => ["GO", "TB"],
				],
				"munkaruha" => [
					"name" => "Munkaruha",
					"types" => ["GO", "TB"],
				],
				"vedoeszkoz" => [
					"name" => "Védőeszköz",
					"types" => ["GO", "TB"],
				],	
			],
			"tableHeaderCol4" => [
				"cikkszam" => [
					"name" => "Cikkszám",
					"types" => ["GO", "TB"],
				],
			],
			"tableHeaderCol5" => [
				"megjegyzes" => [
					"name" => "Megjegyzés",
					"types" => ["GO", "TB"],
				],
			],
			"tableHeaderCol6" => [
				"mennyiseg" => [
					"name" => "Mennyiség",
					"types" => ["GO", "TB"],
				],
			],
			"store" => [
				"raktar" => [
					"name" => "Raktár",
					"types" => ["GO", "TB"],
				],
			],
		];
	}
	
	public function getWorkdressFormTextsDefaultValues($type = NULL)
	{
		switch($type)
		{
			case "GO":
				return [
					"title" => "atadas",
					"companyType" => "atado",
					"company" => "gablini",
					"text" => "gablini-vagyonkezelo-zrt-atadas",
					"tableHeaderCol1" => "raktar",
					"tableHeaderCol2" => "tipus",
					"tableHeaderCol3" => "megnevezes",
					"tableHeaderCol4" => "cikkszam",
					"tableHeaderCol5" => "megjegyzes",
					"tableHeaderCol6" => "mennyiseg",
					"store" => "raktar",
				];
				break;
			case "TB":
				return [
					"title" => "atvetel",
					"companyType" => "atvevo",
					"company" => "gablini",
					"text" => "gablini-vagyonkezelo-zrt-atvetel",
					"tableHeaderCol1" => "raktar",
					"tableHeaderCol2" => "tipus",
					"tableHeaderCol3" => "megnevezes",
					"tableHeaderCol4" => "cikkszam",
					"tableHeaderCol5" => "megjegyzes",
					"tableHeaderCol6" => "mennyiseg",
					"store" => "raktar",
				];
				break;
			#Keys for $_GET	
			default:
				return [
					"title" => "szCim",
					"companyType" => "szCegtipus",
					"company" => "szCeg",
					"text" => "szSzoveg",
					"tableHeaderCol1" => "szTablazatFejlec1",
					"tableHeaderCol2" => "szTablazatFejlec2",
					"tableHeaderCol3" => "szTablazatFejlec3",
					"tableHeaderCol4" => "szTablazatFejlec4",
					"tableHeaderCol5" => "szTablazatFejlec5",
					"tableHeaderCol6" => "szTablazatFejlec6",
					"store" => "szRaktar",
				];
				break;	
		}
	}
	
	public function getWorkdressFormTextValues($type)
	{
		$texts = $this->getWorkdressFormTexts(); 
		$defaultValues = $this->getWorkdressFormTextsDefaultValues($type); 
		$getValues = $this->getWorkdressFormTextsDefaultValues(); 

		$return = [];
		foreach($texts AS $textKey => $textData)
		{
			$okay = true;
			
			#Isset GET value
			if(isset($getValues[$textKey]))
			{
				$getKey = $getValues[$textKey];
				if(isset($_GET[$getKey]) AND !empty($_GET[$getKey]))
				{
					if(isset($textData[$_GET[$getKey]]))
					{
						$return[$textKey] = $textData[$_GET[$getKey]];
						$okay = false;
					}
				}
			}
			
			#Default value is defined
			if($okay)
			{
				if(isset($defaultValues[$textKey]))
				{
					if(isset($textData[$defaultValues[$textKey]]))
					{
						$return[$textKey] = $textData[$defaultValues[$textKey]];
						$okay = false;
					}
				}
			}
			
			#No default value -> First item in array
			if($okay) { $return[$textKey] = $textData[array_keys($textData)[0]]; }
		}
		
		return $return;
	}
	
	#JSON encode and decode
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
	
	public function jsonDecode($json)
	{
		return json_decode($json, JSON_UNESCAPED_UNICODE);
	}
}
