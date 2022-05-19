<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Statistic extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			// "statistics" => $this->dbPrefix."statistics",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
}
