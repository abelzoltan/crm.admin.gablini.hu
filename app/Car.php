<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Car extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"cars" => $this->dbPrefix."cars",
			"brands" => $this->dbPrefix."cars_brands",
			"categories" => $this->dbPrefix."cars_categories",
			"datas" => $this->dbPrefix."cars_datas",
				"dataCategories" => $this->dbPrefix."cars_datas_categories",
				"dataFacilities" => $this->dbPrefix."cars_datas_facilites",
				"dataTypes" => $this->dbPrefix."cars_datas_types",
			"descriptions" => $this->dbPrefix."cars_descriptions",
			"facilities" => $this->dbPrefix."cars_facilites",
			"models" => $this->dbPrefix."cars_models",
			"motors" => $this->dbPrefix."cars_motors",
				"motorDatas" => $this->dbPrefix."cars_motors_datas",
				"fuels" => $this->dbPrefix."cars_motors_fuels",
			"wheels" => $this->dbPrefix."cars_wheels",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get car and prices
	public function getCar($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("cars"), "id", $id, $field, $delCheck);
	}
	
	public function getCarByURL($url, $brand, $field = "", $visible = 1, $deleted = 0)
	{
		$params = [
			"brand" => $brand,
			"name" => $url,
		];
		$query = "SELECT * FROM ".$this->tables("cars")." WHERE brand = :brand AND name = :name";
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if($visible !== NULL)
		{
			$query .= " AND visible = :visible";
			$params["visible"] = $visible;
		}
		
		$rows = $this->select($query, $params);
		if(count($rows) == 0) { $return = new \stdClass(); }
		
		if(!empty($field)) { $return = $rows[0]->$field; }
		else { $return = $rows[0]; }
		
		return $return;
	}
	
	public function getCarsByBrand($brand, $visible = NULL, $deleted = 0, $orderNumber = "category, orderNumber")
	{
		$params = ["brand" => $brand];
		$query = "SELECT * FROM ".$this->tables("cars")." WHERE brand = :brand";
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if($visible !== NULL)
		{
			$query .= " AND visible = :visible";
			$params["visible"] = $visible;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	public function getPriceFrom($car)
	{
		$return = [];
		$return["originalRows"] = $this->select("SELECT priceOriginal, isNet FROM ".$this->tables("models")." WHERE car = :car AND del = '0' AND active = '1' AND priceOriginal IS NOT NULL AND priceOriginal > '0' ORDER BY priceOriginal LIMIT 0, 1", ["car" => $car]);
		$return["saleRows"] = $this->select("SELECT priceSale, isNet FROM ".$this->tables("models")." WHERE car = :car AND del = '0' AND active = '1' AND priceSale IS NOT NULL AND priceSale > '0' ORDER BY priceSale LIMIT 0, 1", ["car" => $car]);
		
		return $return;
	}
	
	#Get brand
	public function getBrand($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("brands"), "id", $id, $field, $delCheck);
	}
	
	public function getBrandByName($name, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("brands"), "name", $name, $field, $delCheck);
	}
	
	public function getBrands($deleted = 0, $orderNumber = "orderNumber")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("brands")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get category
	public function getCategory($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("categories"), "id", $id, $field, $delCheck);
	}
	
	public function getCategories($brand, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["brand" => $brand];
		$query = "SELECT * FROM ".$this->tables("categories")." WHERE brand = :brand";

		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get facility
	public function getFacility($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("facilities"), "id", $id, $field, $delCheck);
	}
	
	public function getFacilities($car, $active = NULL, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["car" => $car];
		$query = "SELECT * FROM ".$this->tables("facilities")." WHERE car = :car";

		if($active !== NULL)
		{
			$query .= " AND active = :active";
			$params["active"] = $active;
		}
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get motor
	public function getMotor($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("motors"), "id", $id, $field, $delCheck);
	}
	
	public function getMotors($car, $active = NULL, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["car" => $car];
		$query = "SELECT * FROM ".$this->tables("motors")." WHERE car = :car";

		if($active !== NULL)
		{
			$query .= " AND active = :active";
			$params["active"] = $active;
		}
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	public function getMotorDatas($motor, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["motor" => $motor];
		$query = "SELECT * FROM ".$this->tables("motorDatas")." WHERE motor = :motor";
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get motor fuel
	public function getFuel($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("fuels"), "id", $id, $field, $delCheck);
	}
	
	public function getFuels($deleted = 0, $orderNumber = "orderNumber")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("fuels")." WHERE id != '0'";

		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get model
	public function getModel($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("models"), "id", $id, $field, $delCheck);
	}
	
	public function getModelByToken($token, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("models"), "token", $token, $field, $delCheck);
	}
	
	public function getModels($car, $active = NULL, $deleted = 0, $orderNumber = "motor, facility, priceOriginal, priceSale")
	{
		$params = ["car" => $car];
		$query = "SELECT * FROM ".$this->tables("models")." WHERE car = :car";

		if($active !== NULL)
		{
			$query .= " AND active = :active";
			$params["active"] = $active;
		}
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get data-categories
	public function getDataCategory($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("dataCategories"), "id", $id, $field, $delCheck);
	}
	
	public function getDataCategories($car, $active = NULL, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["car" => $car];
		$query = "SELECT * FROM ".$this->tables("dataCategories")." WHERE car = :car";

		if($active !== NULL)
		{
			$query .= " AND active = :active";
			$params["active"] = $active;
		}
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get datas by car & category
	public function getData($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("datas"), "id", $id, $field, $delCheck);
	}
	
	public function getDatas($car, $category = NULL, $deleted = 0, $orderNumber = "orderNumber")
	{
		$params = ["car" => $car];
		$query = "SELECT * FROM ".$this->tables("datas")." WHERE car = :car";

		if($category !== NULL)
		{
			$query .= " AND category = :category";
			$params["category"] = $category;
		}
		
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get data types
	public function getDataType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("dataTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getDataTypes($deleted = 0, $orderNumber = "orderNumber")
	{
		$params = [];
		$query = "SELECT * FROM ".$this->tables("dataTypes")." WHERE id != '0'";

		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get data facilities
	public function getDataFacilityRow($data, $facility)
	{
		return $this->select("SELECT * FROM ".$this->tables("dataFacilities")." WHERE del = '0' AND data = :data AND facility = :facility", ["data" => $data, "facility" => $facility]);
	}
	
	public function getDataFacilities($data, $facility = NULL, $type = NULL, $deleted = 0, $orderNumber = "facility, id")
	{
		$params = ["data" => $data];
		$query = "SELECT * FROM ".$this->tables("dataFacilities")." WHERE data = :data";

		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if($facility !== NULL)
		{
			$query .= " AND facility = :facility";
			$params["facility"] = $facility;
		}
		
		if($type !== NULL)
		{
			$query .= " AND type = :type";
			$params["type"] = $type;
		}
		
		$query .= " ORDER BY ".$orderNumber;
		
		return $this->select($query, $params);
	}
	
	#Get descriptions
	public function getDescription($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("descriptions"), "id", $id, $field, $delCheck);
	}
	
	public function getDescriptionByCar($carID)
	{
		$rows = $this->select("SELECT * FROM ".$this->tables("descriptions")." WHERE del = '0' AND car = :car", ["car" => $carID]);
		if(count($rows) > 0) { $return = $rows[0]; }
		else { $return = false; }
		
		return $return;
	}
	
	#Get wheel
	public function getWheel($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("wheels"), "id", $id, $field, $delCheck);
	}
}
