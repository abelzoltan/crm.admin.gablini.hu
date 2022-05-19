<?php
#Datas
$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"days" => $days,
	"tableRows" => "",
];

foreach($events AS $eventID => $event)
{
	$emailController->variables["tableRows"] .= "
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".date("Y. m. d.", strtotime($event->dateSelling))."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->adminName."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->_allDetailsArray["márka"]." ".$event->_allDetailsArray["modell"]."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->_customer->lastName." ".$event->_customer->firstName."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->_customer->email."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->_customer->mobile."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$event->_customer->phone."</td>
		</tr>
	";
}

#Sending datas
$emailController->subject = "[GABLINI-CRM] Aston Martin - ".$days." nappal ezelőtti autó átadások";
$emailController->body = $emailController->setBody("crm/newcar_sellings_aston_martin_notification");
$emailController->addresses = [
	["type" => "to", "email" => "csurgai.gabor@gablini.hu", "name" => "Csurgai Gábor"],
	["type" => "cc", "email" => "alexa.attila@gablini.hu", "name" => "Alexa Attila"],
	["type" => "cc", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	["type" => "cc", "email" => "gablini.peter@gablini.hu", "name" => "Gablini Péter"],
	
	["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
// echo $emailController->watch();
?>