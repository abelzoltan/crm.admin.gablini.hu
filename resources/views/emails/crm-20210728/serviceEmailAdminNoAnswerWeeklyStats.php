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
$emailController->addresses = [
	["type" => "to", "email" => "gablini.peter@gablini.hu", "name" => "Gablini Péter"],
	
	["type" => "to", "email" => "gazdag.ferenc@gablini.hu", "name" => "Gazdag Ferenc"],
	// ["type" => "to", "email" => "csurgai.gabor@gablini.hu", "name" => "Csurgai Gábor"],
	
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	["type" => "to", "email" => "enying@gablini.hu", "name" => "Gablini Enying"],
	["type" => "to", "email" => "rendszergazda@gablini.hu", "name" => "Gablini Rendszergazda"],
	
	["type" => "to", "email" => "erdelyi.janos@gablini.hu", "name" => "Erdélyi János"],
	["type" => "to", "email" => "lengyel.tamas@gablini.hu", "name" => "Lengyel Tamás"],
	["type" => "to", "email" => "varga.laszlo@gablini.hu", "name" => "Varga László Zoltán"],
	["type" => "to", "email" => "sepsei.nandor@gablini.hu", "name" => "Sepsei Nándor"],
	
	["type" => "to", "email" => "pataki.daniel@gablini.hu", "name" => "Pataki Dániel"],
	
	// ["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
// echo $emailController->watch();
?>