<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

#Cars
$brands = [];
$VIEW["vars"]["cars"] = $customers->getCarsByBrandsByCustomer($customer["id"]);
foreach($VIEW["vars"]["cars"] AS $brand => $cars)
{
	if(count($cars) > 0) { $brands[] = $brand; }
}

$promotions = new \App\Http\Controllers\ServicePromotionController;
$VIEW["vars"]["promotions"] = $promotions->getActivePromotions($brands);
?>