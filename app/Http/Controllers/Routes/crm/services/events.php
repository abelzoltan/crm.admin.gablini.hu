<?php 
$services = new \App\Http\Controllers\ServiceController;
if(isset($routes[1]))
{
	$event = $services->getEvent($routes[1]);
	if($event !== false)
	{
		$VIEW["title"] = "Szerviz esemény: ".$event["sheetNumber"];
		$VIEW["name"] = "services.event-details";
		$VIEW["vars"]["event"] = $event;
	}
	else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
}
else
{
	$search = [];
	if(isset($_GET["search"]) AND $_GET["search"])
	{
		if(isset($_GET["sheetNumber"]) AND !empty($_GET["sheetNumber"])) { $search["sheetNumber"] = $_GET["sheetNumber"]; }
		if(isset($_GET["questionnaireAnswered"]) AND !empty($_GET["questionnaireAnswered"])) 
		{ 
			if($_GET["questionnaireAnswered"] == "y") $search["questionnaireAnswered"] = true; 
			elseif($_GET["questionnaireAnswered"] == "n") $search["questionnaireAnswered"] = false; 
		}
		
		if(isset($_GET["dateFrom"]) AND !empty($_GET["dateFrom"])) { $search["dateFrom"] = str_replace(".", "-", $_GET["dateFrom"])." 00:00:00"; }
		if(isset($_GET["dateTo"]) AND !empty($_GET["dateTo"])) { $search["dateTo"] = str_replace(".", "-", $_GET["dateTo"])." 23:59:59"; }
		
		if(isset($_GET["dateSetoutFrom"]) AND !empty($_GET["dateSetoutFrom"])) { $search["dateSetoutFrom"] = str_replace(".", "-", $_GET["dateSetoutFrom"])." 00:00:00"; }
		if(isset($_GET["dateSetoutTo"]) AND !empty($_GET["dateSetoutTo"])) { $search["dateSetoutTo"] = str_replace(".", "-", $_GET["dateSetoutTo"])." 23:59:59"; }
		
		if(isset($_GET["dateClosedFrom"]) AND !empty($_GET["dateClosedFrom"])) { $search["dateClosedFrom"] = str_replace(".", "-", $_GET["dateClosedFrom"])." 00:00:00"; }
		if(isset($_GET["dateClosedTo"]) AND !empty($_GET["dateClosedTo"])) { $search["dateClosedTo"] = str_replace(".", "-", $_GET["dateClosedTo"])." 23:59:59"; }
	}
	else { $search["dateClosedFrom"] = date("Y-m-d 00:00:00", strtotime("-1 week")); }
	$VIEW["vars"]["search"] = $search;

	$VIEW["title"] = "Szerviz események";
	$eventList = $services->getEvents($search);
	
	$VIEW["name"] = "services.events";
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Szerviz események listája",
		// "dataPageLength" => "-1",
		"table" => [
			"header" => [
				[
					"name" => "#", 
					"class" => "", 
					"style" => "width: 5%;", 
				],
				[
					"name" => "Rögzítve", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Típus", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Munkalapszám / Ajánlat sorszáma", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Szerviz munkalap kiállítása", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Lezárás dátuma", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Rendszám", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Kitöltés?", 
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => ["details"],
			"rows" => [],
		],
	];	
	
	$i = 1;	
	$customers = new \App\Http\Controllers\CustomerController;
	foreach($eventList AS $rowID => $row)
	{
		$car = $customers->getCar($row["data"]->car, false);
		if(!empty($row["data"]->questionnaireAnswer))
		{
			$name = "IGEN";
			$class = "font-bold color-green2";
		}
		else
		{
			/*$name = "<a href='".$row["questionnaireLinkUser"]."' target='_blank' style='text-decoration: underline;'>Kitöltés</a>";
			$class = "color-blue";*/
			$name = "Nem";
			$class = "font-italic font-bold color-red";
		}
		
		$VIEW["vars"]["LIST"]["table"]["rows"][$rowID] = [
			"row" => $row,
			"data" => $row["data"],
			"columns" => [	
				[
					"name" => $i.".",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["dateOut"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["typeName"],
					"class" => "font-bold", 
					"style" => "", 
				],
				[
					"name" => $row["sheetNumber"],
					"class" => "font-bold", 
					"style" => "", 
				],
				[
					"name" => $row["dateSetoutPublic"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["dateClosedPublic"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $car["regNumber"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $name,
					"class" => $class, 
					"style" => "", 
				],
			],
			"buttons" => [
				"details" => [
					"class" => "primary",
					"icon" => "file-text",
					"href" => $URL->link([$routes[0], $row["data"]->id]),
					"title" => "Esemény adatlapja",
				],
			],	
		];
		$i++;
	}
}
?>