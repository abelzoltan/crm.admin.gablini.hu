<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Email;

class EmailController extends BaseController
{
	public $model;
	
	#Variables for send()
	public $frameName;
	public $subject;
	public $body;
	public $addresses; // Format: $addresses[] = ["type" => "to", "email" => "email@domain.com", "name" => "Test Address"];
	public $from;
	public $replyTo;
	public $attachments; //Format: $attachments[] = ["path" => "path/to/file", "name" => "Test Name"];
	public $images; //Format: $attachments[] = ["path" => "path/to/file", "web" => "path/to/file/in/web", "name" => "Test Name"];
	public $variables;
	public $errorInfo;
	
	#Variables for log()
	public $mailObject;
	public $logID;
	public $logInfo;
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Email";
		$this->model = new \App\Email($connectionData);
		$this->frameName = (isset($GLOBALS["site"])) ? $GLOBALS["site"]->data->url : "";
	}
	
	#Datas
	public function datas($name = "")
	{
		$return = [
			"fromEmail" => EMAIL_FROM_EMAIL,
			"fromName" => EMAIL_FROM_NAME,
			"replyToEmail" => EMAIL_REPLYTO_EMAIL,
			"replyToName" => EMAIL_REPLYTO_NAME,
			"isSMTP" => SMTP_ON,
			"auth" => SMTP_AUTH,
			"secure" => SMTP_SECURE,
			"host" => SMTP_HOST,
			"port" => SMTP_PORT,
			"username" => SMTP_USERNAME,
			"password" => SMTP_PASSWORD,
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get email body from file
	public function bodyFile($fileName, $dir = "emails")
	{
		$file = base_path("resources/views/".$dir."/".$fileName.".html");
		if(file_exists($file)) { $return = file_get_contents($file); }
		else { $return = ""; }
		return $return;
	}
	
	public function setBody($fileName, $dir = "emails")
	{
		return $this->bodyFile($fileName, $dir);
	}
	
	#Watch email = create body
	public function watch($body = "", $subject = "", $frame = "")
	{
		if(empty($body)) { $body = $this->body; }
		if(empty($subject)) { $subject = $this->subject; }
		if(empty($frame)) { $frame = $this->frameName; }
		
		$file = base_path("resources/views/".$frame.".html");
		if(file_exists($file))
		{
			$emailFrame = file_get_contents($file);
			$emailBody = $this->changeHtmlVariable($emailFrame, ["body" => $body, "subject" => $subject]);
			
			if(!empty($this->variables)) { $return = $this->changeHtmlVariable($emailBody, $this->variables); }
			else { $return = $emailBody; }
		}
		else { $return = ""; }
		
		return $return;
	}
	
	public function watchWithEmbeddedImages($body = "", $subject = "", $frame = "")
	{
		$return = $this->watch($body = "", $subject = "", $frame = "");
		if(!empty($this->images))
		{
			foreach($this->images AS $imageKey => $image) { $return = str_replace("cid:".$image["name"], $image["web"], $return); }
		}
		return $return;
	}
	
	#Send email (not from DB)
	public function send($frameName = "")
	{
		#Variables
		$datas = $this->datas();
		
		#Email sender class
		require_once("PHPMailer/PHPMailerAutoload.php");
		
		#PHPMailer  - Initialize: smtp, wordwrap, charset
		$mail = new \PHPMailer();	
		if($datas["isSMTP"]) { $mail->IsSMTP(); }
		$mail->SMTPAuth = $datas["auth"];
		if(!empty($datas["secure"])) { $mail->SMTPSecure = $datas["secure"]; }
		if(!empty($datas["host"])) { $mail->Host = $datas["host"]; }
		if(!empty($datas["port"])) { $mail->Port = $datas["port"]; }
		if(!empty($datas["username"])) { $mail->Username = $datas["username"]; }
		if(!empty($datas["password"])) { $mail->Password = $datas["password"]; }
		
		$mail->WordWrap = 80;
		$mail->CharSet = "utf-8";
		
		#From and replyTo
		if(empty($this->from) OR empty($this->from["email"]))
		{
			$this->from["email"] = $datas["fromEmail"];
			$this->from["name"] = $datas["fromName"];
		}
		$mail->SetFrom($this->from["email"], $this->from["name"]);
		if(empty($this->replyTo))
		{ 
			$this->replyTo["email"] = $datas["replyToEmail"];
			$this->replyTo["name"] = $datas["replyToName"];
		}
		if(!empty($this->replyTo["email"])) { $mail->AddReplyTo($this->replyTo["email"], $this->replyTo["name"]); }
		
		#Addresses
		foreach($this->addresses AS $row) 
		{
			if($row["type"] == "cc") { $functionName = "AddCC"; }
			elseif($row["type"] == "bcc") { $functionName = "AddBCC"; }
			else { $functionName = "AddAddress"; }

			$mail->$functionName($row["email"], $row["name"]);
		}
		
		#Subject, Body
		if(!empty($frameName)) { $this->frameName = $frameName; }
		$mail->Subject = $this->subject;
		$mail->MsgHTML($this->watch());
		
		#Attachments
		if(!empty($this->attachments))
		{
			foreach($this->attachments AS $attachment)
			{
				$mail->AddAttachment($attachment["path"], $attachment["name"]);
			}
		}
		
		#Embed images
		if(!empty($this->images))
		{
			foreach($this->images AS $image)
			{
				$mail->AddEmbeddedImage($image["path"], $image["name"], $image["name"]);
			}
		}
		
		#Send
		$mail->Send();
		
		#Log and Return
		$this->errorInfo = $mail->ErrorInfo;
		$this->mailObject = $mail;
		$logID = $this->log();
		return $logID;
	}
	
	#Log email sent
	public function log($emailID = NULL)
	{
		$model = $this->model;
		$info = [];
		
		if(!empty($this->variables)) { $body = $this->changeHtmlVariable($this->body, $this->variables); }
		else { $body = $this->body; }
		
		$params = [
			"date" => date("Y-m-d H:i:s"), 
			"email" => $emailID, 
			"error" => $this->errorInfo, 
			"subject" => $this->subject, 
			"body" => $body,
			"frameName" => $this->frameName,
			"fromName" => $this->from["name"],
			"fromEmail" => $this->from["email"],
			"replyToName" => $this->replyTo["name"],
			"replyToEmail" => $this->replyTo["email"],
		];
		$lastID = $model->myInsert($model->tables("sent"), $params);
		$info["params"] = $params;
		$info["email"] = $lastID;
		
		foreach($this->addresses AS $row) 
		{
			$params2 = [
				"sentEmail" => $lastID, 
				"type" => $row["type"], 
				"emailAddress" => $row["email"], 
				"name" => $row["name"]
			];
			$lastAddressID = $model->myInsert($model->tables("sent_addresses"), $params2);
			
			$email = $row["email"];
			$info["addresses"][$email] = $lastAddressID;
		}
		
		$this->logID = $lastID;
		$this->logInfo = $info;	
		
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("emails-send", ["int1" => $lastID, "text1" => $GLOBALS["log"]->json($info)]);
		}
		
		return $lastID;
	}
	
	#Contact message - check rows
	public function contactMessageCheckRows($fromWhere, $limit = 0, $field = NULL, $returnAllData = false)
	{
		$return = true;
		if(!empty($limit))
		{
			$table = $this->model->tables("contactMessages");
			$registrations = 0;				
			
			if(empty($field))
			{
				$rows = $this->model->select("SELECT id FROM ".$table." WHERE del = '0' AND _fromWhere = :fromWhere", ["fromWhere" => $fromWhere]);
				$registrations = count($rows); 
			}
			else
			{
				$rows = $this->model->select("SELECT id, ".$field." FROM ".$table." WHERE del = '0' AND _fromWhere = :fromWhere", ["fromWhere" => $fromWhere]);
				if(!empty($rows)) { foreach($rows AS $row) { $registrations += (int)$row->$field; } }
			}
			
			if($registrations >= $limit) { $return = false; }
		}
		
		if($returnAllData)
		{
			$okay = $return;
			$return = [
				"okay" => $okay,
				"limit" => $limit,
				"registrations" => $registrations,
			];
		}
		
		return $return;
	}
	
	#Contact message
	public function contactMessage($datas, $brand = NULL)
	{
		#Datas		
		$required = ["listCarID", "model", "name", "email", "phone", "identityCard", "carBrand", "carType", "carRegNumber", "carBodyNumber"];
		$fields = ["newsletter", "premiseAddress", "subject", "model", "name", "email", "phone", "address", "identityCard", "msg", "carBrand", "carType", "carYear", "carRegNumber", "carBodyNumber", "carKM", "serviceBringTake", "serviceCourtesyCar", "chosenDate", "chosenDate2"];
		
		$return = [
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
			"id" => NULL,
		];
		
		#Required check
		foreach($required AS $item)
		{
			if(isset($datas[$item]) AND empty($datas[$item]))
			{
				$return["type"] = "error";
				$return["errors"][] = "missing-fields";
			}
		}
		
		#Everything is OK - Database insert
		if($return["type"] == "success")
		{
			$table = $this->model->tables("contactMessages");
			$params = [];
			foreach($fields AS $key)
			{
				if(isset($datas[$key])) 
				{ 
					if($key == "premiseAddress") 
					{
						$rowController = new \App\Http\Controllers\PremiseController;
						$params[$key."Name"] = $rowController->model->getAddress($datas[$key])->nameOut;
					}
					
					$params[$key] = $datas[$key];
				}
				else
				{
					if($datas["type"] == "szerviz-bejelentkezes" AND ($key == "serviceBringTake" OR $key == "serviceCourtesyCar")) { $params[$key] = 0; }
				}
			}
			
			$params["acceptTerms"] = 1;
			$params["site"] = SITE;
			$params["brand"] = $brand;
			$params["date"] = date("Y-m-d H:i:s");
			$params["referer"] = $_SERVER["HTTP_REFERER"];
			$params["ip"] = $_SERVER["REMOTE_ADDR"];
			$params["browser"] = $_SERVER["HTTP_USER_AGENT"];
			$params["resolution"] = NULL;
			$params["deviceType"] = DEVICE_TYPE;
			$params["_token"] = $datas["_token"];
			$params["_type"] = $datas["type"];
			$params["_fromWhere"] = $datas["fromWhere"];
			$params["hash"] = sha1($params["date"].$params["site"].$params["ip"].uniqid());
			
			$return["id"] = $this->model->insert($table, $params);			
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$logParams = ["text1" => $GLOBALS["log"]->json($return)];
			if($return["type"] == "success")
			{
				$logParams["int1"] = 1;
				$logParams["int2"] = $return["id"];
				$logParams["text2"] = $GLOBALS["log"]->json($params);
			}
			else { $logParams["int1"] = 0; }
			$GLOBALS["log"]->log("emails-contact", $logParams);
		}
		
		#Return
		return $return;
	}
	
	public function getContactMessage($id)
	{
		$return = [];
		$return["data"] = $msg = $this->model->getContactMessages($id);	
		$return["id"] = $msg->id;
		
		switch($msg->_type)
		{
			case NULL: $return["type"] = "Hírlevél fel- / leiratkozás"; break;
			case "muszaki-bejelentkezes": $return["type"] = "Műszaki bejelentkezés"; break;
			case "szerviz-bejelentkezes": 
				if($msg->_fromWhere == "muszaki-bejelentkezes") { $return["type"] = "Műszaki bejelentkezés"; }
				elseif($msg->_fromWhere == "gumiabroncsok") { $return["type"] = "Gumiabroncs bejelentkezés"; }
				else { $return["type"] = "Szerviz bejelentkezés"; }				
				break;
			case "tesztvezetes": $return["type"] = "Tesztvezetésre jelentkezés"; break;
			default: 
				if($msg->_fromWhere == "tesztvezetes") { $return["type"] = "Tesztvezetésre jelentkezés"; }
				else { $return["type"] = "Kapcsolatfelvétel"; }
				break;
		}
		
		if(isset($GLOBALS["site"]) AND !empty($msg->site)) { $site = $GLOBALS["site"]->getSite($msg->site, false)["data"]->name;  }
		else { $site = NULL; }
		
		if(!empty($msg->brand)) 
		{ 
			$brandController = new \App\Http\Controllers\CarController;
			$brand = $brandController->model->getBrand($msg->brand)->nameOut;
		}
		else { $brand = NULL; }
		
		if($msg->serviceBringTake == 1) { $serviceBringTake = "Igen"; }
		elseif($msg->serviceBringTake === 0 OR $msg->serviceBringTake === "0") { $serviceBringTake = "Nem"; }
		else { $serviceBringTake = ""; }
		
		if($msg->serviceCourtesyCar == 1) { $serviceCourtesyCar = "Igen"; }
		elseif($msg->serviceCourtesyCar === 0 OR $msg->serviceCourtesyCar === "0") { $serviceCourtesyCar = "Nem"; }
		else { $serviceCourtesyCar = ""; }

		$return["details"] = [
			"basic" => [],
			"data" => [],
			"meta" => [],
		];
		
		$return["details"]["basic"] = [
			"subject" => ["name" => "Tárgy", "value" => $msg->subject], 
			"premiseAddressName" => ["name" => "Telephely", "value" => $msg->premiseAddressName], 
			"name" => ["name" => "Név", "value" => $msg->name], 
			"email" => ["name" => "E-mail cím", "value" => $msg->email], 
			"phone" => ["name" => "Telefonszám", "value" => $msg->phone], 
			"address" => ["name" => "Levelezési cím", "value" => $msg->address], 
			"identityCard" => ["name" => "Személyi igazolvány", "value" => $msg->identityCard], 
			"msg" => ["name" => "Üzenet", "value" => nl2br($msg->msg)], 
		];
		
		$return["details"]["data"] = [		
			"carBrand" => ["name" => "Autó márkája", "value" => $msg->carBrand], 
			"carType" => ["name" => "Autó típusa", "value" => $msg->carType], 
			"carYear" => ["name" => "Autó évjárata", "value" => $msg->carYear], 
			"carRegNumber" => ["name" => "Autó rendszáma", "value" => $msg->carRegNumber], 
			"carBodyNumber" => ["name" => "Autó alvázszáma", "value" => $msg->carBodyNumber], 
			"carKM" => ["name" => "Kilométer óra állása", "value" => $msg->carKM], 
			"serviceBringTake" => ["name" => "Hozom-viszem szolgáltatás", "value" => $serviceBringTake], 
			"serviceCourtesyCar" => ["name" => "Csereautó", "value" => $serviceCourtesyCar], 
			"chosenDate" => ["name" => "Kiválasztott időpont", "value" => $msg->chosenDate], 
			"chosenDate2" => ["name" => "Kiválasztott időpont", "value" => $msg->chosenDate2], 
		];
		
		$return["details"]["meta"] = [
			"site" => ["name" => "Oldal", "value" => $site], 
			"brand" => ["name" => "Márka", "value" => $brand], 
			"date" => ["name" => "Dátum", "value" => $msg->date], 
			"referer" => ["name" => "Honnan?", "value" => $msg->referer],  
			"ip" => ["name" => "IP cím", "value" => $msg->ip],  
			"browser" => ["name" => "Böngésző", "value" => $msg->browser],  
			"resolution" => ["name" => "Felbontás", "value" => $msg->resolution],  
			"deviceType" => ["name" => "Eszköz típusa", "value" => $msg->deviceType], 
			"_type" => ["name" => "Kapcsolatfelvétel típusa", "value" => $msg->_type],  
			"_fromWhere" => ["name" => "Kapcsolatfelvétel oldala", "value" => $msg->_fromWhere],  
		];
		
		#Return
		return $return;
	}
	
	public function getContactMessageByHash($hash, $delCheck = 1)
	{
		$id = $this->model->getContactMessagesByHash($hash, "id", $delCheck);
		if(!empty($id)) { return $this->getContactMessage($id); }
		else { return false; }
	}
	
	public function delContactMessageByHash($hash)
	{
		$id = $this->model->getContactMessagesByHash($hash, "id");
		if(!empty($id)) { return $this->model->myDelete($this->model->tables("contactMessages"), $id); }
		else { return false; }
	}
	
	#Newsletter
	public function newsletter($subscribe, $name, $email, $unsubscribeOnFalse = false)
	{
		#Datas
		$return = [
			"workType" => NULL,
			"date" => date("Y-m-d H:i:s"),
			"subscribe" => $subscribe,
			"name" => $name,
			"email" => $email,
			"unsubscribeOnFalse" => $unsubscribeOnFalse,
			"emailData" => NULL,
			"params" => NULL,
			"id" => NULL,
			"rowCount" => 0,
		];
		
		$table = $this->model->tables("newsletter");
		$params = [];
		$return["emailData"] = $emailData = explode("@", $email);
		
		#Process
		if(isset($emailData[1]) AND !empty($emailData[1]))
		{
			$params["site"] = SITE;
			$params["emailUser"] = $emailData[0];
			$params["emailDomain"] = $emailData[1];
			$query = "SELECT * FROM ".$table." WHERE del = '0' AND site = :site AND emailUser = :emailUser AND emailDomain = :emailDomain";
			$rows = $this->model->select($query, $params);
			if(isset($rows[0]) AND isset($rows[0]->id) AND !empty($rows[0]->id)) { $rowsExists = true; }
			else { $rowsExists = false; }
			
			#Subscribe
			if($subscribe)
			{
				#Already has a subscription
				if($rowsExists) 
				{ 
					$return["workType"] = "subscribe-already-exists";
					$return["id"] = $rows[0]->id;
				}
				#NEW subscription
				else 
				{ 
					$return["workType"] = "new-subscribe";
					$params["name"] = $name;
					$params["dateSubscribe"] = $return["date"];
					$return["id"] = $id = $this->model->myInsert($table, $params);
				}
			}
			#Unsubscribe
			elseif(!$subscribe AND $unsubscribeOnFalse)
			{
				#Has subscription --> delete
				if($rowsExists)
				{
					$return["workType"] = "unsubscribe";
					$return["id"] = $rows[0]->id;
					$this->model->myUpdate($table, ["dateUnsubscribe" => $return["date"]], $return["id"]);
					$this->model->myDelete($table, $return["id"]);
				}
				#No subscription
				else 
				{ 
					$return["workType"] = "unsubscribe-subscription-not-exists";
					$params["name"] = $name;
					$return["id"] = $id = $this->model->myInsert($table, $params);
					$this->model->myUpdate($table, ["dateUnsubscribe" => $return["date"]], $return["id"]);
					$this->model->myDelete($table, $return["id"]);
				}
			}
			#Unsubscribe - no permission
			else
			{
				$return["workType"] = "unsubscribe-but-not-allowed";
				if($rowsExists) { $return["id"] = $rows[0]->id; }
			}
		}
		else { $return["workType"] = "invalid-email"; }
		
		$return["params"] = $params;
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$logParams = [
				"int1" => $return["subscribe"],
				"int2" => $return["id"],
				"vchar1" => $return["workType"],
				"vchar2" => $return["email"],
				"vchar3" => $return["name"],
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("emails-newsletter", $logParams);
		}
		
		#Return
		return $return;
	}
	
	#Newsletter - Email format
	public function newsLetterEmail($user, $domain)
	{
		return trim($user)."@".trim($domain);
	}
	
	#Newsletter - Robinson-list
	public function newsLetterRobinson($dateFrom = NULL, $dateTo = NULL)
	{
		$return = [];
		$rows = $this->model->newsLetterRobinson($dateFrom, $dateTo);
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				$row->email = $email = $this->newsLetterEmail($row->emailUser, $row->emailDomain);				
				$return[$row->email] = $row;
			}
		}
		
		return $return;
	}
	
	#Newsletter - Rebound-list
	public function newsLetterRebounds()
	{
		$return = [];
		$rows = $this->model->newsLetterRebounds();
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				$row->email = trim($row->email);		
				$emailData = explode("@", $row->email);
				$row->emailUser = $emailData[0];
				$row->emailDomain = $emailData[1];
				$return[$row->email] = $row;
			}
		}
		
		return $return;
	}
	
	#Newsletter - Invalid-list
	public function newsLetterInvalids()
	{
		$return = [];
		$rows = $this->model->newsLetterInvalids();
		if(count($rows) > 0)
		{
			foreach($rows AS $row)
			{
				$row->email = trim($row->email);		
				$emailData = explode("@", $row->email);
				$row->emailUser = $emailData[0];
				$row->emailDomain = $emailData[1];
				$return[$row->email] = $row;
			}
		}
		
		return $return;
	}
	
	#Newsletter - Get lists
	public function newsLetterList($siteList = [], $userList = false, $dateFrom = NULL, $dateTo = NULL)
	{
		$return = [
			"out" => [],
			"afterSelect" => [],
			"wrongs" => [],
			"invalids" => [],
			"rebounds" => [],
			"unsubscribes" => [],
			"count" => [
				"out" => 0,
				"afterSelect" => 0,
				"wrongs" => 0,
				"invalids" => 0,
				"rebounds" => 0,
				"unsubscribes" => 0,
			],
			"names" => [
				"out" => "E-mail címek [OK]",
				"afterSelect" => "Lekérés utáni e-mail címek",
				"wrongs" => "Rossz e-mail címek (filter)",
				"invalids" => "Rossz e-mail címek (invalid)",
				"rebounds" => "Visszapattanó e-mail címek",
				"unsubscribes" => "Leiratkozott",
			],
		];
		
		#Get datas
		$rows = [];
		if(!empty($siteList)) { $rows = $this->model->newsLetterList($siteList, $dateFrom, $dateTo); }
		if($userList)
		{
			$users = new \App\Http\Controllers\UserController;
			$userList = $users->getUsers();

			foreach($userList["all"] AS $userID => $user)
			{
				$emailData = explode("@", $user["email"]);
				$rows[] = (object)[
					"id" => "u".$user["data"]->id,
					"created_at" => $user["data"]->created_at,
					"updated_at" => $user["data"]->updated_at,
					"deleted_at" => $user["data"]->deleted_at,
					"del" => $user["data"]->del,
					"site" => 1,
					"name" => $user["name"],
					"email" => $user["email"],
					"emailUser" => trim($emailData[0]),
					"emailDomain" => trim($emailData[1]),
					"dateSubscribe" => $user["data"]->regDate,
				];
			}
		}

		if(count($rows) > 0)
		{
			#Store after-query data
			$return["afterSelect"] = $rows;
			$return["count"]["afterSelect"] = count($rows);
			
			#Get invalid lists from database
			$invalids = [];
			$invalidRows = $this->model->newsLetterInvalids();
			foreach($invalidRows AS $invalidRow) { $invalids[] = trim($invalidRow->email); }
			
			#Get rebound lists from database
			$rebounds = [];
			$reboundRows = $this->model->newsLetterRebounds();
			foreach($reboundRows AS $reboundRow) { $rebounds[] = trim($reboundRow->email); }
			
			#----------------
			#Rows after query
			$key = "email";
			foreach($rows AS $row)
			{
				$row->email = $email = $this->newsLetterEmail($row->emailUser, $row->emailDomain);				
				
				#If wrong address
				if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					$return["wrongs"][$row->$key] = $row;
					$return["count"]["wrongs"]++;
				}
				#If invalid address
				elseif(in_array($email, $invalids))
				{
					$return["invalids"][$row->$key] = $row;
					$return["count"]["invalids"]++;
				}
				#If rebound address
				elseif(in_array($email, $rebounds))
				{
					$return["rebounds"][$row->$key] = $row;
					$return["count"]["rebounds"]++;
				}
				#IF MAIN CHECKS ARE OK
				else
				{
					$unsubscribes = $this->model->newsLetterUnsubscribes($row->dateSubscribe, $row->emailUser, $row->emailDomain);
					#If email has an unsubscription
					if(count($unsubscribes) > 0) 
					{
						$return["unsubscribes"][$row->$key] = $row;
						$return["count"]["unsubscribes"]++;
					}
					#OKAY
					else
					{
						$return["out"][$row->$key] = $row;
						$return["count"]["out"]++;
					}
				}
			}
		}
		
		return $return;
	}
}
?>