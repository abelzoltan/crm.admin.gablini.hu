<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Requests;

class URLController extends Controller
{	
	public $path;
	public $web;
	public $htaccessOn;
	public $htaccess;
	public $routes;
	public $get;
	public $getString;
	public $currentURL;
	public $fullURL;

	public $siteID;
	
	#Constructor
	public function __construct($htaccessOn = true, $path = "", $web = "", $site = NULL)
	{
		#Pathes
		if(empty($path)) { $path = __DIR__."/"; }
		$this->path = $path;
		if(empty($web)) { $web = "//".$_SERVER["SERVER_NAME"]."/"; }
		$this->web = $web;

		#HTAccess
		$this->htaccessOn = $htaccessOn;

		#Site settings
		if(!empty($site))
		{
			$this->siteID = $site->data->id;
			$this->htaccess = (isset($GLOBALS["rootURL"])) ? $GLOBALS["rootURL"]->htaccess : NULL;
			$this->routes = (isset($GLOBALS["rootURL"])) ? $GLOBALS["rootURL"]->routes : [];
			if(!empty($site->data->baseName) AND !$site->data->baseNameAsDomain)
			{
				if(isset($GLOBALS["rootURL"]) AND ($this->web == $GLOBALS["rootURL"]->web.$this->htaccess OR $this->web == $GLOBALS["rootURL"]->web.$this->htaccess."/"))
				{
					$this->htaccess = $GLOBALS["rootURL"]->htaccess;
					$this->routes = $GLOBALS["rootURL"]->routes;
				}
				elseif(isset($GLOBALS["rootURL"]))
				{
					$this->htaccess = str_replace($site->baseName, "", $GLOBALS["rootURL"]->htaccess);
					array_shift($GLOBALS["rootURL"]->routes);
				}
			}
			elseif(isset($GLOBALS["rootURL"]))
			{
				$this->htaccess = $GLOBALS["rootURL"]->htaccess;
				$this->routes = $GLOBALS["rootURL"]->routes;
			}
		}

		#URL data
		$this->setURLdata();
	}
	
	public function setURLdata()
	{
		$this->get = $_GET;
		$this->getString = http_build_query($this->get);

		$currentURL = $this->web;
		if(!empty($this->htaccess)) { $currentURL .= $this->htaccess; }
		$this->currentURL = $currentURL;

		if(!empty($this->getString)) { $currentURL .= "?".$this->getString; }
		$this->fullURL = $currentURL;
	}
	
	public function getURLdata()
	{
		return [
			"path" => $this->path,
			"web" => $this->web,
			"htaccessOn" => $this->htaccessOn,
			"htaccess" => $this->htaccess,
			"routes" => $this->routes,
			"get" => $this->get,
			"getString" => $this->getString,
			"currentURL" => $this->currentURL,
			"fullURL" => $this->fullURL,
		];
	}

	public function link($url = [], $get = [], $andSign = "&", $path = NULL, $htaccess = NULL)
	{
		if($htaccess === NULL) { $htaccess = $this->htaccessOn; }
		if($path === NULL) { $path = $this->web; }

		#Beginning of URL
		$return = $path;
		if(mb_substr($return, -1, 1, "utf-8") != "/") { $return .= "/"; }

		#HTAccess is active (ON)
		if($htaccess)
		{
			if(!empty($url))
			{
				if(is_array($url)) { $return .= implode("/", $url); }
				else { $return .= $url; }
			}
			if(mb_substr($return, -1, 1, "utf-8") == "/") { $return = rtrim($return, "/"); }
			if(!empty($get)) { $return .= "?".http_build_query($get, "", $andSign); }
		}
		#HTAccess is inactive (OFF)
		else
		{
			$return .= "index.php";
			if(!empty($url) OR !empty($get)) { $return .= "?"; }

			if(!empty($url))
			{
				if(is_array($url)) { $urlString = implode("/", $url); }
				else { $urlString = $url; }
				$return .= "htaccess-path=".$urlString;
			}

			if(!empty($get))
			{
				if(!empty($url)) { $return .= $andSign; }
				$return .= http_build_query($get, "", $andSign);
			}
		}
		
		#Return URL string
		return $return;
	}
	
	public function redirect($url = [], $get = [], $andSign = "&", $path = NULL, $htacess = NULL, $exit = true)
	{	
		$link = $this->link($url, $get, $andSign, $path, $htacess);
		header("Location: ".$link);
		if($exit) { exit; }
	}

	public function linkHeader($url = [], $get = [], $andSign = "&", $path = NULL, $htacess = NULL, $exit = true)
	{
		$this->redirect($url, $get, $andSign, $path, $htacess, $exit);
	}

	public function header($link, $exit = true)
	{
		header("Location: ".$link);
		if($exit) { exit; }
	}
}
