<?php
#Basic settings
$basicRanks = $users->loginAcceptedRanks = [3, 4, 5, 6, 8];
$users->loginAcceptedRanks[] = 9; // Enying - Service todo
$users->loginAcceptedRanks[] = 10; // ??? - Newcar sellings todo

$ranksForEnyingMenu = $basicRanks;
$ranksForEnyingMenu[] = 9;

$ranksForNewCarSellingsMenu = $basicRanks;
$ranksForNewCarSellingsMenu[] = 10;

$this->MAIN = ["URL" => $GLOBALS["URL"]->getURLdata()];
$this->MAIN["SITE"] = $site->data;
$VIEW["vars"] = [];
$routes = $GLOBALS["URL"]->routes;

#Remember me
if(!$_SESSION[USER_LOGGED_IN] AND isset($_COOKIE["rememberMe"]) AND !empty($_COOKIE["rememberMe"]))
{
	$userID = $users->model->getUserByRememberToken($_COOKIE["rememberMe"], "id");
	if(!empty($userID))
	{
		$_SESSION[USER_LOGGED_IN] = true;
		$_SESSION[USER_ID_KEY] = $userID;
		header("Refresh:0");
		exit;
	}
}

$siteURL = $site->data->url;

#Meta
$VIEW["titlePrefix"] = $site->data->titlePrefix;
$VIEW["titleSuffix"] = $site->data->titleSuffix;
$VIEW["title"] = "CRM rendszer";
$VIEW["name"] = implode("-", $routes); 
$VIEW["meta"] = [
	"keywords" => "",
	"description" => "Gablini.hu CRM rendszer",
	"og:title" => $VIEW["title"],
	"og:image" => URL::asset("pics/logo-facebook.png"),
	"og:description" => "Gablini.hu CRM rendszer",
	"og:site_name" => $site->data->name,
	"og:type" => "website",
	"og:url" => $GLOBALS["URL"]->currentURL,
];

if($_SESSION[USER_LOGGED_IN]) 
{ 
	#Customers
	$customers = new \App\Http\Controllers\CustomerController;
	define("CUSTOMER_CODEBEFORE", $customers->codeBefore);
	define("CUSTOMER_CODENAME", $customers->codeName);
	define("CUSTOMER_TOKENNAME", $customers->tokenName);
	
	#Navigation
	include($siteURL."/_navigation.php");
	
	#Routes
	switch($routes[0])
	{
		#PHP Info
		case "get-php-info":
			echo "<pre style='text-align: center;'>";
			echo "<strong>allow_url_fopen</strong> => ".ini_get("allow_url_fopen")."<br>";
			echo "<strong>allow_url_include</strong> => ".ini_get("allow_url_include")."<br>";
			echo "<strong>display_errors</strong> => ".ini_get("display_errors")."<br>";
			echo "<strong>file_uploads</strong> => ".ini_get("file_uploads")."<br>";
			echo "<strong>max_execution_time</strong> => ".ini_get("max_execution_time")."<br>";
			echo "<strong>max_input_time</strong> => ".ini_get("max_input_time")."<br>";
			echo "<strong>max_input_vars</strong> => ".ini_get("max_input_vars")."<br>";
			echo "<strong>memory_limit</strong> => ".ini_get("memory_limit")."<br>";
			echo "<strong>session.gc_maxlifetime</strong> => ".ini_get("session.gc_maxlifetime")."<br>";
			echo "<strong>session.save_path</strong> => ".ini_get("session.save_path")."<br>";
			echo "<strong>upload_max_filesize</strong> => ".ini_get("upload_max_filesize")."<br>";
			echo "</pre><br><br>";
			echo phpinfo();
			exit;
			break;
		#Session lifetime
		case "session-lifetime":
			echo 1;
			exit;
			break;	
		#Homepage
		case "":
			if($GLOBALS["user"]["data"]->rank == 9) { $URL->redirect(["service-todo-home"]); }
			elseif($GLOBALS["user"]["data"]->rank == 10) { $URL->redirect(["new-car-sellings-home"]); }
			else { include($siteURL."/home.php"); }
			break;	
		case $site->data->homepageURL:
			if($GLOBALS["user"]["data"]->rank == 9) { include($siteURL."/home.php"); }
			if($GLOBALS["user"]["data"]->rank == 10) { include($siteURL."/home.php"); }
			else { $URL->redirect(); }
			break;	
		#Log In and Out
		case "login":
			$URL->redirect();
			break;
		case "logout":
			if(isset($_COOKIE["rememberMe"])) 
			{ 
				$users->editUser($_SESSION[USER_ID_KEY], ["remember_token" => NULL]);
				setcookie("rememberMe", NULL, time() - 10, "/"); 
			}
			$users->logout();
			$URL->redirect();
			break;
		#File	
		case "file":
			include($siteURL."/files.php");
			break;
		#User Profile	
		case "profile":
			include($siteURL."/users/profile.php");
			break;
		#Customers
		case "customers":
			include($siteURL."/customers/list.php");
			break;	
		case "customer":
			include($siteURL."/customers/details.php");
			break;	
		#Services
		case "service-import":
			include($siteURL."/services/import.php");
			break;	
		case "service-events":
			include($siteURL."/services/events.php");
			break;
		case "service-events-answered":
			include($siteURL."/services/events-answered.php");
			break;	
		case "service-exports":
			include($siteURL."/services/exports.php");
			break;
		case "service-todo":
			include($siteURL."/services/todo.php");
			break;
		case "service-todo-home":
			include($siteURL."/services/todo-home.php");
			break;
		case "service-tracking":
			include($siteURL."/services/tracking.php");
			break;	
		#Newcar sellings
		case "new-car-sellings-import":
			include($siteURL."/new-car-sellings/import.php");
			break;	
		case "new-car-sellings-events":
			include($siteURL."/new-car-sellings/events.php");
			break;
		case "new-car-sellings-exports":
			include($siteURL."/new-car-sellings/exports.php");
			break;
		case "new-car-sellings-todo":
			include($siteURL."/new-car-sellings/todo.php");
			break;
		case "new-car-sellings-home":
			include($siteURL."/new-car-sellings/home.php");
			break;	
		case "new-car-sellings-log":
			include($siteURL."/new-car-sellings/log.php");
			break;		
		#Questionnaires	
		case "questionnaire-answers":
			include($siteURL."/questionnaires/answers.php");
			break;	
			
		case "mate-test":
			echo "<pre>";
			$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
			$services = new \App\Http\Controllers\ServiceController;
			$questionnaires = new \App\Http\Controllers\QuestionnaireController;
			// echo "<pre>"; print_r($questionnaires->exportAnswersIntoExcel([16, 2, 5, 4, 3, 8, 6, 7, 9, 10, 12, 11, 14, 13, 15]));
			
			/*$answers = $questionnaires->model->select("SELECT id FROM questionnaires_answers WHERE lowestValue IS NULL LIMIT 0, 5000");
			foreach($answers AS $i => $answerID)
			{
				$lowestValue = NULL;
				$answer = $questionnaires->getAnswer($answerID->id);
				// dd($answer);
				// echo $answerID->id."<br>";
				if(!empty($answer["questionsData"]))
				{
					foreach($answer["questionsData"] AS $question)
					{
						if(is_numeric($question["answer"]) AND ($lowestValue === NULL OR  $lowestValue >= $question["answer"])) { $lowestValue = $question["answer"]; }
					}
					if($lowestValue !== NULL) { $questionnaires->model->myUpdate("questionnaires_answers", ["lowestValue" => $lowestValue], $answerID->id); }
				}
			}
			echo $i;*/
			
			// $qID = 16;
			// $return = $questionnaires->exportAnswersIntoExcel_CreateWorksheet("/var/www/vhosts/infinitimagyarorszag.hu/crm.admin.gablini.hu/_questionnaire_answers_exports/20211011_Kerdoiv_export_6164121f06047.xlsx", $questionnaires->model->getQuestionnaire($qID));
			// print_r($return);
			
			
			// $services->getEventsForDailyReport("2020-11-19");
			
			// $services->statEventsCustom("2020-10-01 00:00:00", "2020-12-31 23:59:59", [], true);
			// $services->statEventsQuarterly();
			
			// $return = $services->tempInfinitiAnswers();
			// print_r($return);
			
			/*$i = 0;
			$qAnswers = $services->model->select("SELECT * FROM questionnaires_answers WHERE `date` >= '2021-07-27 00:00:00' AND questionnaireCode = 'serviceEvents2021All' AND user IS NOT NULL AND user > 0");
			foreach($qAnswers AS $qAnswer)
			{				
				$events = $services->model->select("SELECT * FROM services_events WHERE id = :id", ["id" => $qAnswer->foreignKey]);
				foreach($events AS $event)
				{
					if(!$event->adminTodoSuccess)
					{
						$i++;
						echo $event->id."<br>";
						
						$services->newEventStatusChange($event->id, 8, "");
						$services->editEvent($event->id, ["adminTodo" => 0, "adminTodoSuccess" => 1, "status" => 8]);
						
						$changes = $services->model->select("SELECT * FROM `services_events_statuses_changes` WHERE event = :event AND status = '8' ORDER BY id DESC", ["event" => $event->id]);
						foreach($changes AS $change)
						{
							$services->model->myUpdate("services_events_statuses_changes", ["date" => $qAnswer->date], $change->id);
							break;
						}
					}
				}
			}*/
			
			// echo $newcarSellings->__globalStat20211125()["stats"]["admins"];
			
			exit;
			break;
		case "cron-run":
			if(isset($_GET["command"]) AND !empty($_GET["command"]))
			{
				$run = \Illuminate\Support\Facades\Artisan::queue($_GET["command"]); # queue() works in background... Artisan::call() works inline
				echo "<pre>"; print_r($run);
			}
			exit;
			break;	
	}
}
#Without login -----------------------------------------------------------------------------------------------------------------------------------------------------------
else
{
	switch($routes[0])
	{
		case "":
			$VIEW["title"] = "Bejelentkezés";
			$VIEW["name"] = "without-login.login";
			break;
		case "login":
			include($siteURL."/users/login.php");
			break;
		case "forgot-password":	
			include($siteURL."/users/forgot-password.php");
			break;
		case "new-password":
			include($siteURL."/users/new-password.php");
			break;	
		default:
			$URL->redirect([], ["error" => "required", "url" => $URL->htaccess, "get" => $URL->getString]);
			break;
	}
}
?>
