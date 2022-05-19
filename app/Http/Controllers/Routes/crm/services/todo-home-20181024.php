<?php 
if(isset($routes[1]) AND empty($routes[1])) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
else
{
	#Set view data
	$VIEW["title"] = "Mai teendők listája";
	$VIEW["name"] = "services.todo-home";
	$VIEW["vars"]["navSM"] = true;
	
	#Table header		
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Mai hívásra megjelölt ügyfelek",
		// "order" => ["column" => 0, "type" => "desc"],
		"dataPageLength" => 10,
		"table" => [
			"header" => [
				[
					"name" => "#",
					"class" => "", 
					"style" => "width: 40px;", 
				],
				[
					"name" => "Gyártmány", 
					"class" => "", 
					"style" => "width: 80px;", 
				],
				[
					"name" => "Dátum", 
					"class" => "", 
					"style" => "width: 58px;",
				],
				[
					"name" => "Partner", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Modell", 
					"class" => "", 
					"style" => "width: 70px;", 
				],
				[
					"name" => "Rendszám", 
					"class" => "", 
					"style" => "width: 70px;",
				],				
				[
					"name" => "Hiba", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Telephely", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Belső megjegyzés és állapot", 
					"class" => "no-sort printing-hidden", 
					"style" => "width: 180px;", 
				],
			],
			"buttons" => ["save", "answer"],
			"rows" => [],
		],
	];
	
	#Event list
	$fullEventList = [];
	$services = new \App\Http\Controllers\ServiceController;
	$questionnaires = [5, 2, 6, 4, 3, 7]; // Peugeot, Nissan, Infiniti, Kia, Hyundai, General
	foreach($questionnaires AS $qID)
	{
		$eventList = $services->getEventsForTodoList($qID);
		$fullEventList = array_merge($fullEventList, $eventList);
	}
	$eventStatuses = $services->getEventStatuses(1);
	
	$i = 1;
	foreach($fullEventList AS $rowID => $row)
	{
		$customer = $customers->getCustomer($row["data"]->customer, false);
		$car = $customers->getCar($row["data"]->car, false);
		
		$statusList = '<option value="">(Állapot)</option>';
		foreach($eventStatuses AS $eventStatusKey => $eventStatus) 
		{
			$statusList .= '<option value="'.$eventStatus["id"].'"';
			// if($row["statusID"] == $eventStatus["id"]) { $statusList .= ' selected'; }
			$statusList .= '>'.$eventStatus["name"].'</option>';
		}
		
		$VIEW["vars"]["LIST"]["table"]["rows"][$row["id"]] = [
			"row" => $row,
			"data" => $row["data"],
			"class" => "panel-row-service-todo-".strtolower($car["brand"]),
			"columns" => [	
				[
					"name" => $i.".",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $car["brand"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["adminTodoDateOut"],
					"class" => "", 
					"style" => "", 
				],			
				[
					"name" => $customer["name"]."<br><strong>".$customer["phone"]."<br>".$customer["mobile"]."</strong>",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => trim($car["model"], "'"),
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $car["regNumber"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["text"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["premise"],
					"class" => "", 
					"style" => "", 
				],			
				[
					"name" => '
						<textarea rows="1" class="form-control services-todo-comment-'.$row["data"]->id.'" name="datas['.$row["data"]->id.'][innerComment]" placeholder="Megjegyzés" style="display: block; width: 100%; min-width: 150px; margin-bottom: 5px;"></textarea>
						<select class="form-control services-todo-status services-todo-status-'.$row["data"]->id.' name="datas['.$row["data"]->id.'][status]" style="display: block; width: 100%; min-width: 150px;">'.$statusList.'</select>
					',
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => [
				"save" => [
					"class" => "primary",
					"icon" => "save",
					"href" => "javascript:void(0)",
					"onclick" => "serviceStatusSave('".$row["id"]."')",
					"title" => "Mentés",
				],
				"answer" => [
					"class" => "success",
					"icon" => "pencil-square-o",
					"href" => $row["questionnaireLinkUser"],
					"target" => "_blank",
					"title" => "Kérdőív kitöltése",
				],
			],	
		];
		$i++;
	}
}
?>