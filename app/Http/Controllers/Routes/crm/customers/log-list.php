<?php
if(count($customer["log"]) > 0)
{
	$tableHeader = [];
	if(isset($logLimit) AND $logLimit > 0)
	{
		$tableHeader[] = [
			"name" => "#", 
			"class" => "", 
			"style" => "width: 5%;", 
		];
	}
	foreach(array_values($customer["log"])[0]["output"] AS $key => $item) 
	{
		$tableHeader[] = [
			"name" => $item["name"], 
			"class" => "", 
			"style" => "", 
		];
	}
	
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Ügyfél eseménynapló",
		"table" => [
			"header" => $tableHeader,
			"rows" => [],
		],
	];	

	$i = 1;	
	foreach($customer["log"] AS $rowID => $row)
	{								
		$columns = [];
		if(isset($logLimit) AND $logLimit > 0)
		{
			$columns[] = [
				"name" => $i.".", 
				"class" => "", 
				"style" => "", 
			];
		}
		foreach($row["output"] AS $key => $item) 
		{
			$columns[] = [
				"name" => $item["val"], 
				"class" => "", 
				"style" => "", 
			];
		}
		$VIEW["vars"]["LIST"]["table"]["rows"][$rowID] = [
			"row" => $row,
			"data" => $row["data"],
			"columns" => $columns,	
		];
		
		if(isset($logLimit) AND $logLimit > 0 AND $i >= $logLimit) { break; }
		$i++;
	}
	
	if(isset($logLimit) AND $logLimit > 0) {  }
	else { $VIEW["vars"]["LIST"]["order"] = ["column" => 0, "type" => "desc"]; }
}
?>