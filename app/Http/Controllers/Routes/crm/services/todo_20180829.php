<?php 
$services = new \App\Http\Controllers\ServiceController;
$customers = new \App\Customer;
if(isset($routes[1]))
{
	$event = $services->getEvent($routes[1]);
	if($event !== false)
	{
		$VIEW["title"] = "Szerviz esemény: ".$event["data"]->worksheetNumber;
		$VIEW["name"] = "services.event-details";
		$VIEW["vars"]["event"] = $event;
	}
	else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
}
else
{
	
	$VIEW["title"] = "Mai teendők";
	$eventList = $services->getEvents([], NULL, "id", 0, $orderBy = "dateSetout DESC, dateClosed DESC, date DESC, id DESC LIMIT 0, 50");
	$VIEW["name"] = "list-panel";
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Mai hívásra megjelölt HYUNDAI ügyfelek",
		"table" => [
			"header" => [
				[
					"name" => "#", 
					"class" => "", 
					"style" => "width: 5%;", 
				],
				[
					"name" => "Ügyfél neve", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Telefonszáma", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Rendszám", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Munkafelvevő", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Munkalapszám", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Dátum", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "1. Kérjük értékelje a márkaszervizt a legutóbbi látogatása során szerzett benyomásai alapján!", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "2. Mennyire elégedett a szerviz-szolgáltatással és a munkafelvevővel?", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "3. Az átadást követően vissza kellett-e mennie a szervizbe az elvégzett munkával kapcsolatos okból?", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "4. Kérjük ossza meg velünk egyéb észrevételeit, javaslatait!", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Belső megjegyzés", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Kérdőív", 
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => ["save"],
			"rows" => [],
		],
	];	
	
	$i = 1;	
	
	foreach($eventList AS $rowID => $row)
	{
		$customer = $customers->getCustomer($row["data"]->customer);
		$car = $customers->getCar($row["data"]->car);
		
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
					"name" => $customer->lastName." ".$customer->firstName,
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $customer->phone,
					"class" => "font-bold", 
					"style" => "", 
				],
				[
					"name" => $car->regNumber,
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["data"]->adminName,
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["data"]->sheetNumber,
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["dateOut"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "<select><option value='0'></option><option value='1'>Elégtelen</option><option value='2'>Elégséges</option><option value='3'>Megfelelő</option><option value='4'>Jó</option><option value='5'>Kiváló</option></select>",
					"class" => "", 
					"style" => "min-width: 200px;", 
				],
				[
					"name" => "<select><option value='0'></option><option value='1'>Elégtelen</option><option value='2'>Elégséges</option><option value='3'>Megfelelő</option><option value='4'>Jó</option><option value='5'>Kiváló</option></select>",
					"class" => "", 
					"style" => "min-width: 200px;", 
				],
				[
					"name" => "<select><option value='0'></option><option value='1'>Igen</option><option value='2'>Nem</option><option value='3'>Alkatrész miatt</option><option value='4'>Még nem, de vissza fogok menni</option></select>",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "<textarea cols='50' rows='3'></textarea>",
					"class" => $class, 
					"style" => "", 
				],
				[
					"name" => "<select><option value='0'></option><option value='nemhivando'>Nem hívandó</option><option value='holnap'>Holnap újra kell hívni</option><option value='jovohet'>Jövő héten újra kell hívni</option><option value='flottas'>Flottás</option><option value='nemhivando'>Ismerjük esélytelen!</option></select>",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $name,
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => [
				"save" => [
					"class" => "primary",
					"icon" => "save",
					"href" => $URL->link([$routes[0], $row["data"]->id]),
					"title" => "Mentés",
				],
			],	
		];
		$i++;
	}
	
	$VIEW["vars"]["LIST2"] = $VIEW["vars"]["LIST"];
	$VIEW["vars"]["LIST2"]["panelName"] = "Mai hívásra megjelölt NISSAN ügyfelek";
}
?>