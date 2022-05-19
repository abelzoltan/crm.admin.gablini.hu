<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Address;

class AddressController extends BaseController
{
	public $googleMapsZoom = 15;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Address";
		$this->model = new \App\Address($connectionData);
	}
	
	public function getAddress($id)
	{
		$return = [];
		$return["data"] = $address = $this->model->getAddress($id);
		$return["type"] = $this->model->getType($address->cityID);
		$return["city"] = $this->getCity($address->cityID);
		
		$return["address"] = $this->AddressFormat($address->zipCode, $address->city, $address->address, $address->others);
		$return["addressComment"] = $address->comment;
		
		if(!empty($address->comment)) { $return["addressWithComment"] = $return["address"]." (".$return["addressComment"].")"; }
		else { $return["addressWithComment"] = $return["address"]; }
		
		return $return;
	}
	
	public function getCity($id)
	{
		$return = [];
		$return["data"] = $city = $this->model->getCity($id);
		$return["region"] = $region = $this->model->getRegion($city->region);
		$return["country"] = $this->model->getCountry($region->country);
		$return["gps"] = $city->gps1.",".$city->gps2;
		$return["googleMaps"] = "https://www.google.com/maps/@".$return["gps"].",".$this->googleMapsZoom."z";
		//continent
		
		return $return;
	}
	
	public function getCities($countryCode = "HU")
	{
		$return = [];	
		$rows = $this->model->getCities($countryCode);
		foreach($rows AS $id) { $return[$id] = $this->getCity($id); }	
		return $return;
	}
	
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
	
	public function getCityByZipCode($zipCode, $city = NULL)
	{	
		$rows = $this->model->getCitiesByZipCode($zipCode);
		if(count($rows) > 0)
		{
			$return = $rows[0];
			if($city !== NULL AND count($rows) > 1)
			{
				foreach($rows AS $row)
				{
					if($row->name == $city) { $return = $row; }
				}
			}
		}
		else { $return = false; }
		return $return;
	}
	
	public function getCityByZipCode2($zipCode, $field = "", $delCheck = 1)
	{
		return $this->model->getCityByZipCode($zipCode, $field, $delCheck);
	}
	
	public function newAddress($typeName, $foreignKey, $cityID, $datas)
	{
		$type = $this->model->getTypeByURL($typeName, "id");
		return $this->model->newAddress($type, $foreignKey, $cityID, $datas);
	}
}
?>