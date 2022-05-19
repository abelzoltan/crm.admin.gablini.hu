<?php 
if(isset($routes[1]) AND !empty($routes[1])) 
{
	switch($routes[1])
	{
		case "download":
			$services = new \App\Http\Controllers\ServiceController;
			$export = $services->mitoExport($_GET);
			// exit;
			if($export === false) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
			else
			{
				#Filename and settings
				header("Content-Type: text/csv; charset=".$export["charset"]);
				header("Content-Disposition: attachment; filename=".$export["fileName"]);
				$output = fopen("php://output", "w");
				
				#Content
				fputcsv($output, $export["headerRow"], ";");
				foreach($export["rows"] AS $row) { fputcsv($output, $row, ";"); }
			}		
			exit;
			break;
		default:
			$URL->redirect([$routes[0]], ["error" => "unknown"]);
			break;
	}
}
else
{
	$VIEW["title"] = "Márka exportok letöltése";
	$VIEW["name"] = "services.exports";
	
	$brands = ["hyundai", "kia", "nissan", "peugeot", "citroen", "infiniti", "general"];				
	$VIEW["vars"]["brands"] = [];				
	foreach($brands AS $brand)
	{
		#Premises
		$services = new \App\Http\Controllers\ServiceController;
		$brandParam = ($brand == "general") ? "" : $brand;
		$premises = $services->model->getEventPremises2($brandParam);
		
		#Basic get data
		$get1 = $get2 = $get3 = $get4 = ["brand" => $brand, "dateFrom" => date("Y-m-d 00:00:00", strtotime("-5 days"))];
		$get2["dateFrom"] = date("Y-m-d 00:00:00", strtotime("-10 days"));
		
		#Changing get datas
		$startDate = "last monday";
        if(date("N") != "1") { $startDate .= " -7 days"; }
		$get3["dateFrom"] = date("Y-m-d 00:00:00", strtotime($startDate));
		$get3["dateTo"] = date("Y-m-d 23:59:59", strtotime($get3["dateFrom"]." + 6 days"));
		$get3Name = date("Y. m. d.", strtotime($get3["dateFrom"]))." - ".date("m. d.", strtotime($get3["dateTo"]));
		
		$get4["dateFrom"] = date("Y-m-01 00:00:00", strtotime("last month"));
		$get4["dateTo"] = date("Y-m-d 23:59:59", strtotime("last day of last month"));
		$get4Name = strftime("%Y. %B", strtotime($get4["dateFrom"]));
		
		#Array for view
		$link = [$routes[0], "download"];
		$VIEW["vars"]["brands"][$brand] = [
			"name" => ($brand == "general") ? "Márkafüggetlen" : ucfirst($brand),
			"link" => $link,
			"premises" => $premises,
			"buttons" => [
				"link1" => [
					"link" => $URL->link($link, $get1),
					"get" => $get1,
					"class" => "primary",
					"name" => "Elmúlt 5 nap",
					"premises" => [],
				],
				"link2" => [
					"link" => $URL->link($link, $get2),
					"get" => $get2,
					"class" => "dark",
					"name" => "Elmúlt 10 nap",
					"premises" => [],
				],
				"link3" => [
					"link" => $URL->link($link, $get3),
					"get" => $get3,
					"class" => "info",
					"name" => "Előző hét (".$get3Name.")",
					"premises" => [],
				],
				"link4" => [
					"link" => $URL->link($link, $get4),
					"get" => $get4,
					"class" => "success",
					"name" => "Előző hónap (".$get4Name.")",
					"premises" => [],
				],
			],
		];
		
		foreach($VIEW["vars"]["brands"][$brand]["buttons"] AS $btnKey => $btnData)
		{
			$VIEW["vars"]["brands"][$brand]["buttons"][$btnKey]["premises"][] = [
				"link" => $URL->link($link, $btnData["get"]),
				"get" => $btnData["get"],
				"class" => "danger",
				"name" => "ÖSSZES",
			];
			
			foreach($premises AS $premise)
			{
				$premiseGet = $btnData["get"];
				$premiseGet["premise"] = $premise->premise;
				$VIEW["vars"]["brands"][$brand]["buttons"][$btnKey]["premises"][] = [
					"link" => $URL->link($link, $premiseGet),
					"get" => $premiseGet,
					"class" => "warning",
					"name" => $premiseGet["premise"],
				];
			}
		}
	}
}
?>