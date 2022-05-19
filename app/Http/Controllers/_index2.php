<?php 
use App\Http\Controllers\MylogController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\URLController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
include("MobileDetect/Mobile_Detect.php");

#CDN
define("CDN", false);
if(CDN)
{
	define("CDN_PATH", base_path()."/../cdn.gablini.hu/");
	define("CDN_PATH_WEB", env("CDN_PATH_WEB"));
}

#FTP to progress export
define("FTP_USER", env("FTP_USER"));
define("FTP_PASSWORD", env("FTP_PASSWORD"));


#Site data
$GLOBALS["site"] = $site = new SiteController();
define("SITE", $site->data->id);

#Path
define("PATH", PATH_ROOT);
define("PATH_WEB", "http://".$_SERVER["SERVER_NAME"]."/");
define("GENTELELLA_DIR", PATH_WEB."gentelella/");

define("PATH_QUESTIONNAIRE_WEB", env("PATH_QUESTIONNAIRE_WEB"));
define("PATH_CRM_WEB", env("PATH_CRM_WEB"));

#URL
$GLOBALS["URL"] = $URL = new URLController(true, PATH, PATH_WEB, $site);
define("SESSION_PREFIX", $site->data->sessionPrefix);

#Visited Pages
define("VISITED_PAGES", SESSION_PREFIX."siteVisitedPages");
if(!isset($_SESSION[VISITED_PAGES])){ $_SESSION[VISITED_PAGES] = []; }
$_SESSION[VISITED_PAGES][] = [
	"date" => date("Y-m-d H:i:s"),
	"siteID" => $site->data->id,
	"url" => $URL,
];

#Mobile Detect (device type)
$mobileDetect = new Mobile_Detect();
if($mobileDetect->isMobile()) { $deviceType = "mobile"; }
elseif($mobileDetect->isTablet()) { $deviceType = "tablet"; }
else { $deviceType = "pc"; }
define("DEVICE_TYPE", $deviceType);

#User [optional]
define("USER_LOGGED_IN", SESSION_PREFIX."loggedIn");
define("USER_ID_KEY", SESSION_PREFIX."userID");
$GLOBALS["users"] = $users = new UserController();
if($_SESSION[USER_LOGGED_IN]) 
{ 
	$users->activity(); 
	define("USERID", $_SESSION[USER_ID_KEY]);
	$GLOBALS["user"] = $users->getUser(USERID); 
	$GLOBALS["userID"] = USERID;
}
else { $_SESSION[USER_LOGGED_IN] = false; }

#Log
$GLOBALS["log"] = $log = new MylogController();
$log->log("pageload", ["text1" => $log->json($URL->routes), "text2" => $log->json($_GET)]);

#Include routing

include("Routes/".$site->data->url.".php");
?>
