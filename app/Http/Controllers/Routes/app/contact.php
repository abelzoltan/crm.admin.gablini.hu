<?php
#View
$VIEW["name"] = $routes[0];
$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

$premises = new \App\Http\Controllers\PremiseController;
$this->MAIN["premises"] = $premises->getAddresses(false);
unset($this->MAIN["premises"][7]); // Enying
?>