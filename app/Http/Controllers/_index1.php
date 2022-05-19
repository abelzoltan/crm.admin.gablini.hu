<?php
use App\Http\Controllers\URLController;

#Basic settings 
ini_set("display_errors", 0);
ini_set("log_errors", 0);
error_reporting(E_ALL ^ E_NOTICE);
session_set_cookie_params(14400, "/");
session_start();	
header("Content-Type: text/html; charset=utf-8");
setlocale(LC_ALL, "hu_HU.UTF-8");
date_default_timezone_set("Europe/Budapest");

#Default database
define("MYSQL_HOST", env("DB_HOST"));
define("MYSQL_PORT", env("DB_PORT"));
define("MYSQL_DB", env("DB_DATABASE"));
define("MYSQL_USERNAME", env("DB_USERNAME"));
define("MYSQL_PASSWORD", env("DB_PASSWORD"));
define("MYSQL_PREFIX", "");

define("MYSQL_CONNECTION_NAME", env("DB_CONNECTION"));
define("MYSQL_CONNECTION_NAME_G", env("DB_CONNECTION_G"));
define("MYSQL_CONNECTION_NAME_G2", env("DB_CONNECTION_G2"));

#Pathes of the root directory
define("PATH_ROOT", base_path()."/");
define("PATH_ROOT_WEB", env("PATH_ROOT_WEB"));

#Directories
define("DIR_CLASSES", __DIR__."/");
define("DIR_CLASSES_WEB", PATH_ROOT_WEB);
define("DIR_CONTROLLERS", __DIR__."/");
define("DIR_CONTROLLERS_WEB", PATH_ROOT_WEB);
define("DIR_MODELS", PATH_ROOT."app/");
define("DIR_MODELS_WEB", PATH_ROOT_WEB);
define("DIR_PUBLIC", PATH_ROOT."public/");
define("DIR_PUBLIC_WEB", PATH_ROOT_WEB);
define("DIR_ROUTES", PATH_ROOT."routes/");
define("DIR_ROUTES_WEB", PATH_ROOT_WEB."routes/");
define("DIR_VIEWS", PATH_ROOT."resources/views/");
define("DIR_VIEWS_WEB", PATH_ROOT_WEB."resources/views/");

#Email settings [optional]
define("EMAIL_FROM_EMAIL", "noreply@gablini.hu");
define("EMAIL_FROM_NAME", "Gablini Kft.");
define("EMAIL_REPLYTO_EMAIL", NULL);
define("EMAIL_REPLYTO_NAME", NULL);
if(env("MAIL_DRIVER") == "smtp") { $smtp = true; $smtpAuth = true; }
else { $smtp = false; $smtpAuth = false; }
define("SMTP_ON", $smtp);
define("SMTP_AUTH", $smtpAuth);
define("SMTP_SECURE", env("MAIL_ENCRYPTION"));
define("SMTP_HOST", env("MAIL_HOST"));
define("SMTP_PORT", env("MAIL_PORT"));
define("SMTP_USERNAME", env("MAIL_USERNAME"));
define("SMTP_PASSWORD", env("MAIL_PASSWORD"));

#Include functions
// how?

#Path and View functions
function publicPath($path = "", $web = true)
{
	if($web) { $return = DIR_PUBLIC_WEB; }
	else { $return = DIR_PUBLIC; }
	
	$return .= $path;
	return $return;
}

function viewExists($path) //change!
{
	if(empty($path)) { $return = false; }
	else { $return = file_exists(DIR_VIEWS.$path.".php"); }
	return $return;
}

function setLink($string, $path = NULL)
{
	$link = mb_convert_case($string, MB_CASE_LOWER, "utf-8");
	if($path === NULL) { $path = PATH_WEB; }
	
	$return = $link;
	if(mb_substr($link, 0, 7, "utf-8") == "http://") {  }
	elseif(mb_substr($link, 0, 8, "utf-8") == "https://") {  }
	elseif(mb_substr($link, 0, 2, "utf-8") == "//") {  }
	elseif(mb_substr($link, 0, 7, "utf-8") == "ftp://") {  }
	elseif(mb_substr($link, 0, 7, "utf-8") == "ftps://") {  }
	elseif(mb_substr($link, 0, 1, "utf-8") == "#") { $return = $link; }
	else { $return = $path.$link; }
	
	return $return;
}

#HTAccess and Root URL
$GLOBALS["rootURL"] = $rootURL = new URLController(true, PATH_ROOT, PATH_ROOT_WEB, NULL);
?>