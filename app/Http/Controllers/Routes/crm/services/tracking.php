<?php 
if(isset($routes[1]))
{
	if($routes[1] == "work-save")
	{
		#Get event
		$eventID = (isset($_POST["eventID"]) AND !empty($_POST["eventID"])) ? $_POST["eventID"] : false;
		if($eventID != false)
		{
			#Get datas
			if(isset($_POST) AND !empty($_POST) AND isset($_POST["phone"]) AND !empty($_POST["phone"]))
			{
				$services = new \App\Http\Controllers\ServiceController;
				$customers = new \App\Http\Controllers\CustomerController;
				
				$eventRow = $services->model->getEvent($eventID);
				$customerID = $eventRow->customer;
				$customer = $customers->getCustomer($customerID, false);
				
				if($customer != false)
				{
					#Process
					$customers->changeCustomer($customerID, ["phone" => $_POST["phone"]], $customer["data"]);
					$customers->changeCustomerPhone($customer["data"]->progressCode, $_POST["phone"], "phone");
					$services->editEvent($eventID, ["wrongNumberClosed" => 1]);
					$dateClosedCheck = date("Y-m-d", strtotime("-10 days"));
					if($eventRow->dateClosed > $dateClosedCheck) { $services->editEvent($eventID, ["status" => NULL, "adminTodoSuccess" => 0, "adminTodo" => 1]); }
					$ajaxMsg = "ok";
					
					#Other events of customer
					$events = $services->getEventsForTracking();
					if(count($events > 0))
					{
						foreach($events AS $event)
						{
							if($event["data"]->customer == $customerID AND $event["data"]->id != $eventID) 
							{ 
								$services->editEvent($event["data"]->id, ["wrongNumberClosed" => 1]); 
								if($event["data"]->dateClosed > $dateClosedCheck) { $services->editEvent($event["data"]->id, ["status" => NULL, "adminTodoSuccess" => 0, "adminTodo" => 1]); }
							}
						}
					}
				}
				else { $ajaxMsg = "Az ügyfél nem azonosítható!"; }	
			}
			else { $ajaxMsg = "A telefonszám megadása kötelező!"; }	
		}
		else { $ajaxMsg = "A szerviz esemény nem azonosítható!"; }
		
		echo $ajaxMsg;
		exit;		
	}
	elseif($routes[1] == "work-close")
	{
		#Get event
		$eventID = (isset($_POST["eventID"]) AND !empty($_POST["eventID"])) ? $_POST["eventID"] : false;
		if($eventID != false)
		{
			$services = new \App\Http\Controllers\ServiceController;
			$customers = new \App\Http\Controllers\CustomerController;
			
			$customerID = $services->model->getEvent($eventID, "customer");
			$customer = $customers->getCustomer($customerID, false);
			
			if($customer != false)
			{
				#Process
				$services->editEvent($eventID, ["wrongNumberClosed" => 1]);
				$ajaxMsg = "ok";
				
				#Other events of customer
				$events = $services->getEventsForTracking();
				if(count($events > 0))
				{
					foreach($events AS $event)
					{
						if($event["data"]->customer == $customerID AND $event["data"]->id != $eventID) { $services->editEvent($event["data"]->id, ["wrongNumberClosed" => 1]); }
					}
				}
			}
			else { $ajaxMsg = "Az ügyfél nem azonosítható!"; }	
		}
		else { $ajaxMsg = "A szerviz esemény nem azonosítható!"; }	
		
		echo $ajaxMsg;
		exit;
	}
	elseif($routes[1] == "work-rows-check")
	{
		#Get admin todo list
		$services = new \App\Http\Controllers\ServiceController;
		$eventIDList = [];
		$eventList = $services->getEventsForTracking();
		foreach($eventList AS $eventKey => $event) { $eventIDList[] = $event["id"]; }
		
		#Output
		echo json_encode($eventIDList);
		exit;
	}
	else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
}
else
{
	#Set view data
	$VIEW["title"] = "Telefonszám nyomozás";
	$VIEW["name"] = "services.tracking";
	$VIEW["vars"]["navSM"] = true;
	
	#Table header		
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Telefonszám nyomozás",
		// "order" => ["column" => 0, "type" => "desc"],
		"dataPageLength" => 5,
		"table" => [
			"id" => "service-todo-table",
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
					"name" => "Telefonszámok", 
					"class" => "", 
					"style" => "width: 85px;", 
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
					"name" => "Kinyomozott telefonszám", 
					"class" => "no-sort", 
					"style" => "width: 180px;", 
				],
			],
			"buttons" => ["save", "close"],
			"rows" => [],
		],
	];
	
	#Get event lists and questionnaires
	$services = new \App\Http\Controllers\ServiceController;
	$eventStatuses = $services->getEventStatuses(1);
	$eventList = $services->getEventsForTracking();
	
	#Loop events
	$i = 1;
	foreach($eventList AS $rowID => $row)
	{
		$customer = $customers->getCustomer($row["data"]->customer, false);
		$car = $customers->getCar($row["data"]->car, false);
		
		$statusList = '';
		$allDetails = $services->jsonDecode($row["data"]->allDetails);
		
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
					"name" => $customer["name"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => '
						<small>
							K: '.$allDetails["Készrejelentés"].'<br>
							M: '.$allDetails["Mobil"].'<br>
							T: '.$allDetails["Telefon 1"].'
						</small>
					',
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
					"name" => '<input type="text" class="form-control services-todo-phone-'.$row["data"]->id.'" name="phones['.$row["data"]->id.']" placeholder="Új telefonszám" style="display: block; width: 100%; min-width: 150px;">',
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => [
				"save" => [
					"class" => "primary",
					"icon" => "save",
					"href" => "javascript:void(0)",
					"onclick" => "saveEvent('".$row["id"]."')",
					"title" => "Mentés",
				],
				"close" => [
					"class" => "danger",
					"icon" => "times",
					"href" => "javascript:void(0)",
					"onclick" => "closeEvent('".$row["id"]."')",
					"title" => "Lezárás",
				],
			],	
		];
		$i++;
	}
}
?>