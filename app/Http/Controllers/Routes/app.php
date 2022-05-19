<?php
#Basic settings
$this->MAIN = ["URL" => $GLOBALS["URL"]->getURLdata()];
$this->MAIN["SITE"] = $site->data;
$VIEW["vars"] = [];
$routes = $GLOBALS["URL"]->routes;
$siteURL = $site->data->url;

define("CDN_APP", true);
if(CDN_APP)
{
	define("CDN_APP_PATH", base_path()."/../cdn.gablini.hu/");
	define("CDN_APP_PATH_WEB", "https://cdn.gablini.hu/");
}

#Meta
$VIEW["titlePrefix"] = $site->data->titlePrefix;
$VIEW["titleSuffix"] = $site->data->titleSuffix;
$VIEW["title"] = "Gablini App";
$VIEW["name"] = implode("-", $routes); 
$VIEW["meta"] = [
	"keywords" => "",
	"description" => "Gablini App",
	"og:title" => $VIEW["title"],
	"og:image" => URL::asset("pics/logo-facebook.png"),
	"og:description" => "Gablini App",
	"og:site_name" => $site->data->name,
	"og:type" => "website",
	"og:url" => $GLOBALS["URL"]->currentURL,
];

#Check GET login
if(isset($_GET["token"]) AND !empty($_GET["token"]))
{
	$app = new \App\Http\Controllers\AppController;
	$json = $app->login();
	if(!$app->doLogin($json)) { echo $json; }
}

#With login
if($_SESSION[SESSION_PREFIX."appUserLoggedIn"])
{ 
	#App user, Customer
	$app = new \App\Http\Controllers\AppController;
	$customers = new \App\Http\Controllers\CustomerController;
	
	$appUser = $GLOBALS["APPUSER"] = $app->getUserByToken($_SESSION[SESSION_PREFIX."appUserDatas"]["token"]);
	if($appUser === false) 
	{
		$app->logout();
		$URL->redirect(["login"]);
	}
	
	$customer = $GLOBALS["CUSTOMER"] = $customers->getCustomer($appUser["data"]->customer, false); // info@gablini.hu
	
	#Navigation - header
	$VIEW["vars"]["navHeader"] = [
		"profile" => "Adatlapom", // $appUser["name"]
		"logout" => "Kijelentkezés",
	];
	
	#Navigation - main
	$VIEW["vars"]["navMain"] = [
		"loyalty-card" => "Hűségkártya",
		"points" => "Pontjaim",
		"points-use" => "Pontbeváltás",
		"cars" => "Autóim",
		"promotions" => "Aktuális akciók",
		"help" => "SOS Help",
		"service" => "Szervizlátogatási kérelem",
		"contact" => "Kapcsolat",
		"messages" => "Üzenetek",
	];
	
	#Routes
	switch($routes[0])
	{	
		#Homepage
		case "":
			include($siteURL."/home.php");
			break;	
		case $site->data->homepageURL:
			$URL->redirect();
			break;	
		#Log In and Out
		case "login":
			$URL->redirect();
			break;
		case "logout":
			$app->logout();
			$URL->redirect(["login"]);
			break;
		#Menus	
		case "profile":
		case "points":
		case "points-use":
		case "cars":
		case "promotions":
		case "help":
		case "service":
		case "contact":
		case "messages":
		case "loyalty-card":
			include($siteURL."/".$routes[0].".php");
			break;
		#Default
		default:
			$URL->redirect();
			break;
	}
}
#Without login -----------------------------------------------------------------------------------------------------------------------------------------------------------
else
{
	switch($routes[0])
	{
		/*case "":
			$VIEW["title"] = "Bejelentkezés";
			$VIEW["name"] = "without-login.login";
			break;*/
		#Login
		case "login":
			#Token or Email+Invoice
			$app = new \App\Http\Controllers\AppController;
			$json = $app->login();
			$datas = $app->jsonDecode($json);
			if($app->doLogin($json)) { $URL->redirect([""], ["token" => $datas["token"]]); }
			else { echo $json; }
			exit;
			break;	
		default:
			$URL->redirect(["login"]);
			break;
	}
}
?>