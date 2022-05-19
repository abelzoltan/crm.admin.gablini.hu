<?php
$file = new \App\Http\Controllers\FileController;
if(isset($_GET["from"]) AND !is_array($_GET["from"])) { $_GET["from"] = explode("/", $_GET["from"]); }
switch($routes[1])
{
	#Download
	case "download":
		$file->download($routes[2], $_GET["from"]);
		break;
	case "download-token":
		$file->downloadByToken($routes[2], $_GET["from"]);
		break;	
	case "download-hash":
		$file->downloadByHash($routes[2], $_GET["from"]);
		break;	
	#Watch	
	case "watch":
		$file->watch($routes[2], $_GET["from"]);
		break;
	case "watch-token":
		$file->watchByToken($routes[2], $_GET["from"]);
		break;	
	case "watch-hash":
		$file->watchByHash($routes[2], $_GET["from"]);
		break;
	case "watch-url":
		$file->watchByURL($routes[2], $_GET["from"]);
		break;	
	#Upload files
	case "upload":
		$fileReturn = $file->upload("files", $_POST["type"], $_POST["foreignKey"]);
		if(isset($_GET["from"]) AND !empty($_GET["from"]))
		{
			$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-upload"]);
		}
		break;
	#Ordering files
	case "order":
		$row = $file->getFile($routes[3]);
		$return = $file->newOrderFiles($routes[2], $routes[3], $row["file"]->type, $row["file"]->foreignKey);
		if(isset($_GET["from"]) AND !empty($_GET["from"]))
		{
			if($return === true) { $GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-order"]); }
			else { $GLOBALS["URL"]->redirect($_GET["from"], ["error" => "order-".$return]); }
		}
		break;
	#Edit files
	case "edit":
		if(count($_POST["files"]) > 0)
		{
			foreach($_POST["files"] AS $fileID => $params) { $file->editFile($fileID, $params); }
		}
		if(isset($_GET["from"]) AND !empty($_GET["from"]))
		{
			$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-edit"]);
		}
		break;		
	#Delete	
	case "del":
		$file->delFile($routes[2]);
		if(isset($_GET["from"]) AND !empty($_GET["from"]))
		{
			switch($_GET["from"][0])
			{
				#Users
				case "profile":
					$users->editUser(USERID, ["pic" => NULL]);
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;
				case "users":
					$row = $file->getFile($routes[2]);
					$users->editUser($row["file"]->foreignKey, ["pic" => NULL]);
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;	
				#Career
				case "career":
					$row = $file->getFile($routes[2]);
					$careers = new \App\Http\Controllers\CareerController;
					if($row["type"]->name == "career-job-details-1") { $work = "picDetails1"; }
					elseif($row["type"]->name == "career-job-details-2") { $work = "picDetails2"; }
					elseif($row["type"]->name == "career-job-details-3") { $work = "picDetails3"; }
					elseif($row["type"]->name == "career-job") { $work = "pic"; }
					else { $work = ""; }
					
					if(!empty($work)) { $careers->adminWork("jobs", $work, ["id" => $row["file"]->foreignKey, "pic" => NULL]); }
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;	
				#Sliders
				case "sliders":
					$row = $file->getFile($routes[2]);
					if($row["type"]->name == "site-slider") { $workType = "pic-pc"; }
					elseif($row["type"]->name == "site-slider-mobile") { $workType = "pic-mobile"; }
					else { $workType = ""; }
					
					if(!empty($workType)) { $site->adminWork("sliders", $workType, ["id" => $row["file"]->foreignKey, "pic" => NULL]); }
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;
				#Cars	
				case "new-car":
					$row = $file->getFile($routes[2]);
					$car = new \App\Http\Controllers\CarController;
					if($row["type"]->name == "new-car-pic-list") { $work = "picList"; }
					elseif($row["type"]->name == "new-car-pic-form") { $work = "picForm"; }
					elseif($row["type"]->name == "new-car-pic-form-mobile") { $work = "picFormMobile"; }
					else { $work = ""; }
					
					if(!empty($work)) { $car->adminWork("cars", $work, ["id" => $row["file"]->foreignKey, "pic" => NULL]); }
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;
				#Contents	
				case "news":
					$row = $file->getFile($routes[2]);
					$contents = new \App\Http\Controllers\ContentController;
					if($row["type"]->name == "contents") { $work = "pic"; }
					elseif($row["type"]->name == "contents-details") { $work = "picDetails"; }
					else { $work = ""; }
					
					if(!empty($work)) { $contents->model->myUpdate($contents->model->tables("contents"), [$work => NULL], $row["file"]->foreignKey); }
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;	
				default:
					$GLOBALS["URL"]->redirect($_GET["from"], ["success" => "file-del"]);
					break;
			}
		}
		$GLOBALS["URL"]->redirect([], ["success" => "file-del"]);
		break;
}
?>