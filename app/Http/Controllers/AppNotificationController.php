<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Notification;

class AppNotificationController extends BaseController
{	
	public $model;
	public $info;
	public $firebaseServerKey = "AAAA9PG8lAE:APA91bG1ITFgnXm-1MOYYgGTfX1OXgD42MDx8cu9r13OIgJhPhppVkGG41eANVn5R5NUywhgmdBkGiF-29q3fLnDkbl5sw9vHQloLoWFJfWlKzfY9ykfme225nhm9kYdnHSh6L26huqD";
	public $firebaseTokenAndroid = "dwxMHra2QMiDMQkiNMTTFk:APA91bEgEDNZpKWh2LM9Ic29EH6gP0JVzglRQNbA4Mwj36woGFxtd_25z5GLcOygb3VWjghNOYB5XFeVlk9d6NVCD27N82Ws09f2WA6dqi5T81uxwTfw5-Grfk7hEnwwGVkQV9n4nNTP";
	public $firebaseTokenIOS = "fAHdwhIGpkZ2kWAxFUbEAi:APA91bFw5DsK3FaOxEJhMMkkQseD2VUcaM1LAurYxc2TTMfMPOH9_EmMSNVCBXONqpKKKVX8Sg6Nzr9so8Yfu-qaykyuV2d3e7Swoy0mlKLevuz_8JIrzPW-g4mLQsoalBdssxILShlF";
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "AppNotification";
		$this->model = new \App\AppNotification($connectionData);
	}
	
	#Get notification
	public function getNotification($id, $row = NULL)
	{
		if($row === NULL) { $row = $this->model->getNotification($id); }
		if(!empty($row) AND is_object($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [
				"data" => $row,
				"title" => $row->title,
				"body" => $row->body,
				"shortText" => mb_substr(strip_tags($row->body), 0, 200, "UTF-8")."...",
				"dateOut" => (!empty($row->date) AND $row->date != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->date)) : "",
				"sendDateOut" => (!empty($row->sendDate) AND $row->sendDate != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->sendDate)) : "",
				"isSent" => ($row->isSent),
				"sentDate" => (!empty($row->sentDate) AND $row->sentDate != "0000-00-00 00:00:00") ? date("Y. m. d. H:i", strtotime($row->sentDate)) : "",
			];
			
			return $return;
		}
		else { return false; }
	}
	
	public function getNotifications($sendDateMax = NULL, $isSent = NULL, $deleted = 0)
	{
		$return = [];
		$rows = $this->model->getNotifications($sendDateMax, $isSent, $deleted);
		if($rows AND count($rows) > 0)
		{
			foreach($rows AS $row) { $return[$row->id] = $this->getNotification($row->id, $row); }
		}
		
		return $return;
	}
	
	#Work with notification
	public function delNotification($id)
	{
		return $this->model->myDelete($this->model->tables("notifications"), $id);
	}
	
	public function notificationWork($type, $datas)
	{
		if($type == "new" OR $type == "edit")
		{
			$params = [];
			$fields = ["title", "body", "bodyFull", "sendDate"];
			foreach($fields AS $field)
			{
				if(isset($datas[$field]) AND !empty($datas[$field])) { $params[$field] = $datas[$field]; }
				else { $params[$field] = NULL; }
			}
			
			if(isset($datas["sites"]) AND !empty($datas["sites"])) { $params["sites"] = "|".implode("|", $datas["sites"])."|"; }
			
			if($type == "new") 
			{ 
				$params["date"] = date("Y-m-d H:i:s");
				$id = $this->model->myInsert($this->model->tables("notifications"), $params);
			}
			elseif($type == "edit") 
			{
				$id = $datas["id"];
				$this->model->myUpdate($this->model->tables("notifications"), $params, $id);
			}
			
			return $id;
		}
		elseif($type == "send")
		{
			$params = [
				"sentDate" => date("Y-m-d H:i:s"),
				"sentResponse" => $datas["response"],
			];
			if($datas["type"] == "success") { $params["isSent"] = 1; }
			$this->model->myUpdate($this->model->tables("notifications"), $params, $datas["id"]);
		}
		else { return NULL; }
	}
	
	#Send notification
	public function sendNotification($id)
	{
		$row = $this->getNotification($id);
		if($row !== false) 
		{ 
			$response = $this->sendPushMessageToAllDevices($row["data"]->title, $row["data"]->body);
			$responseArray = json_decode($response, true);
			
			$type = ($responseArray["success"] > 0) ? "success" : "error";
			$this->notificationWork("send", ["type" => $type, "id" => $row["data"]->id, "response" => $response]);
			
			return $responseArray;
		}
		else { return "wrong-id"; }
	}
	
	public function sendNotificationsWithCron()
	{
		$rows = $this->getNotifications(date("Y-m-d H:i:s"), 0);
		if(count($rows) > 0)
		{
			$return = [];		
			foreach($rows AS $row) { $return[$row["data"]->id] = $this->sendNotification($row["data"]->id); }
			return $return;
		}
		else { return "no-rows"; }
	}
	
	#Send to firebase
	public function sendPushMessageCURL($datas)
	{
		$headers = [
			"Content-Type: application/json",
			"Authorization: key=".$this->firebaseServerKey,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datas));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		// $return = ($response === false) ? curl_error($ch) : true;
		curl_close($ch);
		
		return $response;
	}
	
	public function sendPushMessageToAllDevices($title, $body)
	{
		$notification = [
			"title" => $title,
			"body" => $body,
			"not_type" => "bigtext",
			"sound" => "default",
			"badge" => "0",
		];

		$datas = [
			"registration_ids" => [
				$this->firebaseTokenAndroid,
				$this->firebaseTokenIOS,
			], 
			"notification" => $notification, 
			"data" => $notification,
			"priority" => "high",
		];
		
		return $this->sendPushMessageCURL($datas);
	}
	
	public function sendPushMessageToDevice($token, $title, $body)
	{
		$notification = [
			"title" => $title,
			"body" => $body,
			"not_type" => "bigtext",
			"sound" => "default",
			"badge" => "0",
		];

		$datas = [
			"to" => $token, 
			"notification" => $notification, 
			"data" => $notification,
			"priority" => "high",
		];
		
		return $this->sendPushMessageCURL($datas);
	}	
}
