<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

#Cars
$VIEW["vars"]["cars"] = $customers->getCarsByCustomer($customer["id"]);

#Service events
$services = new \App\Http\Controllers\ServiceController;
$VIEW["vars"]["serviceEvents"] = $services->getEvents([], $customer["id"], "id", 0, "dateClosed DESC");

#Points
$VIEW["vars"]["customerPoints"] = $customers->getPointsByCustomer($customer["id"]);
?>