<?php
#View
$VIEW["name"] = "home";
$VIEW["title"] = "Kezdőlap";

$VIEW["vars"]["customerPoints"] = $customers->getPointsByCustomer($customer["id"]);

$promotions = new \App\Http\Controllers\ServicePromotionController;
$VIEW["vars"]["promotions"] = $promotions->getActivePromotions($brands);
?>