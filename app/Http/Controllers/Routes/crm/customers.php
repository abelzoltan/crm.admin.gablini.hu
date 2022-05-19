<?php 
if(isset($routes[1])) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
else
{
	#Stat
	$stat = new \App\Http\Controllers\StatisticController;
	$VIEW["vars"]["customerCount"] = $stat->customerCount();
	$VIEW["vars"]["customersFromWhere"] = $stat->customersFromWhere();
	
	$jump = $search = [];
	
	#Jump to details
	if(isset($_GET["jump"]) AND $_GET["jump"])
	{
		$fields = [
			"id" => "getCustomer", 
			"code" => "getCustomerByCode", 
			"token" => "getCustomerByToken", 
			"email" => "getCustomerByEmail",
		];		
		$newGet = $_GET;
		$redirect = false;
		$jump["jump"] = 0;
		foreach($fields AS $field => $functionName) 
		{
			if(isset($_GET[$field])) 
			{ 
				#If OK => Redirect ------------------
				if($field == "code") { $val = CUSTOMER_CODEBEFORE.$_GET[$field]; }
				else { $val = $_GET[$field]; }
				$id = $customers->model->$functionName($val, "id");
				if(!empty($id)) { $URL->redirect(["customer", $id]); }
				#End --------------------------------
				
				$jump["jump"] = 1;
				$jump[$field] = $_GET[$field];
				if(empty($_GET[$field])) 
				{ 
					$redirect = true; 
					unset($newGet[$field]);
				} 
			}
			else { $jump[$field] = "";  }
		}
		if(!$jump["jump"]) { $URL->redirect($routes); }
		elseif($redirect) { $URL->redirect($routes, $newGet); }
	}	
	#Search
	elseif(isset($_GET["search"]) AND $_GET["search"])
	{
		$fields = ["name", "companyName", "user", "email", "phone", "code"];		
		$newGet = $_GET;
		$redirect = false;
		$search["search"] = 0;
		foreach($fields AS $field) 
		{
			if(isset($_GET[$field])) 
			{ 
				$search["search"] = 1;
				$search[$field] = $_GET[$field];
				if(empty($_GET[$field])) 
				{ 
					$redirect = true; 
					unset($newGet[$field]);
				} 
			}
			else { $search[$field] = "";  }
		}
		if(!$search["search"]) { $URL->redirect($routes); }
		elseif($redirect) { $URL->redirect($routes, $newGet); }
		else 
		{ 
			$rows = $customers->getCustomersForList($search); 
			$VIEW["title"] = "Ügyfelek kezelése";
			$VIEW["vars"]["newButton"]["label"] = "Új ügyfél létrehozása";
			$VIEW["vars"]["LIST"] = [
				"panelName" => "Találatok listája",
				"table" => [
					"header" => [
						[
							"name" => "#", 
							"class" => "", 
							"style" => "width: 5%;", 
						],
						[
							"name" => CUSTOMER_CODENAME, 
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => "Név", 
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => "E-mail cím", 
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => "Cégnév", 
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => "Telefonszám", 
							"class" => "", 
							"style" => "", 
						],
					],
					"buttons" => ["edit", "del"],
					"rows" => [],
				],
			];
			
			$i = 1;	
			foreach($rows AS $rowID => $row)
			{
				$VIEW["vars"]["LIST"]["table"]["rows"][$row["data"]->id] = [
					"row" => $row,
					"data" => $row["data"],
					"columns" => [	
						[
							"name" => $i.".",
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => $row["code"],
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => $row["name"],
							"class" => "font-medium", 
							"style" => "", 
						],
						[
							"name" => $row["email"],
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => $row["companyName"],
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => $row["phone"],
							"class" => "", 
							"style" => "", 
						],
					],
					"buttons" => [],	
				];
				$i++;
			}
		}
	}

	#View
	$VIEW["vars"]["jump"] = $jump;
	$VIEW["vars"]["search"] = $search;
	$VIEW["name"] = "customers.list";
}
?>