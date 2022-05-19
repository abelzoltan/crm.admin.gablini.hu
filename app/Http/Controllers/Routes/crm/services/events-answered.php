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
	$search["questionnaireAnswered"] = true;
	if(isset($_GET["search"]) AND $_GET["search"])
	{
		if(isset($_GET["dateFrom"]) AND !empty($_GET["dateFrom"])) { $search["dateFrom"] = str_replace(".", "-", $_GET["dateFrom"])." 00:00:00"; }
		if(isset($_GET["dateTo"]) AND !empty($_GET["dateTo"])) { $search["dateTo"] = str_replace(".", "-", $_GET["dateTo"])." 23:59:59"; }
		
		if(isset($_GET["dateSetoutFrom"]) AND !empty($_GET["dateSetoutFrom"])) { $search["dateSetoutFrom"] = str_replace(".", "-", $_GET["dateSetoutFrom"])." 00:00:00"; }
		if(isset($_GET["dateSetoutTo"]) AND !empty($_GET["dateSetoutTo"])) { $search["dateSetoutTo"] = str_replace(".", "-", $_GET["dateSetoutTo"])." 23:59:59"; }
		
		if(isset($_GET["dateClosedFrom"]) AND !empty($_GET["dateClosedFrom"])) { $search["dateClosedFrom"] = str_replace(".", "-", $_GET["dateClosedFrom"])." 00:00:00"; }
		if(isset($_GET["dateClosedTo"]) AND !empty($_GET["dateClosedTo"])) { $search["dateClosedTo"] = str_replace(".", "-", $_GET["dateClosedTo"])." 23:59:59"; }
	}
	else { $search["dateClosedFrom"] = date("Y-m-01 00:00:00"); }
	$VIEW["vars"]["search"] = $search;

	$VIEW["title"] = "Szerviz események";
	$eventList = $services->getEvents($search);
	
	$VIEW["name"] = "services.events-answered";
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Megválaszolt szerviz események listája",
		// "dataPageLength" => "-1",
		"table" => [
			"header" => [
				[
					"name" => "#", 
					"class" => "", 
					"style" => "width: 5%;", 
				],
				[
					"name" => "Munkatárs", 
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
			],
			"buttons" => ["details"],
			"rows" => [],
		],
	];	
	
	$i = 1;	
	$customers = new \App\Http\Controllers\CustomerController;
	$questionnaires = new \App\Http\Controllers\QuestionnaireController;
	foreach($eventList AS $rowID => $row)
	{
		$qAnswer = $questionnaires->getAnswer($row["data"]->questionnaireAnswer, false);
		if($qAnswer === false OR !$qAnswer["answerByUser"]) { continue; }
		
		$car = $customers->getCar($row["data"]->car, false);
		
		$answerUserRow = $GLOBALS["users"]->model->getUserByID($qAnswer["data"]->user);
		if(!empty($answerUserRow) AND isset($answerUserRow->id) AND !empty($answerUserRow->id)) { $answerUserName = $answerUserRow->lastName." ".$answerUserRow->firstName; }
		else { continue; }
		
		
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
					"name" => $answerUserName,
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