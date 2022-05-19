<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Premise;

class PremiseController extends BaseController
{	
	public $model;	
	public $addressPrefix = "Gablini ";
	public $premisePrefix = "{BRAND} Gablini ";
	
	#Construct
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "Premise";
		$this->model = new \App\Premise($connectionData);
	}

	#Addresses
	public function getAddress($id, $getUsers = true)
	{
		$return = [];
		$return["data"] = $address = $this->model->getAddress($id);
		$return["id"] = $address->id;
		if($id == 5) { $return["name"] = $address->nameOut; }
		else { $return["name"] = $this->addressPrefix.$address->nameOut; }
		$return["address"] = $this->AddressFormat($address->zipCode, $address->city, $address->address);
		$return["gps"] = $address->gpsN.", ".$address->gpsE;
		$return["gpsGoogleMaps"] = "{lat: ".$address->gpsN.", lng: ".$address->gpsE."}";
		$return["email"] = $address->email;
		$return["phone"] = $address->phone;
		$return["phoneLink"] = str_replace(" ", "", $address->phone);
		$return["premises"] = $this->getPremisesByAddress($id);
		
		$return["url"] = "elerhetosegek/".$address->name;
		
		#Brands and Premises
		$return["brands"] = [];
		$return["brandNames"] = [];
		foreach($return["premises"] AS $premiseID => $premise)
		{
			$brand = $premise["brand"];
			$return["brands"][$brand->id] = $brand;
			$return["brandNames"][] = $brand->name;
			if($premiseID == 11) { $return["premises"][$premiseID]["name"] = $return["name"]; }
			elseif($premiseID == 10) { $return["premises"][$premiseID]["name"] = $brand->nameOut." Center ".$address->nameOut; }
			elseif($premiseID == 9) { $return["premises"][$premiseID]["name"] = $brand->nameOut." ".trim($this->addressPrefix); }
			else { $return["premises"][$premiseID]["name"] = str_replace("{BRAND}", $brand->nameOut, $this->premisePrefix).$address->nameOut; }
		}
		$return["brandKeys"] = array_keys($return["brands"]);
		
		#Users
		if($getUsers)
		{
			$users = new \App\Http\Controllers\UserController;
			$return["users"] = $users->getUsersByPosition($id);
		}
		
		return $return;
	}
	
	public function getAddressByName($name)
	{
		$id = $this->model->getAddressByName($name, "id");
		return $this->getAddress($id);
	}
	
	public function getAddresses()
	{
		$return = [];
		$rows = $this->model->getAddresses();
		foreach($rows AS $row) { $return[$row->id] = $this->getAddress($row->id); }
		return $return;
	}
	
	#Premises
	public function getPremise($id)
	{
		$return = [];
		$return["data"] = $premise = $this->model->getPremise($id);
		if(!isset($GLOBALS["CARS"])) { $GLOBALS["CARS"] = new \App\Http\Controllers\CarController; }
		$return["brand"] = $GLOBALS["CARS"]->model->getBrand($premise->brand);
		$return["id"] = $premise->id;
		$return["email"] = $premise->email;
		$return["phone"] = $premise->phone;
		$return["phoneLink"] = str_replace(" ", "", $premise->phone);
		$return["fax"] = $premise->fax;
		$return["faxLink"] = str_replace(" ", "", $premise->fax);
		$return["openingHours"] = [
			"salon" => [
				"name" => "Szalon",
				"nl" => $premise->openSalon,
				"br" => nl2br($premise->openSalon, false),
				"sp" => str_replace("\r\n", " ", $premise->openSalon),
			],
			"service" => [
				"name" => "Szerviz",
				"nl" => $premise->openService,
				"br" => nl2br($premise->openService, false),
				"sp" => str_replace("\r\n", " ", $premise->openService),
			],
			"oldCar" => [
				"name" => "Használtautó",
				"nl" => $premise->openOldCar,
				"br" => nl2br($premise->openOldCar, false),
				"sp" => str_replace("\r\n", " ", $premise->openOldCar),
			],
			"stock" => [
				"name" => "Raktár",
				"nl" => $premise->openStock,
				"br" => nl2br($premise->openStock, false),
				"sp" => str_replace("\r\n", " ", $premise->openStock),
			],
		];
		$return["phones"] = [
			"newCar" => [
				"name" => "Új autó értékesítés",
				"phone" => $premise->phoneNewCar,
				"link" => str_replace(" ", "", $premise->phoneNewCar),
			],
			"service" => [
				"name" => "Szerviz",
				"phone" => $premise->phoneService,
				"link" => str_replace(" ", "", $premise->phoneService),
			],
			"component" => [
				"name" => "Alkatrész",
				"phone" => $premise->phoneComponent,
				"link" => str_replace(" ", "", $premise->phoneComponent),
			],
			"oldCar" => [
				"name" => "Használtautó értékesítés",
				"phone" => $premise->phoneOldCar,
				"link" => str_replace(" ", "", $premise->phoneOldCar),
			],
			"carBody" => [
				"name" => "Karosszéria üzem",
				"phone" => $premise->phoneCarBody,
				"link" => str_replace(" ", "", $premise->phoneCarBody),
			],
		];
		
		return $return;
	}
	
	public function getPremisesByAddress($address)
	{
		$return = [];
		$rows = $this->model->getPremisesByAddress($address);
		foreach($rows AS $row) { $return[$row->id] = $this->getPremise($row->id); }
		return $return;
	}
	
	#Address format
	public function AddressFormat($zipCode = "", $city = "", $address = "", $others = "")
	{
		$return = [];
		if(!empty($zipCode)) { $return[] = $zipCode; }
		if(!empty($city)) { $return[] = $city; }
		if(!empty($address)) { $return[] = $address; }
		if(!empty($others)) { $return[] = $others; }
		
		$return = implode(" ", $return);
		return $return;
	}
}
