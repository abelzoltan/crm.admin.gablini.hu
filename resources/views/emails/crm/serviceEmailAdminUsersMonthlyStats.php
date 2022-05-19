<?php
#Datas
$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
];

$emailController->attachments = [["path" => $attachment, "name" => NULL]];

#Sending datas
$emailController->subject = "[GABLINI-CRM] Havi statisztika a munkatársakról";
$emailController->body = $emailController->setBody("crm/service_monthly_admin_users_stats");

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL("crm-szerviz-havi-statisztika-munkatarsak");
$emailController->addresses = $addressList["all"];

$emailController->send();
// echo $emailController->watch();
?>