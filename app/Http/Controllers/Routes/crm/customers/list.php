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
			$VIEW["vars"]["customerRows"] = $rows;
			$VIEW["title"] = "Ügyfelek kezelése";
			$VIEW["vars"]["newButton"]["label"] = "Új ügyfél létrehozása";	
		}
	}

	#View
	$VIEW["vars"]["usersForSearch"] = $customers->getUserListForSearch();
	$VIEW["vars"]["jump"] = $jump;
	$VIEW["vars"]["search"] = $search;
	$VIEW["name"] = "customers.list";
}
?>