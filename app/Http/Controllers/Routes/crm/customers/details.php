<?php 
if(!isset($routes[1])) { $URL->redirect(["customers"], ["error" => "customer-not-exists"]); }
else
{
	$customer = $customers->getCustomer($routes[1]);
	if($customer === false) { $URL->redirect(["customers"], ["error" => "customer-not-exists"]); }
	elseif($customer["data"]->del) { $URL->redirect(["customers"], ["error" => "customer-not-exists"]); }
	else
	{
		$VIEW["title"] = $customer["name"]." adatlapja";
		$VIEW["vars"]["customer"] = $customer;
		$VIEW["vars"]["baseURL"] = $baseURL = $URL->link([$routes[0], $routes[1]]);
		
		if(isset($routes[2]))
		{
			switch($routes[2])
			{				
				case "del":
					$redirect = PATH_WEB."customers?";
					if(isset($_GET["getData"]) AND !empty($_GET["getData"])) { $redirect .= $_GET["getData"]."&"; }
					
					if(!isset($_GET["work"]) OR !isset($_GET["id"])) { $URL->header($redirect."error=unknown"); }
					elseif($_GET["work"] != $routes[2] OR $_GET["id"] != $routes[1]) { $URL->header($redirect."error=unknown"); }
					elseif(!isset($_GET["reason"]) OR empty($_GET["reason"])) { $URL->header($redirect."error=del-reason"); }
					else
					{
						$customers->delCustomer($_GET["id"], $_GET["reason"]);
						$URL->header($redirect."success=".$routes[2]);
					}
					break;
				case "edit":	
					$VIEW["name"] = "customers.details";
					break;
				case "email":
					$VIEW["name"] = "customers.details";
					break;	
				case "log":
					include("log-list.php");
					$VIEW["vars"]["backButton"] = [
						"label" => "Vissza az adatlapra",
						"href" => $baseURL,
					];
					$VIEW["name"] = "customers.log";
					break;
				case "contacts":
					$VIEW["name"] = "customers.details";
					break;
				case "comments":
					$VIEW["name"] = "customers.details";
					break;
				case "cars":	
					$VIEW["name"] = "customers.details";
					break;
				case "addresses":	
					$VIEW["name"] = "customers.details";
					break;
				case "imported-datas":
					$VIEW["name"] = "customers.details";
					break;	
				case "service-events":
					$VIEW["name"] = "customers.details";
					break;		
				default:
					$URL->redirect([$routes[0], $routes[1]], ["error" => "unknown"]);
					break;
			}
		}
		else
		{
			$VIEW["name"] = "customers.details";
			$VIEW["vars"]["backButton"]["label"] = "Vissza a listára";
			$VIEW["vars"]["backButton"]["href"] = PATH_WEB."customers";
			
			$logLimit = 20;
			include("log-list.php");
			$VIEW["vars"]["logList"] = $VIEW["vars"]["LIST"];
			$VIEW["vars"]["logList"]["panelName"] = "Utolsó ".$logLimit." naplóesemény";
		}
	}
}
?>