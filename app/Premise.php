<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Premise extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"premises" => $this->dbPrefix."premises",
			"addresses" => $this->dbPrefix."premises_addresses",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Addresses
	public function getAddress($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("addresses"), "id", $id, $field, $delCheck);
	}
	
	public function getAddressByName($name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("addresses"), "name", $name, $field, $delCheck);
	}
	
	public function getAddresses()
	{
		return $this->select("SELECT * FROM ".$this->tables("addresses")." WHERE del = '0' ORDER BY orderNumber");
	}
	
	#Premises
	public function getPremise($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("premises"), "id", $id, $field, $delCheck);
	}
	
	public function getPremises()
	{
		return $this->select("SELECT * FROM ".$this->tables("premises")." WHERE del = '0' ORDER BY address, brand");
	}
	
	public function getPremisesByAddress($address)
	{
		return $this->select("SELECT * FROM ".$this->tables("premises")." WHERE del = '0' AND address = :address ORDER BY address, brand", ["address" => $address]);
	}
}
