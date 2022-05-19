<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Requests;
use Response;
use Mail;
use View;

include("_index1.php");
class BaseController extends Controller
{
	public $modelName;
	public $model;
	
	public $MAIN;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Base";
		$this->model = new \App\Base($connectionData);
	}
	
	#Routing (pages)
	public function routes($url1 = "", $url2 = "", $url3 = "", $url4 = "", $url5 = "", $url6 = "", $url7 = "", $url8 = "", $url9 = "", $url10 = "", $url11 = "", $url12 = "", $url13 = "", $url14 = "", $url15 = "", $url16 = "", $url17 = "", $url18 = "", $url19 = "", $url20 = "")
	{
		#URL
		$baseRoutes = [$url1, $url2, $url3, $url4, $url5, $url6, $url7, $url8, $url9, $url10, $url11, $url12, $url13, $url14, $url15, $url16, $url17, $url18, $url19, $url20];
		$routes = [];
		foreach($baseRoutes AS $baseRoute) 
		{ 
			if(!empty($baseRoute)) { $routes[] = $baseRoute; } 
			else { break; }
		}
		if(empty($routes)) { $routes[] = ""; }
		
		if(isset($GLOBALS["rootURL"]))
		{
			$GLOBALS["rootURL"]->routes = $routes;
			$GLOBALS["rootURL"]->htaccess = implode("/", $routes);
			$GLOBALS["rootURL"]->setURLdata();
			$rootURL = $GLOBALS["rootURL"];
			$GLOBALS["htaccessPath"] = $GLOBALS["rootURL"]->htaccess;
		}
		
		$VIEW = [];
		$VIEW["title"] = "";
		$VIEW["name"] = "404"; 
		$VIEW["vars"] = [];

		include("_index2.php");

		if(!isset($VIEW["nameDir"])) { $VIEW["nameDir"] = $site->data->url; }
		if(!empty($VIEW["nameDir"])) { $VIEW["name"] = $VIEW["nameDir"].".".$VIEW["name"]; }



		if(!View::exists($VIEW["name"])) { $VIEW["name"] = $site->data->url."."."404";}
		
		$response = Response::view($VIEW["name"], ["VIEW" => $VIEW, "MAIN" => $this->MAIN]);
		if(isset($VIEW["responseHeader"]) AND $VIEW["responseHeader"] == "xml") { $response->header("Content-type", "text/xml"); }
		return $response;
	}
	
	#HTML variable change
	public function changeHtmlVariable($text, $params, $startSign = "{", $endSign = "}")
	{
		$from = [];
		$to = [];
		foreach($params AS $key => $value)
		{
			$from[] = $startSign.$key.$endSign;
			$to[] = $value;
		}
		
		return str_replace($from, $to, $text);
	}
	
	#Unique field set (f.e: token, key, ...)
	public function setUniqueURL($table, $key, $value, $after = "", $params = [], $id = 0, $primaryKey = "id")
	{
		$count = 1;
		while(true)
		{
			$url = self::generateUrl($value, $after, [], "-");
			$rows = $this->model->checkUniqueField($table, $key, $url, $params, $id, $primaryKey);
			
			if(count($rows) > 0)
			{
				$count++;
				$after = $count;
			}
			else { break; }
		}
		
		return $url;
	}
	
	#Unique field set (f.e: token, key, ...)
	public function setUniqueToken($length, $allowToUse = ["numbers", "smallLetters", "capitalLetters"], $table, $key, $params = [], $id = 0, $primaryKey = "id")
	{
		while(true)
		{
			$token = self::random($length, [], $allowToUse);			
			$rows = $this->model->checkUniqueField($table, $key, $token, $params, $id, $primaryKey);
			if(count($rows) == 0) { break; }
		}
		
		return $token;
	}
	
	public function setUniqueTokenDifferentChars($length, $allowToUse = ["numbers", "smallLetters", "capitalLetters"], $table, $key, $params = [], $id = 0, $primaryKey = "id")
	{
		while(true)
		{
			$token = self::randomDifferentChars($length, [], $allowToUse);		
			$rows = $this->model->checkUniqueField($table, $key, $token, $params, $id, $primaryKey);
			if(count($rows) == 0) { break; }
		}
		
		return $token;
	}
	
	#Generate URL	
	public static function generateURL($string, $after = NULL, $replace = [], $delimiter = "-")
	{	
		if(!empty($replace)) { $string = str_replace((array)$replace, " ", $string); }
		
		$return = iconv("utf-8", "ASCII//TRANSLIT", $string);
		if(empty($return)) { $return = $string; }
		$return = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", "", $return);
		$return = strtolower(trim($return, $delimiter));
		$return = preg_replace("/[\/_|+ -]+/", $delimiter, $return);
		
		if(!empty($after)) { $return .= $delimiter.$after; }

		return $return;
	}
	
	#Generate random string
	public static function random($length = 10, $extraAllowToUse = [], $basicAllowToUse = ["numbers", "smallLetters", "capitalLetters"]) 
	{
		#Arrays
		$numbers = range(0, 9);
		$smallLetters = range("a", "z");
		$capitalLetters = range("A", "Z");
		
		#Generate array with chars to be used
		$chars = array();
		foreach($extraAllowToUse AS $item) { $chars = array_merge($chars, $item); }
		foreach($basicAllowToUse AS $item) { $chars = array_merge($chars, $$item); }
		
		#Generate and return
		$return = "";
		for($i = 0; $i < $length; $i++) { $return .= $chars[mt_rand(0, count($chars) - 1)]; }
		return $return;
	}
	
	public static function randomDifferentChars($length = 10, $extraAllowToUse = [], $basicAllowToUse = ["numbers", "smallLetters", "capitalLetters"]) 
	{
		#Arrays
		$numbers = range(0, 9);
		$smallLetters = range("a", "z");
		$capitalLetters = range("A", "Z");
		
		#Generate array with chars to be used
		$chars = array();
		foreach($extraAllowToUse AS $item) { $chars = array_merge($chars, $item); }
		foreach($basicAllowToUse AS $item) { $chars = array_merge($chars, $$item); }
		
		#Generate and return
		$return = "";
		for($i = 0; $i < $length; $i++) 
		{ 
			$charKey = mt_rand(0, count($chars) - 1);
			$return .= $chars[$charKey]; 
			unset($chars[$charKey]);
			$chars = array_values($chars);
		}
		return $return;
	}
	
	#String max length
	public function strMax($text, $maxLength = 100, $after = "[...]", $anyWhere = 1)
	{
		$length = strlen($text);
		$afterLength = strlen($after);
		$maxOutLength = $maxLength - $afterLength;
		
		if($length <= $maxOutLength) { $string = $text; }
		else
		{
			if($anyWhere == 1) { $string = mb_substr($text, 0, $maxOutLength, "utf-8")." ".$after; }
			else { /* ONLY on spaces */ }
		}
		return $string;
	}
}
