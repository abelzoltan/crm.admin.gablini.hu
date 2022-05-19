<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Car;

class CarController extends BaseController
{	
	public $model;
	public $modelsURL = "uj-autok/";
	public $modelNewLabel = "Új ";
	
	public $priceCurrency = "Ft";
	public $priceNet = " + Áfa";
	public $priceFromLabel = "-tól";
	public $noPriseText = "Hamarosan!";
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Car";
		$this->model = new \App\Car($connectionData);
	}
	
	#Pricees and format
	public function priceFormat($price, $isNet = false)
	{
		$return = number_format($price, 0, ",", " ")." ".$this->priceCurrency;
		if($isNet) { $return .= $this->priceNet; }
		
		return $return;
	}
	
	public function getPriceFrom($car)
	{
		$datas = $this->model->getPriceFrom($car);
		
		#Has original price
		if(count($datas["originalRows"]) > 0) 
		{ 
			$price = $datas["originalRows"][0]->priceOriginal;
			$isNet = $datas["originalRows"][0]->isNet;
			$type = "original";
			if(count($datas["saleRows"]) > 0 AND $datas["saleRows"][0]->priceSale < $price)	
			{ 
				$price = $datas["saleRows"][0]->priceSale; 
				$isNet = $datas["saleRows"][0]->isNet;
				$type = "sale";
			}		
		}
		#Has NOT original price, but sale price
		elseif(count($datas["saleRows"]) > 0) 
		{ 
			$price = $datas["saleRows"][0]->priceSale; 
			$isNet = $datas["saleRows"][0]->isNet;
			$type = "sale";
		}
		#Has NO Price
		else 
		{ 
			$price = 0; 
			$isNet = 0;
			$type = NULL;
		}
		
		#Return
		$return = [
			"type" => $type,
			"price" => $price,
			"isNet" => $isNet,
			"formatted" => $this->priceFormat($price, $isNet),
		];
		return $return;
	}
	
	#Car new label
	public function newLabel($car)
	{
		$return = [
			"active" => NULL,
			"label" => NULL,
			"activeTime" => [
				"from" => NULL,
				"to" => NULL,
			],
		];
		
		#Checkbox: if true, the label is active ALWAYS!
		if($car->newLabel) { $active = true; }
		#If checkbox is false, than check the dates --> if there is a date set up
		elseif((!empty($car->newLabelFrom) AND $car->newLabelFrom != "0000-00-00 00:00:00") OR (!empty($car->newLabelTo) AND $car->newLabelTo != "0000-00-00 00:00:00"))
		{
			$date = date("Y-m-d H:i:s");
			$newLabelFromOK = true;
			if(!empty($car->newLabelFrom) AND $car->newLabelFrom != "0000-00-00 00:00:00") 
			{
				$return["activeTime"]["from"] = $car->newLabelFrom;
				if($car->newLabelFrom <= $date) { $active = true; }
				else { $active = false; $newLabelFromOK = false; }
			}
			if(!empty($car->newLabelTo) AND $car->newLabelTo != "0000-00-00 00:00:00") 
			{
				$return["activeTime"]["to"] = $car->newLabelTo;
				if($car->newLabelTo >= $date AND $newLabelFromOK) { $active = true; }
				else { $active = false; }
			}
		}
		#NOT active
		else { $active = false; }
		
		$return["active"] = $active;
		
		#Label
		if($active)
		{
			if(!empty($car->newLabelText)) { $return["label"] = $car->newLabelText; }
			else { $return["label"] = $this->modelNewLabel; }
		}
		else { $return["label"] = ""; }
		
		return $return;
	}

	#Get car
	public function getCar($id, $brand = NULL, $allDatas = true)
	{
		$car = $this->model->getCar($id);
		if(!empty($car) AND isset($car->id) AND !empty($car->id))
		{
			#Basic datas: car, brand, category
			$return = [];
			$return["data"] = $car;
			$return["id"] = $car->id;
			
			if(empty($brand)) { $return["brand"] = $brand = $this->model->getBrand($car->brand); }
			else { $return["brand"] = $brand; }
			$return["category"] = $category = $this->model->getCategory($car->category);
			
			#Active
			if($car->visible) { $return["active"] = true; }
			else { $return["active"] = false; }
			
			#Text datas: name, url, ...
			$return["brandURL"] = $brand->name;
			$return["brandName"] = $brand->nameOut;
			$return["categoryURL"] = $category->name;
			$return["categoryName"] = $category->nameOut;
			
			$return["innerName"] = $car->nameInner;
			$return["carURL"] = $car->name;
			$return["carName"] = $car->nameOut;
			
			$return["url"] = $this->modelsURL.$return["carURL"];
			$return["name"] = $return["brandName"]." ".$return["carName"];
			
			#New label and names
			$return["newLabel"] = $newLabel = $this->newLabel($car);
			if($newLabel["active"]) { $return["isNew"] = true; }
			else { $return["isNew"] = false; }
			
			if(!empty($car->newLabelFrom)) { $return["dateNewLabelFrom"] = date("Y-m-d", strtotime($car->newLabelFrom)); }
			else { $return["dateNewLabelFrom"] = ""; }
			if(!empty($car->newLabelTo)) { $return["dateNewLabelTo"] = date("Y-m-d", strtotime($car->newLabelTo)); }
			else { $return["dateNewLabelTo"] = ""; }
			
			$return["fullName"] = $newLabel["label"].$return["name"];
			$return["shortName"] = $newLabel["label"].$return["carName"];
			
			if($car->useFullName) { $return["publicName"] = $return["fullName"]; }
			else { $return["publicName"] = $return["shortName"]; }
			
			#Price from
			$return["priceFrom"] = $this->getPriceFrom($car->id);
			if(!empty($return["priceFrom"]["price"])) 
			{ 
				$return["priceFromOut"] = $return["priceFrom"]["formatted"]; 
				$return["priceFromOutWithLabel"] = $return["priceFromOut"].$this->priceFromLabel; 
			}
			else { $return["priceFromOut"] = $return["priceFromOutWithLabel"] = $this->noPriseText; }
			
			#List picture
			$file = new \App\Http\Controllers\FileController;
			$return["picList"] = NULL;
			$return["picListLink"] = $return["picDocuments"] = env("PATH_ROOT_WEB")."pics/logok/".$brand->name.".png";
			if(!empty($car->picList)) 
			{ 
				$return["picList"] = $file->getFile($car->picList); 
				if(!empty($return["picList"]["path"]["inner"]) AND file_exists($return["picList"]["path"]["inner"])) { $return["picListLink"] = $return["picDocuments"] = $return["picList"]["path"]["web"]; }
			}
				
			
			if($allDatas)
			{
				#Form picture
				$return["picForm"] = NULL;
				if(!empty($car->picForm)) { $return["picForm"] = $file->getFile($car->picForm); }
				
				#Form picture mobile
				$return["picFormMobile"] = NULL;
				if(!empty($car->picFormMobile)) { $return["picFormMobile"] = $file->getFile($car->picFormMobile); }
				
				#Facilities
				$return["facilities"] = $this->getFacilities($car->id);
				
				#Facility data-categories
				$return["dataCategories"] = $this->getDataCategories($car->id);
				
				#Facility datas
				$return["datas"] = $this->getDatas($id);
				
				#Motors
				$return["motors"] = $this->getMotors($car->id);
				
				#Models
				$return["models"] = $this->getModels($car->id);
				
				#Documents
				$return["documents"] = $file->getFileList("new-car-documents", $car->id);
				
				#Gallery
				$return["gallery"] = $file->getFileList("new-car-gallery", $car->id);
				
				#Descriptions
				$return["descriptions"] = $this->getDescriptionByCar($car->id);
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getCarByURL($url, $brandName, $allDatas = true)
	{
		$brand = $this->model->getBrandByName($brandName);
		$id = $this->model->getCarByURL($url, $brand->id, "id");
		return $this->getCar($id, $brand, $allDatas);
	}
	
	public function getCarsByBrand($brandName, $visible = NULL, $deleted = 0, $key = "id", $orderNumber = "category, orderNumber")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
			"categories" => [],
		];
		$brand = $this->model->getBrandByName($brandName);
		$rows = $this->model->getCarsByBrand($brand->id, $visible, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$car = $this->getCar($row->id, $brand, false); 
			
			#All, active, inactive
			$return["all"][$keyHere] = $car;
			if($car["active"]) { $return["active"][$keyHere] = $car; }
			else { $return["inactive"][$keyHere] = $car; }
			
			#Categories
			$return["categories"][$car["data"]->category]["all"][$keyHere] = $car;
			if($car["active"]) { $return["categories"][$car["data"]->category]["active"][$keyHere] = $car; }
			else { $return["categories"][$car["data"]->category]["inactive"][$keyHere] = $car; }
		}
		return $return;
	}
	
	#Get descriptions
	public function getDescriptionByCar($carID)
	{
		$row = $this->model->getDescriptionByCar($carID);
		if($row !== false) 
		{ 
			$return = [
				"data" => $row,
				"base" => ["title" => "Adatlap", "url" => "", "text" => $row->text],
			];

			for($i = 1; $i <= 4; $i++)
			{
				$field = "text".$i;
				$fieldTitle = $field."Title";
				$fieldURL = "url".$i;
				$return[$field] = ["title" => $row->$fieldTitle, "url" => $row->$fieldURL, "text" => $row->$field];
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	#Get categories
	public function getCategories($brandName, $deleted = 0, $key = "name", $orderNumber = "orderNumber")
	{
		$return = [];
		$brand = $this->model->getBrandByName($brandName);
		$rows = $this->model->getCategories($brand->id, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$return[$keyHere] = $row;
		}
		return $return;
	}
	
	#Get facilities
	public function getFacilities($car, $active = NULL, $deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
		];
		$rows = $this->model->getFacilities($car, $active, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }

			$return["all"][$keyHere] = $row;
			if($row->active) { $return["active"][$keyHere] = $row; }
			else { $return["inactive"][$keyHere] = $row; }
		}
		return $return;
	}
	
	#Get motors
	public function getMotor($id, $allDatas = true)
	{
		$motor = $this->model->getMotor($id);
		if(!empty($motor) AND isset($motor->id) AND !empty($motor->id))
		{
			#Basic datas: car, brand, category
			$return = [];
			$return["data"] = $motor;
			$return["id"] = $motor->id;
			$return["active"] = $motor->active;
			$return["url"] = $motor->name;
			$return["name"] = $motor->nameOut;
			
			#Fuel
			if(!empty($motor->fuel))
			{
				$return["fuel"] = $this->model->getFuel($motor->fuel);
				$return["fuelName"] = $return["fuel"]->nameOut;
			}
			else 
			{ 
				$return["fuel"] = false; 
				$return["fuelName"] = NULL; 
			}
			
			#Name
			$return["fullName"] = $return["name"];
			if(!empty($return["fuelName"])) { $return["fullName"] .= " [".$return["fuelName"]."]"; }
			
			if($allDatas)
			{
				#Basic datas
				$return["details"] = [];
				$return["details"]["name"] = [
					"fieldName" => "nameOut",
					"title" => "Megnevezés",
					"baseValue" => $motor->nameOut,
					"value" => $return["name"],
					"valueHTML" => $return["name"],
				];
				$return["details"]["fuel"] = [
					"fieldName" => "fuel",
					"title" => "Motor típusa / Üzemanyag",
					"baseValue" => $motor->fuel,
					"value" => $return["fuelName"],
					"valueHTML" => $return["fuelName"],
				];
				$cm3 = number_format($motor->cm3, 0, ",", " ");
				$return["details"]["cm3"] = [
					"fieldName" => "fuel",
					"title" => "Hengerűrtartalom",
					"baseValue" => $motor->cm3,
					"value" => $cm3." cm3",
					"valueHTML" => $cm3." cm<sup>3</sup>",
				];
				$powerHP = number_format($motor->powerHP, 0, ",", " ");
				$return["details"]["powerHP"] = [
					"fieldName" => "powerHP",
					"title" => "Teljesítmény (LE)",
					"baseValue" => $motor->powerHP,
					"value" => $powerHP." LE",
					"valueHTML" => $powerHP." LE",
				];
				$powerKW = number_format($motor->powerKW, 0, ",", " ");
				$return["details"]["powerKW"] = [
					"fieldName" => "powerKW",
					"title" => "Teljesítmény (kW)",
					"baseValue" => $motor->powerKW,
					"value" => $powerKW." kW",
					"valueHTML" => $powerKW." kW",
				];
				$return["baseDetails"] = $return["details"];
				
				#Dynamic datas
				$dynamicDatas = $this->getMotorDatas($motor->id);
				$return["dynamicDetails"] = [];
				if(count($dynamicDatas) > 0)
				{
					foreach($dynamicDatas As $dynamicDataID => $dynamicData)
					{
						$return["dynamicDetails"]["dynamic".$dynamicDataID] = $return["details"]["dynamic".$dynamicDataID] = [
							"fieldName" => "dynamic|value",
							"title" => $dynamicData->name,
							"baseValue" => $dynamicData->value,
							"value" => $dynamicData->value,
							"valueHTML" => $dynamicData->value,
						];
					}
				}
				
				$return["baseDetails"]["text"] = $return["details"]["text"] = [
					"fieldName" => "details",
					"title" => "Részletek",
					"baseValue" => $motor->details,
					"value" => $motor->details,
					"valueHTML" => $motor->details,
				];
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getMotors($car, $active = NULL, $deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
		];
		$rows = $this->model->getMotors($car, $active, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$row2 = $this->getMotor($row->id);

			$return["all"][$keyHere] = $row2;
			if($row->active) { $return["active"][$keyHere] = $row2; }
			else { $return["inactive"][$keyHere] = $row2; }
		}
		return $return;
	}
	
	public function getMotorDatas($motor, $deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [];
		$rows = $this->model->getMotorDatas($motor, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$return[$keyHere] = $row;
		}
		return $return;
	}
	
	#Get models
	public function getModel($row)
	{
		$return = $row;
		$row->priceOut = 0;
		$row->priceOutName = NULL;
		$row->priceOutFormatted = "";
		if(!empty($row->priceOriginal)) 
		{
			$row->priceOriginalFormatted = $this->priceFormat($row->priceOriginal, $row->isNet);
			$row->priceOut = $row->priceOriginal;
			$row->priceOutName = "original";
		}
		if(!empty($row->priceSale)) 
		{
			$row->priceSaleFormatted = $this->priceFormat($row->priceSale, $row->isNet);
			if($row->priceSale < $row->priceOriginal OR empty($row->priceOriginal)) 
			{
				$row->priceOut = $row->priceSale;
				$row->priceOutName = "sale";
			}
		}
		
		if(!empty($row->priceOut)) { $row->priceOutFormatted = $this->priceFormat($row->priceOut, $row->isNet); }
		
		return $return;
	}
	
	public function getModels($car, $active = NULL, $deleted = 0, $key = "id", $orderNumber = "motor, facility, priceOriginal, priceSale")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
			"motor" => [
				"active" => [],
				"inactive" => [],
				"all" => [],
			],
			"facility" => [
				"active" => [],
				"inactive" => [],
				"all" => [],
			],
			"facilityIDsByMotors" => [
				"active" => [],
				"inactive" => [],
				"all" => [],
			],
		];
		$rows = $this->model->getModels($car, $active, $deleted, $orderNumber);
		foreach($rows AS $i => $baseRow)
		{
			#Row, Key
			$row = $this->getModel($baseRow);
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }

			#Basic
			$return["all"][$keyHere] = $row;
			if($row->active) { $return["active"][$keyHere] = $row; }
			else { $return["inactive"][$keyHere] = $row; }
			
			#Motor
			$return["motor"][$row->motor]["all"][$keyHere] = $row;
			if($row->active) { $return["motor"][$row->motor]["active"][$keyHere] = $row; }
			else { $return["motor"][$row->motor]["inactive"][$keyHere] = $row; }
			
			#Facility
			$return["facility"][$row->facility]["all"][$keyHere] = $row;
			if($row->active) { $return["facility"][$row->facility]["active"][$keyHere] = $row; }
			else { $return["facility"][$row->facility]["inactive"][$keyHere] = $row; }
			
			#Facility ID's by motors
			$return["facilityIDsByMotors"][$row->motor]["all"][$row->facility] = $row;
			if($row->active) { $return["facilityIDsByMotors"][$row->motor]["active"][$row->facility] = $row; }
			else { $return["facilityIDsByMotors"][$row->motor]["inactive"][$row->facility] = $row; }
		}
		return $return;
	}
	
	#Get fuels
	public function getFuels($deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [];
		$rows = $this->model->getFuels($deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			
			$return[$keyHere] = $row;
		}
		return $return;
	}
	
	#Get data-categories
	public function getDataCategories($car, $active = NULL, $deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [
			"active" => [],
			"inactive" => [],
			"all" => [],
		];
		$rows = $this->model->getDataCategories($car, $active, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }

			$return["all"][$keyHere] = $row;
			if($row->active) { $return["active"][$keyHere] = $row; }
			else { $return["inactive"][$keyHere] = $row; }
		}
		return $return;
	}
	
	#Get datas by car & category
	public function getData($id)
	{
		$data = $this->model->getData($id);
		if(!empty($data) AND isset($data->id) AND !empty($data->id))
		{
			$data->nameOut = str_replace('"', "''", stripslashes($data->nameOut));
			
			$return = [];
			$return["data"] = $data;
			$return["id"] = $data->id;
			$return["name"] = $data->nameOut;
			$return["facilities"] = $this->getDataFacilities($data->id);
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getDatas($car, $category = NULL, $deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [
			"all" => [],
			"category" => [],
		];
		$rows = $this->model->getDatas($car, $category, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			
			$row2 = $this->getData($row->id);
			$return["all"][$keyHere] = $row2;
			$return["category"][$row->category][$keyHere] = $row2;
		}
		return $return;
	}
	
	#Get data types
	public function getDataTypes($deleted = 0, $key = "id", $orderNumber = "orderNumber")
	{
		$return = [];
		$rows = $this->model->getDataTypes($deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			
			$return[$keyHere] = $row;
		}
		return $return;
	}
	
	#Get data facilities
	public function getDataFacilityRow($data, $facility)
	{
		$row = $this->model->getDataFacilityRow($data, $facility);		
		if(!empty($row) AND isset($row[0]) AND isset($row[0]->id) AND !empty($row[0]->id)) { $return = $row[0]; }		
		else { $return = false; }		
		return $return;
	}
	
	public function getDataFacility($row)
	{
		$return = $row;		
		if(!empty($row->price)) { $row->priceFormatted = $this->priceFormat($row->price); }		
		else { $row->priceFormatted = $row->price = NULL; }	
	
		if(!empty($row->priceFormatted)) { $row->textOut = $row->priceFormatted; }
		elseif(!empty($row->text)) { $row->textOut = $row->text; }
		else { $row->textOut = ""; }
		
		return $return;
	}
	
	public function getDataFacilities($data, $facility = NULL, $type = NULL, $deleted = 0, $orderNumber = "facility, id")
	{
		$return = [
			"all" => [],
			"facility" => [],
		];
		$rows = $this->model->getDataFacilities($data, $facility, $type, $deleted, $orderNumber);
		foreach($rows AS $i => $row)
		{
			$row2 = $this->getDataFacility($row);
			$return["all"][$row->id] = $row2;
			$return["facility"][$row->facility] = $row2;
		}
		return $return;
	}
	
	#Import wheels
	public function importWheels($filePath, $sizes = [], $vat = 1.27)
	{
		if(($handle = fopen($filePath, "r")) !== false)
		{
			#Col keys
			$col = [
				"carBrandName" => 0,
				"brand" => 1,
				"type" => 2,
				"size" => 3,
				"name" => 4,
				"priceNet" => 5,
				"itemNumber" => 9,
				"markings" => 6,
				"wetGrip" => 7,
				"rollingResistance" => 10,
				"dB" => 8,
			];
			
			#Brands
			$brands = [];
			foreach($this->model->getBrands() AS $brand) { $brands[$brand->nameOut] = $brand; }
			
			#Lines
			$i = 0;
			while(($datas = fgetcsv($handle, 0, ";")) !== false)
			{
				#Check size
				$size = trim($datas[$col["size"]]);
				if(!empty($sizes) AND !in_array($size, $sizes)) { continue; }
				
				#Spec. datas
				$priceNet = str_replace(",", ".", trim($datas[$col["priceNet"]]));
				$dB = trim($datas[$col["dB"]]);
					
				#Params
				$params = [
					"carBrand" => $brands[trim($datas[$col["carBrandName"]])]->id,
					"brand" => trim($datas[$col["brand"]]),
					"type" => trim($datas[$col["type"]]),
					"size" => $size,
					"name" => trim($datas[$col["name"]]),
					"priceNet" => $priceNet,
					"priceGross" => $priceNet * $vat,
					"itemNumber" => trim($datas[$col["itemNumber"]]),
					"markings" => trim($datas[$col["markings"]]),
					"wetGrip" => trim($datas[$col["wetGrip"]]),
					"rollingResistance" => trim($datas[$col["rollingResistance"]]),
					"dB" => (!empty($dB)) ? $dB : NULL,
				];
				
				#Insert
				$id = $this->model->myInsert($this->model->tables("wheels"), $params);
				$i++;
			}
			
			#Return
			$return = "Success: ".$i." wheels";
		}
		else { $return = "Error: file open"; }

		return $return;
	}
	
	#Get wheel filters
	public function getWheelFilters($field, $returnKey = "index")
	{
		$return = [];
		$rows = $this->model->select("SELECT ".$field." FROM ".$this->model->tables("wheels")." WHERE del = '0' AND priceNet > '0' AND ".$field." != '' AND ".$field." IS NOT NULL GROUP BY ".$field." ORDER BY ".$field);
		foreach($rows AS $i => $row) 
		{ 
			$key = ($returnKey == "field") ? $row->$field : $i;
			$return[$key] = $row->$field;
		}
		return $return;
	}
	
	#Get wheel prices for search
	public function getWheelPricesForSearch()
	{
		#Get prices
		$prices = $this->getWheelFilters("priceGross");
		asort($prices, SORT_NUMERIC);
		
		#Set values
		$return = [];
		// $return[$prices[0]] = $this->priceFormat($prices[0], false);
		
		$lastPrice = end($prices);
		$step = 5000;
		$number = $prices[0] - (2 * $step);
		while(true)
		{
			$number = ceil(($number + $step) / $step) * $step;
			if($number < $prices[0]) { continue; }

			$return[$number] = $this->priceFormat($number, false);
			if($number > $lastPrice) { break; }
		}
		
		#Return
		return $return;
	}
	
	#Get wheel
	public function getWheel($id, $carBrand = NULL)
	{
		$row = $this->model->getWheel($id);
		if(!isset($row->id) OR empty($row->id)) { $return = false; }
		else
		{
			#Basic
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			
			if($carBrand === NULL) { $return["carBrand"] = (!empty($row->carBrand)) ? (array)$this->model->getBrand() : []; }
			else { $return["carBrand"] = $carBrand; }
			
			$return["logo"] = "anyagok/gablini/gumiabroncsok/markak/".str_replace(" ", "_", $row->brand).".png";
			if(defined("PATH_WEB")) { $return["logo"] = PATH_WEB.$return["logo"]; }
			else { $return["logo"] = env("PATH_ROOT_WEB").$return["logo"]; }
			
			$return["brand"] = $row->brand;
			$return["type"] = $row->type;
			$return["size"] = $row->size;
			$return["name"] = $row->name;
			$return["itemNumber"] = $row->itemNumber;
			
			$return["markings"] = $row->markings;
			$return["wetGrip"] = $row->wetGrip;
			$return["rollingResistance"] = $row->rollingResistance;
			$return["dB"] = $row->dB;
			$return["dBOut"] = (!empty($return["dB"])) ? $return["dB"]." dB" : "";
			
			$return["priceNet"] = $row->priceNet;
			$return["priceNetRound"] = round($row->priceNet);
			$return["priceNetOut"] = number_format($return["priceNetRound"], 0, ",", " ")." Ft";
			
			$return["priceGross"] = $row->priceGross;
			$return["priceGrossRound"] = round($row->priceGross);
			$return["priceGrossOut"] = number_format($return["priceGrossRound"], 0, ",", " ")." Ft";
			
			$return["subject"] = $return["name"]." - ".$return["priceGrossOut"];

			#All details
			$return["details"] = [
				"name" => ["name" => "Megnevezés", "value" => $return["name"]],
				"brand" => ["name" => "Márka", "value" => $return["brand"]],
				"size" => ["name" => "Méret", "value" => $return["size"]],
				"type" => ["name" => "Típus", "value" => $return["type"]],
				"markings" => ["name" => "Mintázat", "value" => $return["markings"]],
				"wetGrip" => ["name" => "Nedves tapadás", "value" => $return["wetGrip"]],
				"dB" => ["name" => "Zaj (dB)", "value" => $return["dBOut"]],
				"itemNumber" => ["name" => "Cikkszám", "value" => $return["itemNumber"]],
				"rollingResistance" => ["name" => "Gördülési ellenállás", "value" => $return["rollingResistance"]],
				"price" => ["name" => "Bruttó ár", "value" => $return["priceGrossOut"]],
			];
			
			#Short text details
			$return["detailsShort"] = [];
			$return["detailsShortOut"] = [];
			foreach($return["details"] AS $key => $data)
			{
				if($key == "name" OR $key == "price") { continue; }
				if(!empty($data["value"])) 
				{ 
					$return["detailsShort"][$key] = $data;
					$return["detailsShortOut"][$key] = $data["name"].": <strong>".$data["value"]."</strong>";
				}
			}
			
			$return["detailsShortOut"] = implode("<br>", $return["detailsShortOut"]);
			
			#Main details
			$return["listDatas"] = [
				"size" => ["name" => "Méret", "value" => $return["size"]],
				"type" => ["name" => "Típus", "value" => $return["type"]],
				"wetGrip" => ["name" => "Nedves tapadás", "value" => $return["wetGrip"]],
				"dB" => ["name" => "Zaj (dB)", "value" => $return["dBOut"]],
			];
		}
		
		return $return;
	}
	
	#Get wheel
	public function getWheels($filterFields = [], $datas = [], $orderBy = "priceNet, name")
	{
		#Car brands
		$carBrands = [];
		foreach($this->model->getBrands() AS $carBrand) { $carBrands[$carBrand->id] = (array)$carBrand; }
		
		#Basic query
		$params = [];
		$query = "SELECT id, carBrand FROM ".$this->model->tables("wheels")." WHERE del = '0' AND priceNet > '0'";
		
		#Search: name, price from-to, dB from-to
		if(isset($datas["name"]) AND !empty($datas["name"]))
		{
			#String
			$searchString = $datas["name"];
			$delimiter = " ";
			
			$string = trim($searchString);
			$words = explode($delimiter, $string);	
			
			if(count($words) > 0)
			{
				$fields = ["name", "itemNumber"];
				$query .= " AND (";
				foreach($words AS $i => $word)
				{
					#String format
					$searchKey = $word;
					
					#Query and params
					if($i > 0) { $query .= " AND"; }
					$query .= " (";
					foreach($fields AS $j => $field)
					{
						if($j > 0) { $query .= " OR"; }
						$paramName = $field."Txt".$i.$j;
						$query .= " ".$field." LIKE :".$paramName; 
						$params[$paramName] = "%".$searchKey."%";
					}
					$query .= ")";
				}
				$query .= ")";
			}
		}
		
		if(isset($datas["priceFrom"]) AND !empty($datas["priceFrom"]))
		{
			$query .= " AND priceGross >= :priceFrom";
			$params["priceFrom"] = $datas["priceFrom"];
		}
		
		if(isset($datas["priceTo"]) AND !empty($datas["priceTo"]))
		{
			$query .= " AND priceGross <= :priceTo";
			$params["priceTo"] = $datas["priceTo"];
		}
		
		if(isset($datas["dbFrom"]) AND !empty($datas["dbFrom"]))
		{
			$query .= " AND dB >= :dbFrom";
			$params["dbFrom"] = $datas["dbFrom"];
		}
		
		if(isset($datas["dbTo"]) AND !empty($datas["dbTo"]))
		{
			$query .= " AND dB <= :dbTo";
			$params["dbTo"] = $datas["dbTo"];
		}
		
		#Filters: brands, types, sizes, ...
		foreach($filterFields AS $field)
		{
			if(isset($datas[$field]) AND !empty($datas[$field]))
			{
				$query .= " AND (";
				foreach($datas[$field] AS $i => $data)
				{
					if($i > 0) { $query .= " OR "; }
					$query .= $field." = :".$field.$i;
					$params[$field.$i] = $data;
				}
				$query .= ")";
			}
		}
		
		#Ordering
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		#Return rows
		$return = [];
		$rows = $this->model->select($query, $params);
		foreach($rows AS $i => $row) { $return[$row->id] = $this->getWheel($row->id, $carBrands[$row->carBrand]); }
		return $return;
	}
	
	#Admin work
	public function adminWork($tableName, $workType, $datas)
	{
		$table = $this->model->tables($tableName);
		$return = [
			"table" => $tableName,
			"work" => $workType,
			"datas" => $datas,
			"errors" => [],
			"type" => "success",
			"params" => [],
			"id" => NULL,
		];
		
		switch($tableName)
		{
			case "cars":
				switch($workType)
				{
					case "new":
						#Required
						$required = ["brand", "category", "name", "nameInner", "nameOut"];
						foreach($required AS $requiredField)
						{
							if(!isset($datas[$requiredField]) OR empty($datas[$requiredField]))
							{
								$return["errors"]["required"][] = $requiredField;
								$return["type"] = "error";
							}
						}
						
						if($return["type"] == "success")
						{
							#Check URL
							if(!$this->checkCarURL($datas["name"], $datas["brand"])) 
							{ 
								$datas["name"] = $this->setCarURL($datas["name"], $datas["brand"]);
								$return["errors"]["changed"][] = "url";
							}
							
							#Check inner name
							if(!$this->checkCarInnerName($datas["nameInner"], $datas["brand"])) 
							{ 
								$datas["nameInner"] = $this->setCarInnerName($datas["nameOut"], $datas["brand"]);
								$return["errors"]["changed"][] = "innerName";
							}
							
							#Params
							$params = [];
							foreach($required AS $field) { $params[$field] = $datas[$field]; }
							
							#OrderNumber, database command
							$params["date"] = date("Y-m-d H:i:s");
							$params["orderNumber"] = $this->carReOrder($datas["brand"], $datas["category"]);
							
							$return["id"] = $this->model->myInsert($table, $params);
							$return["params"] = $params;
						}
						break;
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$id = $car["id"];
						
						#Required
						$required = ["category", "name", "nameOut"];
						foreach($required AS $requiredField)
						{
							if(!isset($postDatas[$requiredField]) OR empty($postDatas[$requiredField]))
							{
								$return["errors"]["required"][] = $requiredField;
								$return["type"] = "error";
							}
						}
						
						if($return["type"] == "success")
						{
							#Check URL
							if(!$this->checkCarURL($postDatas["name"], $car["brand"]->id, $id)) 
							{ 
								$postDatas["name"] = $this->setCarURL($postDatas["name"], $car["brand"]->id, $id);
								$return["errors"]["changed"][] = "url";
							}
							
							#Fields
							$otherFields = ["visible", "useFullName", "newLabel", "newLabelFrom", "newLabelTo", "newLabelText", "picFormTitle", "picFormText", "picFormColor"];
							$fields = array_merge($required, $otherFields);
							
							#Params
							$params = [];
							foreach($fields AS $field) 
							{ 
								switch($field)
								{
									case "visible":
									case "newLabel":
									case "useFullName":
										if(isset($postDatas[$field]) AND $postDatas[$field]) { $params[$field] = 1; }
										else { $params[$field] = 0; }
										break;
									case "newLabelFrom":	
									case "newLabelTo":	
										if(isset($postDatas[$field]) AND !empty($postDatas[$field])) 
										{
											if($field == "newLabelFrom") { $params[$field] = $postDatas[$field]." 00:00:00"; }
											elseif($field == "newLabelTo") { $params[$field] = $postDatas[$field]." 23:59:59"; }
											else { $params[$field] = NULL; }
										}
										else { $params[$field] = NULL; }
										break;
									default:
										$params[$field] = $postDatas[$field]; 
										break;
								}
							}
							
							#OrderNumber - new category
							if($car["category"]->id != $params["category"]) { $params["orderNumber"] = $this->carReOrder($car["brand"]->id, $postDatas["category"]); }
							
							#Database Update
							$return["id"] = $this->model->myUpdate($table, $params, $id);
							$return["params"] = $params;
							
							#OrderNumber - original category
							if($car["category"]->id != $params["category"]) { $this->carReOrder($car["brand"]->id, $car["category"]->id); }
							
							#Pics
							$carNewData = $this->getCar($id, $car["brand"], false);
							$file = new \App\Http\Controllers\FileController;
							
							$return["pics"]["list"] = $fileReturn = $file->upload("picList", "new-car-pic-list", $id, [$carNewData["publicName"]]);
							if($fileReturn[0]["type"] == "success") { $this->adminWork($tableName, "picList", ["id" => $id, "pic" => $fileReturn[0]["fileID"]]); }
							
							$return["pics"]["form"] = $fileReturn = $file->upload("picForm", "new-car-pic-form", $id, [$carNewData["publicName"]]);
							if($fileReturn[0]["type"] == "success") { $this->adminWork($tableName, "picForm", ["id" => $id, "pic" => $fileReturn[0]["fileID"]]); }
							
							$return["pics"]["formMobile"] = $fileReturn = $file->upload("picFormMobile", "new-car-pic-form-mobile", $id, [$carNewData["publicName"]]);
							if($fileReturn[0]["type"] == "success") { $this->adminWork($tableName, "picFormMobile", ["id" => $id, "pic" => $fileReturn[0]["fileID"]]); }
						}
						break;	
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->carReOrder($datas["car"]->brand, $datas["car"]->category);
						break;
					case "activate":
						$this->model->myUpdate($table, ["visible" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["visible" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->carNewOrder($datas["orderType"], $datas["id"], $datas["car"]->brand, $datas["car"]->category);
						break;
					case "picList":	
					case "picForm":	
					case "picFormMobile":	
						$this->model->myUpdate($table, [$workType => $datas["pic"]], $datas["id"]);
						break;		
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;	
				}			
				break;
			case "descriptions":
				switch($workType)
				{
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Params
						$params = [];
						if(isset($postDatas["text"])) { $params["text"] = $postDatas["text"]; }
						for($i = 1; $i <= 4; $i++)
						{
							$field = "text".$i;
							$fieldTitle = $field."Title";
							$fieldURL = "url".$i;
							if(isset($postDatas[$fieldURL])) { $params[$fieldURL] = $postDatas[$fieldURL]; }						
							if(isset($postDatas[$fieldTitle])) { $params[$fieldTitle] = $postDatas[$fieldTitle]; }						
							if(isset($postDatas[$field])) { $params[$field] = $postDatas[$field]; }
						}						
						
						#Update
						if($car["descriptions"] !== false) 
						{
							$return["id"] = $id = $car["descriptions"]["data"]->id;
							$this->model->myUpdate($table, $params, $id);
						}
						#Insert
						else 
						{ 
							$params["car"] = $carID;
							$return["id"] = $id = $this->model->myInsert($table, $params); 
						}

						$return["params"] = $params;
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}
				break;
			case "facilities":
				switch($workType)
				{
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Edit facilities
						if(isset($postDatas["facility"]))
						{
							foreach($postDatas["facility"] AS $id => $facility)
							{
								if(!empty($facility))
								{
									#Params
									$params = [
										"name" => $this->setFacilityURL($facility, $carID, $id),
										"nameOut" => $facility,
									];
									
									#Database Update
									$return["id"] = $this->model->myUpdate($table, $params, $id);
								}
							}
						}
						
						#New facilities
						$orderNumber = $this->facilityReOrder($carID);
						foreach($postDatas["newFacility"] AS $facility)
						{
							if(!empty($facility))
							{
								#Params
								$params = [
									"car" => $carID,
									"name" => $this->setFacilityURL($facility, $carID),
									"nameOut" => $facility,
								];
								
								#OrderNumber
								$params["orderNumber"] = $orderNumber;
								$orderNumber++;
								
								#Database Insert
								$return["id"] = $id = $this->model->myInsert($table, $params);
							}
						}
						break;	
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->facilityReOrder($datas["facility"]->car);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->facilityNewOrder($datas["orderType"], $datas["id"], $datas["facility"]->car);
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}					
				break;	
			case "motors":
				switch($workType)
				{
					case "new":
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Params
						$fields = ["nameOut", "fuel", "powerHP", "powerKW", "cm3", "details"];
						$params = [];
						foreach($fields AS $field) { if(isset($postDatas[$field])) { $params[$field] = $postDatas[$field]; } }

						#URL						
						$params["name"] = $this->setMotorURL($postDatas["nameOut"], $carID, $postDatas["id"]);
						
						#Update
						if($workType == "edit") 
						{
							$return["id"] = $id = $postDatas["id"];
							$this->model->myUpdate($table, $params, $id);
						}
						#Insert
						else 
						{ 
							$params["car"] = $carID;
							$params["orderNumber"] = $this->motorReOrder($carID);
							$return["id"] = $id = $this->model->myInsert($table, $params); 
						}

						$return["params"] = $params;
						break;	
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->motorReOrder($datas["motor"]->car);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->motorNewOrder($datas["orderType"], $datas["id"], $datas["motor"]->car);
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}					
				break;	
			case "models":
				switch($workType)
				{
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Edit models
						if(isset($postDatas["model"]))
						{
							foreach($postDatas["model"] AS $id => $model)
							{
								#Params
								if(isset($model["isNet"]) AND $model["isNet"]) { $isNet = 1; }
								else { $isNet = 0; }
								$params = [
									"motor" => $model["motor"],
									"facility" => $model["facility"],
									"isNet" => $isNet,
									"priceOriginal" => $model["priceOriginal"],
									"priceSale" => $model["priceSale"],
								];
								
								#Database Update
								$return["id"] = $this->model->myUpdate($table, $params, $id);
							}
						}
						
						#New models
						if(isset($postDatas["newModel"]))
						{
							foreach($postDatas["newModel"] AS $model)
							{
								if(isset($model["motor"]) AND isset($model["facility"]) AND isset($model["priceOriginal"]) AND !empty($model["motor"]) AND !empty($model["facility"]) AND !empty($model["priceOriginal"]))
								{
									#Params
									if(isset($model["isNet"]) AND $model["isNet"]) { $isNet = 1; }
									else { $isNet = 0; }
									
									$token = $this->setModelToken();
									$tokenHashed = $this->hashModelToken($token);
									
									$params = [
										"token" => $token,
										"tokenHashed" => $tokenHashed,
										"car" => $carID,
										"motor" => $model["motor"],
										"facility" => $model["facility"],
										"isNet" => $isNet,
										"priceOriginal" => $model["priceOriginal"],
										"priceSale" => $model["priceSale"],
									];
									
									#Database Insert
									$return["id"] = $id = $this->model->myInsert($table, $params);
								}
							}
						}
						break;	
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}					
				break;
			case "dataCategories":
				switch($workType)
				{
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Edit categories
						if(isset($postDatas["category"]))
						{
							foreach($postDatas["category"] AS $id => $category)
							{
								if(!empty($category))
								{
									#Params
									$params = [
										"name" => $this->setDataCategoryURL($category, $carID, $id),
										"nameOut" => $category,
									];
									
									#Database Update
									$return["id"] = $this->model->myUpdate($table, $params, $id);
								}
							}
						}
						
						#New categories
						$orderNumber = $this->dataCategoryReOrder($carID);
						foreach($postDatas["newCategory"] AS $category)
						{
							if(!empty($category))
							{
								#Params
								$params = [
									"car" => $carID,
									"name" => $this->setDataCategoryURL($category, $carID),
									"nameOut" => $category,
								];
								
								#OrderNumber
								$params["orderNumber"] = $orderNumber;
								$orderNumber++;
								
								#Database Insert
								$return["id"] = $id = $this->model->myInsert($table, $params);
							}
						}
						break;	
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->dataCategoryReOrder($datas["category"]->car);
						break;
					case "activate":
						$this->model->myUpdate($table, ["active" => 1], $datas["id"]);
						break;	
					case "deactivate":
						$this->model->myUpdate($table, ["active" => 0], $datas["id"]);
						break;		
					case "order":
						$return["order"] = $this->dataCategoryNewOrder($datas["orderType"], $datas["id"], $datas["category"]->car);
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}					
				break;
			case "datas":
				switch($workType)
				{
					case "edit":
						#Base
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$carID = $car["id"];
						
						#Edit datas
						if(isset($postDatas["editDatas"]))
						{
							foreach($postDatas["editDatas"] AS $id => $dataHere)
							{
								if(!empty($dataHere))
								{
									#Params
									$params = ["nameOut" => $dataHere["name"]];
									
									#Category change (new)
									$dataCurrentRow = $car["datas"]["all"][$id]["data"];
									if($dataHere["category"] != $dataCurrentRow->category) 
									{ 
										$params["category"] = $dataHere["category"]; 
										$params["orderNumber"] = $this->dataReOrder($carID, $dataHere["category"]); 
									}
									
									#Database Update
									$return["id"] = $this->model->myUpdate($table, $params, $id);
									
									#Category change (original)
									if($dataHere["category"] != $dataCurrentRow->category) { $this->dataReOrder($carID, $dataCurrentRow->category); }
								}
							}
						}
						
						#New datas (multiple)
						if(isset($postDatas["newDatas"]["multiple"]) AND isset($postDatas["newDatas"]["multiple"]["category"]) AND isset($postDatas["newDatas"]["multiple"]["rows"]) AND !empty($postDatas["newDatas"]["multiple"]["category"]) AND !empty($postDatas["newDatas"]["multiple"]["rows"]))
						{
							$orderNumber = $this->dataReOrder($carID, $postDatas["newDatas"]["multiple"]["category"]);
							
							$rows = str_replace("\r\n", "XXXXX", $postDatas["newDatas"]["multiple"]["rows"]);
							$rows = str_replace("\n", "XXXXX", $rows);
							$rows = str_replace("\r", "XXXXX", $rows);
							
							$rows = explode("XXXXX", $rows);
							foreach($rows AS $name)
							{
								if(!empty($name))
								{
									#Params
									$params = [
										"car" => $carID,
										"category" => $postDatas["newDatas"]["multiple"]["category"],
										"nameOut" => $name,
									];
									$params["orderNumber"] = $orderNumber;
									$orderNumber++;
									
									#Database Insert
									$return["id"] = $id = $this->model->myInsert($table, $params);
								}
							}
						}
						
						#New datas (simple)
						if(isset($postDatas["newDatas"]["simple"]))
						{
							foreach($postDatas["newDatas"]["simple"] AS $dataHere)
							{
								if(isset($dataHere["category"]) AND isset($dataHere["name"]) AND !empty($dataHere["category"]) AND !empty($dataHere["name"]))
								{
									#Params
									$params = [
										"car" => $carID,
										"category" => $dataHere["category"],
										"nameOut" => $dataHere["name"],
										"orderNumber" => $this->dataReOrder($carID, $dataHere["category"]),
									];
									
									#Database Insert
									$return["id"] = $id = $this->model->myInsert($table, $params);
								}
							}
						}
						break;
					case "del":
						$this->model->myDelete($table, $datas["id"]);
						$this->dataReOrder($datas["data"]->car, $datas["data"]->category);
						break;		
					case "order":
						$return["order"] = $this->dataNewOrder($datas["orderType"], $datas["id"], $datas["data"]->car, $datas["data"]->category);
						break;
					case "type":
						$car = $datas["car"];
						$postDatas = $datas["post"];
						$facilityID = $postDatas["facility"];
						$facility = $datas["car"]["facilities"]["all"][$postDatas["facility"]];
						$carID = $car["id"];
						$table = $this->model->tables("dataFacilities");
						
						if(isset($postDatas["datas"]))
						{
							foreach($postDatas["datas"] AS $dataID => $dataArray)
							{
								$row = $this->getDataFacilityRow($dataID, $facilityID);
								$params = [];
								if(isset($dataArray["type"])) { $params["type"] = $dataArray["type"]; }
								if(isset($dataArray["text"])) { $params["text"] = $dataArray["text"]; }
								if(isset($dataArray["price"]))
								{ 
									if(!empty($dataArray["price"])) { $params["type"] = 2; }
									$params["price"] = $dataArray["price"]; 
								}

								#Update
								if($row !== false) { $this->model->myUpdate($table, $params, $row->id); }
								#Insert
								else
								{
									$params["data"] = $dataID;
									$params["facility"] = $facilityID;
									$this->model->myInsert($table, $params);
								}
							}
						}
						break;
					default:
						$return["errors"]["others"] = "unknown-worktype";
						$return["type"] = "error";
						break;		
				}					
				break;	
			default:
				$return["errors"]["others"] = "unknown-table";
				$return["type"] = "error";
				break;	
		}
		
		if(isset($GLOBALS["log"]))
		{
			$logData = [
				"vchar1" => "Car",
				"vchar2" => $tableName,
				"vchar3" => $workType,
				"text1" => $GLOBALS["log"]->json($return),
			];
			$GLOBALS["log"]->log("adminwork", $logData);
		}
		return $return;
	}
	
	#Generate car URL
	public function setCarURL($name, $brand, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("cars"), "name", $name, "", ["del" => 0, "brand" => $brand], $id);
	}
	
	#Check car URL duplication
	public function checkCarURL($url, $brand, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("cars"), "name", $url, ["del" => 0, "brand" => $brand], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Generate car's inner name
	public function setCarInnerName($name, $brand, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("cars"), "nameInner", $name." ".date("Y"), "", ["del" => 0, "brand" => $brand], $id);
	}
	
	#Check car's inner name duplication
	public function checkCarInnerName($innerName, $brand, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("cars"), "nameInner", $innerName, ["del" => 0, "brand" => $brand], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Generate facility URL
	public function setFacilityURL($name, $car, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("facilities"), "name", $name, "", ["del" => 0, "car" => $car], $id);
	}
	
	#Check facility URL duplication
	public function checkFacilityURL($name, $car, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("facilities"), "name", $name, ["del" => 0, "car" => $car], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Generate motor URL
	public function setMotorURL($name, $car, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("motors"), "name", $name, "", ["del" => 0, "car" => $car], $id);
	}
	
	#Check motor URL duplication
	public function checkMotorURL($name, $car, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("motors"), "name", $name, ["del" => 0, "car" => $car], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Generate data-category URL
	public function setDataCategoryURL($name, $car, $id = 0)
	{
		return $this->setUniqueURL($this->model->tables("dataCategories"), "name", $name, "", ["del" => 0, "car" => $car], $id);
	}
	
	#Check data-category URL duplication
	public function checkDataCategoryURL($name, $car, $id = 0)
	{
		$rows = $this->model->checkUniqueField($this->model->tables("dataCategories"), "name", $name, ["del" => 0, "car" => $car], $id);
		if(count($rows) > 0) { return false; }
		else { return true; }
	}
	
	#Order cars
	public function carReOrder($brand, $category)
	{
		$table = $this->model->tables("cars");
		return $this->model->reOrder($table, "del = '0' AND brand = :brand AND category = :category", ["brand" => $brand, "category" => $category]);
	}
	
	public function carNewOrder($type, $currentID, $brand, $category)
	{
		$table = $this->model->tables("cars");
		return $this->model->newOrder($type, $currentID, $table, "del = '0' AND brand = :brand AND category = :category", ["brand" => $brand, "category" => $category]);
	}
	
	#Order facilities
	public function facilityReOrder($car)
	{
		$table = $this->model->tables("facilities");
		return $this->model->reOrder($table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	public function facilityNewOrder($type, $currentID, $car)
	{
		$table = $this->model->tables("facilities");
		return $this->model->newOrder($type, $currentID, $table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	#Order motors
	public function motorReOrder($car)
	{
		$table = $this->model->tables("motors");
		return $this->model->reOrder($table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	public function motorNewOrder($type, $currentID, $car)
	{
		$table = $this->model->tables("motors");
		return $this->model->newOrder($type, $currentID, $table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	#Order data-categories
	public function dataCategoryReOrder($car)
	{
		$table = $this->model->tables("dataCategories");
		return $this->model->reOrder($table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	public function dataCategoryNewOrder($type, $currentID, $car)
	{
		$table = $this->model->tables("dataCategories");
		return $this->model->newOrder($type, $currentID, $table, "del = '0' AND car = :car", ["car" => $car]);
	}
	
	#Order datas
	public function dataReOrder($car, $category)
	{
		$table = $this->model->tables("datas");
		return $this->model->reOrder($table, "del = '0' AND car = :car AND category = :category", ["car" => $car, "category" => $category]);
	}
	
	public function dataNewOrder($type, $currentID, $car, $category)
	{
		$table = $this->model->tables("datas");
		return $this->model->newOrder($type, $currentID, $table, "del = '0' AND car = :car AND category = :category", ["car" => $car, "category" => $category]);
	}
	
	#Set and hash model token
	public function randomize($length = 10)
	{
		$chars = array_merge(range(0, 9), range("A", "Z"));
		$string = "";
		for($i = 0; $i < $length; $i++) { $string .= $chars[mt_rand(0, count($chars) - 1)]; }
		
		return $string;
	}
	public function setModelToken()
	{
		while(true)
		{
			$string = $this->randomize(10);
			$token = $string;
			
			$row = $this->model->getModelByToken($token, "", 0);
			if(!isset($row->id) OR empty($row->id)) { break; }
		}
		return $token;
	}
	
	public function hashModelToken($token)
	{
		$after = "bTa2Eb4C";
		return sha1($token.$after);
	}
}
