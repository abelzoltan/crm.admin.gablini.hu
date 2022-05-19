<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Document extends Base
{
	#Tables
	public function tables($name = "")
	{
		$return = [
			"documents" => $this->dbPrefix."documents",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get all
	public function getDocuments()
	{
		return $this->select("SELECT * FROM ".$this->tables("documents")." WHERE del = '0'");
	}
}
