<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Hex;

class HexController extends BaseController
{	
	public $model;
	public $priceText = " Ft";
	public $priceNetText = " + Áfa";
	public $priceEuroText = "&#8364;";
	public $priceEuroNetText = " + Áfa";
	public $datasList;
	public $datasDetailsMain;
	public $datasDetailsTexts;
	public $datasDetailsFinance;
	public $noImgLink = "pics/hex-no-img.jpg";
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Hex";
		$this->model = new \App\Hex($connectionData);
		
		$this->noImgLink = (defined("PATH_WEB")) ? PATH_WEB.$this->noImgLink : env("PATH_ROOT_WEB").$this->noImgLink;
		
		$this->datasList = ["hengerurtartalom", "uzemanyag", "futottkm", "evjarat"];
		$this->datasDetailsPrice = ["alapar", "ar", "akciosar", "ar_eur", "eredetiar", "koltsegek", "vetelar_teljes"];
		$this->datasDetailsMain = ["evjarat", "uzemanyag", "hengerurtartalom", "teljesitmeny", "futottkm", "szin"];
		$this->datasDetailsTexts = ["felszereltseg", "szeria_felszereltseg", "extra_felszereltseg", "leiras", "megtekintheto", "cim", "telefonszam"];
		$this->datasDetailsFinance = ["finanszirozas", "finanszirozas_tipusa_cascoval", "finanszirozas_tipusa_casco_nelkul", "kezdoreszlet", "kezdoreszlet_casco_nelkul", "havireszlet", "havireszlet_casco_nelkul", "futamido", "futamido_casco_nelkul"];
	}
	
	#Get car
	public function getCar($id, $allDatas = true)
	{
		$car = $this->model->getCar($id);
		if(!empty($car) AND isset($car->id) AND !empty($car->id))
		{
			#Basic datas
			$return = [];
			$return["car"] = $car;
			$return["type"] = $this->model->getType($car->type);
			$return["datas"] = $this->getDatasByCar($car->id);
			$return["category"] = $car->category;
			$return["name"] = $car->brand." ".$car->name;
			$return["nameURL"] = str_replace("-".$car->code, "", $car->url);
			$return["url"] = $car->url;
			$return["url2"] = $return["type"]->urlOnSite."/".$return["url"];
			$return["fullURL"] = (defined("PATH_WEB")) ? PATH_WEB.$return["url2"] : env("PATH_ROOT_WEB").$return["url2"];
			$return["code"] = $car->code;
			
			#Price
			$prices = $this->getPricesForCar($return["datas"]);
			foreach($prices AS $priceKey => $price) { $return[$priceKey] = $price; }
			
			#Pics
			$return["hasPic"] = true;
			$return["pics"] = $this->getPicsByCar($car->id);
			
			if(!empty($return["pics"][0]["link"])) { $return["pic"] = $return["pics"][0]["link"]; }
			elseif(!empty($return["pics"][0]["linkWatermarked"])) { $return["pic"] = $return["pics"][0]["linkWatermarked"]; }
			else 
			{ 
				$return["pic"] = $this->noImgLink; 
				$return["hasPic"] = false;
			}
			
			if($return["hasPic"] AND !empty($return["pics"][0]["linkBig"])) { $return["picDetails"] = $return["pics"][0]["linkBig"]; }
			else { $return["picDetails"] = $return["pic"]; }
			
			#Short text
			$return["facilities"] = "";
			$return["description"] = "";
			$return["shortTextData"] = [];
			$return["shortText"] = "";
			$return["shortTextNL"] = "";
			$return["shortTextComma"] = "";
			
			if(isset($return["datas"]["felszereltseg"]) AND !empty($return["datas"]["felszereltseg"]->value)) { $return["shortTextData"]["facilities"] = $return["facilities"] = $return["datas"]["felszereltseg"]->value; }
			if(isset($return["datas"]["leiras"]) AND !empty($return["datas"]["leiras"]->value)) { $return["shortTextData"]["description"] = $return["description"] = $return["datas"]["leiras"]->value; }
			
			if(!empty($return["shortTextData"]))
			{
				$return["shortText"] = implode("<br>", $return["shortTextData"]);
				$return["shortTextNL"] = implode("\r\n", $return["shortTextData"]);
				$return["shortTextComma"] = implode(", ", $return["shortTextData"]);
			}
			
			#List datas
			$return["listDatas"] = [];
			foreach($this->datasList AS $key)
			{
				if(isset($return["datas"][$key]))
				{
					$return["listDatas"][$key] = [
						"key" => $key,
						"name" => $return["datas"][$key]->name,
						"value" => $return["datas"][$key]->value,
					];
				}
			}
			
			#Details
			if($allDatas)
			{
				#Gallery
				$return["gallery"] = [];
				$i = 1;
				foreach($return["pics"] AS $picKey => $pic)
				{
					if(!empty($pic["linkBig"])) { $link = $pic["linkBig"]; }
					else { $link = $pic["link"]; }
					
					$return["gallery"][$i] = (object)[
						"data" => $pic,
						"link" => $link,
						"href" => "",
						"name" => $return["name"]." - ".$i.". kép",
					];
					$i++;
				}
				
				$keysOut = [];
				$return["details"] = [
					"prices" => [
						"name" => "Ár",
						"datas" => [],
					],
					"main" => [
						"name" => "Kiemelt adatok",
						"datas" => [],
					],
					"texts" => [
						"name" => "Részletek",
						"datas" => [],
					],
					"finance" => [
						"name" => "Finanszírozás, Hitel",
						"datas" => [],
					],
					"others" => [
						"name" => "További adatok",
						"datas" => [],
					],
				];
				
				#Groups
				/*foreach($return["prices"] AS $key => $data)
				{
					if(!empty($data["formatted"]))
					{
						$return["details"]["prices"]["datas"][$key] = [
							"key" => $data["originalKey"],
							"name" => $data["name"],
							"value" => $data["formatted"],
						];
						$keysOut[] = $data["originalKey"];
					}
				}*/
				foreach($this->datasDetailsPrice AS $key)
				{
					if(isset($return["datas"][$key]))
					{
						$return["details"]["prices"]["datas"][$key] = [
							"key" => $key,
							"name" => $return["datas"][$key]->name,
							"value" => $return["datas"][$key]->value,
						];
						$keysOut[] = $key;
					}
				}
				foreach($this->datasDetailsMain AS $key)
				{
					if(isset($return["datas"][$key]))
					{
						$return["details"]["main"]["datas"][$key] = [
							"key" => $key,
							"name" => $return["datas"][$key]->name,
							"value" => $return["datas"][$key]->value,
						];
						$keysOut[] = $key;
					}
				}
				foreach($this->datasDetailsTexts AS $key)
				{
					if(isset($return["datas"][$key]))
					{
						$return["details"]["texts"]["datas"][$key] = [
							"key" => $key,
							"name" => $return["datas"][$key]->name,
							"value" => $return["datas"][$key]->value,
						];
						$keysOut[] = $key;
					}
				}
				foreach($this->datasDetailsFinance AS $key)
				{
					if(isset($return["datas"][$key]))
					{
						$return["details"]["finance"]["datas"][$key] = [
							"key" => $key,
							"name" => $return["datas"][$key]->name,
							"value" => $return["datas"][$key]->value,
						];
						$keysOut[] = $key;
					}
				}
				#Others
				foreach($return["datas"] AS $key => $data)
				{
					if(!in_array($key, $keysOut))
					{
						$return["details"]["others"]["datas"][$key] = [
							"key" => $key,
							"name" => $return["datas"][$key]->name,
							"value" => $return["datas"][$key]->value,
						];
					}
				}
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getCarByURL($url)
	{
		$id = $this->model->getCarByURL($url, "id");
		return $this->getCar($id);
	}
	
	public function getCarByCode($code)
	{
		$id = $this->model->getCarByCode($code, "id");
		return $this->getCar($id);
	}
	
	#Get cars
	public function getCarsByType($typeURL, $search = [], $deleted = 0, $key = "id", $orderNumber = "price, brand, name")
	{
		$type = $this->model->getTypeByURL($typeURL);
		$rows = $this->model->getCarsByType($type->id, $search, $deleted, $orderNumber);
		$return = [];
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$car = $this->getCar($row->id, false); 
			if(isset($car["price"]) AND !empty($car["price"])) 
			{ 
				$okay = $this->search($car, $search);
				if($okay) { $return[$keyHere] = $car; }
			}
		}
		return $return;
	}
	
	#Get cars by multiple types
	public function getCarsByTypes($types, $search = [], $deleted = 0, $key = "id", $orderNumber = "price, brand, name")
	{
		$rows = $this->model->getCarsByTypes($types, $search, $deleted, $orderNumber);
		
		$return = [];
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$car = $this->getCar($row->id, false); 
			if(isset($car["price"]) AND !empty($car["price"])) 
			{ 
				$okay = $this->search($car, $search);
				if($okay) { $return[$keyHere] = $car; }
			}
		}
		return $return;
	}
	
	#Get prices for car
	public function getPricesForCar($datas)
	{
		$return["priceOriginal"] = NULL;
		$return["price"] = NULL;
		$return["priceUnFormatted"] = NULL;
		$return["priceIsNet"] = NULL;
		$return["priceGrossUnFormatted"] = NULL;
		$return["prices"] = [
			"basic" => [],
			"price" => [],
			"sale" => [],
			"euro" => [],
		];
		foreach($return["prices"] AS $key => $data)
		{
			switch($key)
			{
				case "basic": $keyOriginal = "alapar"; break;
				case "price": $keyOriginal = "ar"; break;
				case "sale": $keyOriginal = "akciosar"; break;
				case "euro": $keyOriginal = "ar_eur"; break;
				default: break(2);
			}
			#Store
			if(isset($datas[$keyOriginal]))
			{
				if($key == "sale")
				{
					$taxField = "data2";
					$grossField = "data3";
				}
				else
				{
					$taxField = "data1";
					$grossField = "data2";
				}
				
				if(!empty($datas[$keyOriginal]->$grossField)) 
				{ 
					$hasNet = true;
					$gross = $datas[$keyOriginal]->$grossField;
				}
				else 
				{ 
					$hasNet = false;
					$gross = $datas[$keyOriginal]->baseValue;
				}
				$return["prices"][$key]["originalKey"] = $keyOriginal;
				$return["prices"][$key]["name"] = $datas[$keyOriginal]->name;
				$return["prices"][$key]["price"] = $datas[$keyOriginal]->baseValue;
				$return["prices"][$key]["hasNet"] = $hasNet;
				$return["prices"][$key]["gross"] = $gross;
				if($key == "euro")
				{
					$return["prices"][$key]["formatted"] = $this->priceFormatEuro($return["prices"][$key]["price"], $hasNet, $datas[$keyOriginal]->$taxField);
					$return["prices"][$key]["grossFormatted"] = $this->priceFormatEuro($gross, false);
				}
				else
				{
					$return["prices"][$key]["formatted"] = $this->priceFormat($return["prices"][$key]["price"], $hasNet, $datas[$keyOriginal]->$taxField);
					$return["prices"][$key]["grossFormatted"] = $this->priceFormat($gross, false);
				}
			}
		}
		if(!empty($return["prices"]["basic"])) { $return["priceOriginal"] = $return["prices"]["basic"]["formatted"]; }
		if(!empty($return["prices"]["price"])) 
		{ 
			if(!empty($return["prices"]["sale"])) 
			{ 
				$return["priceOriginal"] = $return["prices"]["price"]["formatted"]; 
				$return["price"] = $return["prices"]["sale"]["formatted"]; 
				$return["priceIsNet"] = $return["prices"]["sale"]["hasNet"]; 
				$return["priceUnFormatted"] = $return["prices"]["sale"]["price"]; 
				$return["priceGrossUnFormatted"] = $return["prices"]["sale"]["gross"]; 
			}
			else 
			{ 
				$return["price"] = $return["prices"]["price"]["formatted"];  
				$return["priceIsNet"] = $return["prices"]["price"]["hasNet"];
				$return["priceUnFormatted"] = $return["prices"]["price"]["price"];
				$return["priceGrossUnFormatted"] = $return["prices"]["price"]["gross"];
			}
			
			if(empty($return["priceGrossUnFormatted"]) AND !$return["priceIsNet"]) { $return["priceGrossUnFormatted"] = $return["price"]; }
		}
		
		return $return;
	}
	
	#Price format
	public function priceFormat($price, $hasNet, $tax = "")
	{
		$intCheck = round($price);
		if($price == $intCheck) { $out = number_format($price, 0, ",", " "); }
		else { $out = number_format($price, 0, ",", " "); }
		$out .= $this->priceText;
		
		if($hasNet) { $out .= str_replace("{TAX}", $tax, $this->priceNetText); }
		
		return $out;
	}
	
	public function priceFormatEuro($price, $hasNet, $tax = "")
	{
		$intCheck = round($price);
		if($price == $intCheck) { $out = number_format($price, 0, ",", " "); }
		else { $out = number_format($price, 0, ",", " "); }
		$out = $this->priceEuroText.$out;
		
		if($hasNet) { $out .= str_replace("{TAX}", $tax, $this->priceEuroNetText); }
		
		return $out;
	}
	
	#Get datas
	public function getDatasByCar($car, $deleted = 0, $key = "url")
	{
		$return = [];
		$rows = $this->model->getDatasByCar($car, $deleted , "id");
		foreach($rows AS $i => $row)
		{ 
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$return[$keyHere] = $row; 
		}
		return $return;
	}
	
	#Get pictures
	public function getPicsByCar($car, $deleted = 0, $key = "")
	{
		$return = [];
		
		$replaceFrom = ["http://", "https://"];
		$replaceTo = ["//", "//"];
		
		$rows = $this->model->getPicsByCar($car, $deleted , "id");
		foreach($rows AS $i => $row)
		{ 
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$return[$keyHere] = [
				"row" => $row,
				"link" => str_replace($replaceFrom, $replaceTo, $row->basic),
				"linkWatermarked" => "",
				"linkSmall" => str_replace($replaceFrom, $replaceTo, $row->small),
				"linkMedium" => str_replace($replaceFrom, $replaceTo, $row->medium),
				"linkBig" => str_replace($replaceFrom, $replaceTo, $row->big),
			];
			
			$return[$keyHere]["linkWatermarked"] = str_replace(
				["egerut.hasznaltauto.hu", "t."],
				["hasznaltauto.medija.hu", "."],
				$return[$keyHere]["linkSmall"]
			);
		}
		return $return;
	}
	
	#Get types
	public function getTypes($deleted = 0, $key = "url", $orderNumber = "id")
	{
		$return = [];
		$rows = $this->model->getTypes($deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$return[$keyHere] = $row;
		}
		return $return;
	}
	
	#Get prices for search
	public function getPricesForSearch($types, $orderNumber = "price")
	{
		#Get prices
		$prices = [];
		$rows = $this->model->getPricesForSearch($types, $orderNumber);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) { $prices[] = $row->price; }
		}
		asort($prices, SORT_NUMERIC);
		
		#Set values
		$return = [];
		// $return[$prices[0]] = $this->priceFormat($prices[0], false);
		
		$lastPrice = end($prices);
		$step = 100000;
		$number = $prices[0] - (2 * $step);
		while(true)
		{
			$number = ceil(($number + $step) / $step) * $step;

			$return[$number] = $this->priceFormat($number, false);
			if($number > $lastPrice) { break; }
		}
		
		#Return
		return $return;
	}
	
	#Get brands for search
	public function getBrandsForSearch($types, $orderNumber = "brand")
	{
		$return = [];
		$rows = $this->model->getBrandsForSearch($types, $orderNumber);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) { $return[] = $row->brand; }
		}
		return $return;
	}
	
	#Get years for search
	public function getYearsForSearch($types)
	{
		$return = [];
		$rows = $this->model->getYearsForSearch($types);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				$yearDate = explode("/", $row->value);
				if(!in_array($yearDate[0], $return)) { $return[] = $yearDate[0]; }
			}
		}
		return $return;
	}
	
	#Get sorting values for search
	public function getSortingValuesSearch()
	{
		$return = [
			"olcso" => [
				"name" => "Legolcsóbbak",
				"order" => "price, brand, name",
			],
			"draga" => [
				"name" => "Legdrágábbak",
				"order" => "price DESC, brand, name",
			],
			"abc" => [
				"name" => "ABC sorrend [A-Z]",
				"order" => "brand, name",
			],
			"friss" => [
				"name" => "Legfrissebbek",
				"order" => "id DESC",
			],
		];
		return $return;
	}
	
	#Search
	public function search($car, $search)
	{
		$return = true;
		if(!empty($search))
		{
			if(isset($search["yearFrom"]) AND !empty($search["yearFrom"])) 
			{
				if(isset($car["datas"]["evjarat"]) AND !empty($car["datas"]["evjarat"]->value)) 
				{
					$yearData = explode("/", $car["datas"]["evjarat"]->value);
					if($yearData[0] < $search["yearFrom"]) { $return = false; }
				}
				else { $return = false; }
			}
			if(isset($search["yearTo"]) AND !empty($search["yearTo"])) 
			{
				if(isset($car["datas"]["evjarat"]) AND !empty($car["datas"]["evjarat"]->value)) 
				{
					$yearData = explode("/", $car["datas"]["evjarat"]->value);
					if($yearData[0] > $search["yearTo"]) { $return = false; }
				}
				else { $return = false; }
			}
		}
		
		return $return;
	}
	
	#HEX: Export from hasznaltauto.hu
	public function export($exportName)
	{
		#Settings ----------------------------------------------------------------------------------------------------------------------------------------------------
		#XML verzió
			$xmlVersion = "1.0";
		#Helyi cache állomány elérési útja
			$localCacheFile = "cache/hasznaltauto_cache.temp";
		#Gyorsítótár elévülési ideje percekben megadva
			$localCacheMinutes = 10;
		#Gyorsítótár letiltása - NEM AJÁNLOTT
			$disableLocalCache = false;
		#Return
			$return = [
				"hex" => [
					"exportName" => $exportName,
					"xmlVersion" => $xmlVersion,
					"localCacheFile" => $localCacheFile,
					"localCacheMinutes" => $localCacheMinutes,
					"disableLocalCache" => $disableLocalCache,
				],
				"exportLinks" => [
					"xml" => NULL,
					"xsd" => NULL,
				],
				"getXMLMode" => NULL,
				"errors" => [],
			];

		#Process ---------------------------------------------------------------------------------------------------------------------------------------------------------
		if ($disableLocalCache OR !is_file($localCacheFile) OR filemtime($localCacheFile) < time() - 60 * $localCacheMinutes)
		{
			#Get file ----------------------------------------------------------------------------------------------------------------------------------------------------
			$return["exportLinks"]["xml"] = $urlXML = "http://hex.hasznaltauto.hu/{$xmlVersion}/xml/{$exportName}";
			$return["exportLinks"]["xsd"] = $urlXSD = "http://hex.hasznaltauto.hu/{$xmlVersion}/hex.xsd";

			if(ini_get("allow_url_fopen")) 
			{
				$return["getXMLMode"] = "file_get_contents()";
				$xmlString = @file_get_contents($urlXML);
				$xsdString = @file_get_contents($urlXSD);
			} 
			elseif(function_exists("curl_init")) 
			{
				$return["getXMLMode"] = "curl_init()";
				
				#XML
				$curl = @curl_init();
				@curl_setopt($curl, CURLOPT_URL, $urlXML);
				@curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				if(!$xmlString = @curl_exec($curl)) {	$return["errors"]["DOWNLOAD => Get XML File: CURL"] = "An error occured during the CURL methods: ".@curl_error($curl); }
				@curl_close($curl);
				
				#XSD
				$curl = @curl_init();
				@curl_setopt($curl, CURLOPT_URL, $urlXSD);
				@curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				if(!$xsdString = @curl_exec($curl)) {	$return["errors"]["DOWNLOAD => Get XSD File: CURL"] = "An error occured during the CURL methods: ".@curl_error($curl); }
				@curl_close($curl);
			} 
			else 
			{ 
				$return["getXMLMode"] = "ERROR";
				$return["errors"]["DOWNLOAD => Get XML File"] = "HEX can't be donwloaded because of the server settings!";
			}
			
			if(!$xmlString) { $return["errors"]["DOWNLOAD => XML string download"] = "An error occured during downloading the XML string ('xmlString')."; }
			if(!$xsdString) { $return["errors"]["DOWNLOAD => XSD string download"] = "An error occured during downloading the XSD string ('xsdString')."; }
			
			#Cheching HEX XML --------------------------------------------------------------------------------------------------------------------------------------------
			libxml_use_internal_errors(true);
			$objDomDocument = new \DomDocument();
			$objDomDocument->loadXML($xmlString);
			
			if(!$xmlRows = @simplexml_import_dom($objDomDocument)) { $return["errors"]["CHECKING => Empty XML"] = "The XML file is empty OR it's format isn't XML: ".$urlXML; }		
			if($xmlRows->getName() != "hirdetesek") { $return["errors"]["CHECKING => Name"] = "HEX error: ".(string)$xmlRows; }				
			if(!isset($xmlRows->attributes()->min_client_version) OR !isset($xmlRows->attributes()->max_client_version)) { $return["errors"]["CHECKING => Client version"] = "The XML doesn't contain the necessary client-version restrictions! The XML file can't be processed!"; }			
			if(version_compare($xmlVersion, (string)$xmlRows->attributes()->min_client_version) < 0 OR version_compare((string)$xmlRows->attributes()->max_client_version, $xmlVersion) < 0) { $return["errors"]["CHECKING => Client version compare"] = "The HEX XML can't be used with the current program. Please check the version numbers!"; }
			
			if(!@$objDomDocument->schemaValidateSource($xsdString))
			{
				$xsdErrors = [];
				foreach(libxml_get_errors() as $eachError) { $xsdErrors[] = $eachError->message; }
				$return["errors"]["CHECKING => Schema (XSD) validation"] = "The XML doesn't fit in the schema! Errors: ".implode("\r\n", $xsdErrors);
			}
			
			$xmlRowCount = count($xmlRows->children());
			if($xmlRowCount == 0) { $return["errors"]["CHECKING => Row count"] = "Currently there are no active rows!"; }
			
			
			#Store row datas ---------------------------------------------------------------------------------------------------------------------------------------------
			if(count($return["errors"]) == 0) { $datas = $xmlRows; }		
		} 
		elseif(is_file($localCacheFile)) 
		{
			$datas = unserialize(file_get_contents($localCacheFile));
			$xmlRowCount = count($datas);
		}
		else { $return["errors"]["Unknown error"] = "Unknown error!"; }
		
		#Return ----------------------------------------------------------------------------------------------------------------------------------------------------------
		$fullReturn = [
			"details" => $return,
			"errors" => $return["errors"],
			"xmlString" => $xmlString,
			"xsdString" => $xsdString,
			"xmlRowCount" => $xmlRowCount,
			"datas" => $datas,
		];
		return $fullReturn;
	}
	
	public function getExportedDatas($datas)
	{
		$return = [];
		$i = 0;
		foreach($datas AS $data)
		{
			$return[$i] = [
				"attributes" => [],
				"datas" => [],
				"pics" => [],
			];
			
			#Row attributes
			foreach($data->attributes() AS $key => $val) { $return[$i]["attributes"][$key] = (string)$val; }
			
			#Row children
			foreach($data->children() AS $itemKey => $itemVal)
			{
				switch($itemKey)
				{
					case "kepek":
						$j = 0;
						foreach($itemVal->children() AS $key => $val) 
						{ 
							$return[$i]["pics"][$j] = [];
							$return[$i]["pics"][$j]["itemValue"] = (string)$val;
							foreach($val->attributes() AS $key2 => $val2) { $return[$i]["pics"][$j][$key2] = (string)$val2; } 
							$j++;
						}
						break;
					default:
						$return[$i]["datas"][$itemKey] = [];
						$return[$i]["datas"][$itemKey]["itemValue"] = (string)$itemVal;
						foreach($itemVal->attributes() AS $key => $val) { $return[$i]["datas"][$itemKey][$key] = (string)$val; }
						break;
				}
			}
			$i++;
		}
		
		return $return;
	}
	
	public function updateDatabase($datas)
	{
		#Basic
		$return = [
			"idInUse" => [],
			"cars" => [],
			"insertedRows" => 0,
			"updatedRows" => 0,
			"deletedRows" => 0,
		];
		$table = $this->model->tables("cars");
		$tableDatas = $this->model->tables("datas");
		$tablePics = $this->model->tables("pics");
		
		#Database
		foreach($datas AS $index => $data)
		{
			$params = [];
			
			#Basic datas
			$params["code"] = $data["attributes"]["hirdeteskod"];
			$params["innerCode"] = $data["attributes"]["belsoazonosito"];
			$params["partnerID"] = $data["attributes"]["partnerkod"];
			$params["category"] = $data["attributes"]["kategoria"];
			$params["brand"] = $data["attributes"]["gyartmany"];
			$params["name"] = $data["attributes"]["tipus"];
			$params["model"] = $data["attributes"]["modell"];
			
			$params["type"] = $this->model->getTypeByPartnerID($params["partnerID"], "id");
			$params["url"] = self::generateURL($params["brand"]." ".$params["name"], $params["code"]);
			
			#Insert or update
			$id = $this->model->getCarByCode($params["code"], "id");
			if(!empty($id)) 
			{ 
				$this->model->myUpdate($table, $params, $id);
				$return["updatedRows"]++;
			}
			else 
			{ 
				$id = $this->model->myInsert($table, $params);
				$return["insertedRows"]++;
			}
			$return["idInUse"][] = $id;
			
			#Datas
			$idInUse = [];
			foreach($data["datas"] AS $dataKey => $data2)
			{
				$params2 = [
					"car" => $id,
					"url" => $dataKey,
				];
				$i = 1;
				foreach($data2 AS $key => $val)
				{
					switch($key)
					{
						case "megnevezes": $params2["name"] = $val; break;
						case "itemValue": $params2["value"] = $val; break;
						case "nyersadat": $params2["baseValue"] = $val; break;
						case "mertekegyseg": $params2["unit"] = $val; break;
						default:
							$keyName = "data".$i;
							$params2[$keyName] = $val;
							$params2[$keyName."Name"] = $key;
							$i++;
							if($i > 5) { break(2); }
					}
				}
				#Insert or update
				$row = $this->model->getDataByCarAndURL($id, $dataKey);
				if(!empty($row) AND isset($row->id) AND !empty($row->id))
				{ 
					$id2 = $row->id;
					$this->model->myUpdate($tableDatas, $params2, $row->id);
				}
				else { $id2 = $this->model->myInsert($tableDatas, $params2); }
				$idInUse[] = $id2;
			}
			$rows = $this->model->getDatasByCar($id);
			foreach($rows AS $row)
			{
				if(!in_array($row->id, $idInUse)) { $this->model->myDelete($tableDatas, $row->id); }
			}
			
			#Pics
			$idInUse = [];
			foreach($data["pics"] AS $dataKey => $data2)
			{
				$params2 = [];
				$params2["car"] = $id;
				if(isset($data2["itemValue"])) { $params2["basic"] = $data2["itemValue"]; }
				if(isset($data2["kicsi"])) { $params2["small"] = $data2["kicsi"]; }
				if(isset($data2["kozepes"])) { $params2["medium"] = $data2["kozepes"]; }
				if(isset($data2["orias"])) { $params2["big"] = $data2["orias"]; }
				if(isset($data2["kepalairas"])) { $params2["text"] = $data2["kepalairas"]; }
				
				#Insert or update
				$row = $this->model->getPicByCarAndBasic($id, $data2["itemValue"]);
				if(!empty($row) AND isset($row->id) AND !empty($row->id))
				{ 
					$id2 = $row->id;
					$this->model->myUpdate($tablePics, $params2, $row->id);
				}
				else { $id2 = $this->model->myInsert($tablePics, $params2); }
				$idInUse[] = $id2;
			}
			$rows = $this->model->getPicsByCar($id);
			foreach($rows AS $row)
			{
				if(!in_array($row->id, $idInUse)) { $this->model->myDelete($tablePics, $row->id); }
			}
			
			#Prices
			$car = $this->getCar($id, false);
			$params3 = [];

			$params3["price"] = $car["priceUnFormatted"];
			if($car["priceIsNet"]) { $net = 1; }
			else { $net = 0; }
			$params3["priceIsNet"] = $net;
			$params3["priceFormatted"] = $car["price"];
			$params3["priceGross"] = $car["priceGrossUnFormatted"];
			
			$this->model->myUpdate($table, $params3, $id);
		}
		
		#Delete the rest
		$rows = $this->model->getCarsByType($params["type"]);
		foreach($rows AS $row)
		{
			if(!in_array($row->id, $return["idInUse"]))
			{
				$this->model->myDelete($table, $row->id);
				$return["deletedRows"]++;
			}
		}
		
		#Return
		return $return;
	}
	
	public function import($exportName)
	{
		#Get XML
		$export = $this->export($exportName);
		
		#Params
		$params = [
			"type" => $this->model->getTypeByExportName($exportName, "id"),
			"xml" => NULL, // $export["xmlString"]
			"xsd" => NULL, // $export["xsdString"]
			"returnData" => $this->json($export["details"]),
			"errors" => $this->json($export["details"]["errors"]),
			"rowCount" => $export["xmlRowCount"],
			"insertedRows" => NULL,
			"updatedRows" => NULL,
			"deletedRows" => NULL,
		];
		
		#Database
		if(count($export["errors"]) == 0)
		{
			$datas = $this->getExportedDatas($export["datas"]);
			$update = $this->updateDatabase($datas);
			$params["insertedRows"] = $update["insertedRows"];
			$params["updatedRows"] = $update["updatedRows"];
			$params["deletedRows"] = $update["deletedRows"];
		}
		
		#Return
		return $this->model->myInsert($this->model->tables("imports"), $params);
	}
	
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
