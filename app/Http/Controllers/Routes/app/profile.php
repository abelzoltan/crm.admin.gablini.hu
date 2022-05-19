<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = "Adatlapom";
$VIEW["vars"]["customerPoints"] = $customers->getPointsByCustomer($customer);
?>