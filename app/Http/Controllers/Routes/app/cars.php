<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

#Cars
$VIEW["vars"]["cars"] = $customers->getCarsByCustomer($customer["id"]);

$cars = new \App\Http\Controllers\CarController;
$VIEW["vars"]["fuels"] = $cars->getFuels();
?>