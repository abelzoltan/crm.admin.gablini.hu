<?php
#Datas
$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
];

$emailController->attachments = [["path" => $fileName, "name" => "Előző heti statisztika.csv"]];

#Sending datas
$emailController->subject = "[GABLINI-CRM] Heti statisztika - Nem szeretne nyilatkozni";
$emailController->body = $emailController->setBody("crm/service_weekly_admin_noanswer");

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL("crm-szerviz-nem-szeretne-nyilatkozni");
$emailController->addresses = $addressList["all"];

$emailController->send();
// echo $emailController->watch();
?>