<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Cron extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"cron" => $this->dbPrefix."cron",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
}
