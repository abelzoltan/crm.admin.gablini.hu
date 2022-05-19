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
$emailController->addresses = [
	["type" => "to", "email" => "gablini.peter@gablini.hu", "name" => "Gablini Péter"],
	["type" => "to", "email" => "gazdag.ferenc@gablini.hu", "name" => "Gazdag Ferenc"],
	// ["type" => "to", "email" => "csurgai.gabor@gablini.hu", "name" => "Csurgai Gábor"],
	
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	
	["type" => "to", "email" => "erdelyi.janos@gablini.hu", "name" => "Erdélyi János"],
	["type" => "to", "email" => "lengyel.tamas@gablini.hu", "name" => "Lengyel Tamás"],
	["type" => "to", "email" => "varga.laszlo@gablini.hu", "name" => "Varga László Zoltán"],
	["type" => "to", "email" => "sepsei.nandor@gablini.hu", "name" => "Sepsei Nándor"],
	
	["type" => "to", "email" => "pataki.daniel@gablini.hu", "name" => "Pataki Dániel"],
	
	["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
// echo $emailController->watch();
?>