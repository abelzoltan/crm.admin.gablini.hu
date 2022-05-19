<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

$VIEW["vars"]["customerPoints"] = $customers->getPointsByCustomer($customer["id"]);
?>