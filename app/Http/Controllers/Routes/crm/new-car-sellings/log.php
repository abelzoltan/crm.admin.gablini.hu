<?php 
$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
if(isset($routes[1]))
{
	$event = $newcarSellings->getEvent($routes[1]);
	if($event !== false)
	{
		$VIEW["title"] = "Új autó eladás: ".$event["sheetNumber"];
		$VIEW["name"] = "new-car-sellings.event-details";
		$VIEW["vars"]["event"] = $event;
	}
	else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
}
else
{
	$VIEW["name"] = "new-car-sellings.events-log";
	$VIEW["title"] = "Új autó eladások napló";
	$eventList = $newcarSellings->getEventsForLog($_GET);
	
	if(count($eventList) > 0)
	{
		$tableHeader = [];
		foreach($eventList AS $rowID => $row)
		{
			foreach($row["tableDatas"] AS $tableDataKey => $tableDataVal)
			{
				$tableHeader[] = [
					"name" => $tableDataKey, 
					"class" => "", 
					"style" => "", 
				];
			}
			break;
		}
		
		$VIEW["vars"]["LIST"] = [
			"panelName" => "Új autó eladások történet",
			// "dataPageLength" => "-1",
			"table" => [
				"header" => $tableHeader,
				"buttons" => ["details"],
				"rows" => [],
			],
		];	
		
		$i = 1;	
		$customers = new \App\Http\Controllers\CustomerController;
		foreach($eventList AS $rowID => $row)
		{
			$tableRow = [];
			foreach($row["tableDatas"] AS $tableDataKey => $tableDataVal)
			{
				$tableRow[] = [
					"name" => $tableDataVal, 
					"class" => "", 
					"style" => $row["tableRowStyle"], 
				];
			}
			
			$VIEW["vars"]["LIST"]["table"]["rows"][$rowID] = [
				"row" => $row,
				"data" => $row["event"],
				"columns" => $tableRow,
				"buttons" => [
					"details" => [
						"class" => "primary",
						"icon" => "file-text",
						"href" => $URL->link([$routes[0], $row["event"]->id]),
						"title" => "Eladás adatlapja",
					],
				],	
			];
			$i++;
		}
	}
}
?>