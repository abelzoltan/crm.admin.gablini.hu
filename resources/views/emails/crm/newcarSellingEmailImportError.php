<?php
$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"fileName" => $emailData["fileName"],
	"errorMessage" => $emailData["errorMessage"],
	"date" => date("Y. m. d. H:i"),
];

$emailController->subject = "[GABLINI-CRM] Új autó átadás - Importálás HIBA: ".$emailData["errorMessage"];
$emailController->body = $emailController->setBody("crm/newcar_sellings_import_error");
$emailController->addresses = [	
	["type" => "to", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "to", "email" => "gyorgy@potocki.hu", "name" => "Potocki György"],
	["type" => "to", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
?>