<?php
#Datas
/*$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"date" => $dateOut,
	"content" => "",
	"stats" => "",
];

$emailController->variables["table"] = "
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000;'>
		<tr>
			<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Progress kód</td>
			<td style='width: 30%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Telefonszám</td>
			<td style='width: 20%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Típus</td>
			<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Dátum</td>
		</tr>
";

foreach($rows AS $row)
{
	if(!empty($row->phone))
	{
		$emailController->variables["table"] .= "
			<tr>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$row->progressCode."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$row->phone."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$row->type."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".date("Y. m. d. H:i", strtotime($row->date))."</td>
			</tr>
		";
	}
}

$emailController->variables["table"] .= "</table>";


#Sending datas
$emailController->subject = "[GABLINI-CRM] ".$dateOut." havi telefonszám-változások";
$emailController->body = $emailController->setBody("crm/customers_phone_changes");
$emailController->addresses = [
	["type" => "to", "email" => "rendszergazda@gablini.hu", "name" => "Gablini Rendszergazda"],
	["type" => "to", "email" => "hr@gablini.hu", "name" => "Gablini HR"],
	["type" => "to", "email" => "spenger.regina@gablini.hu", "name" => "Spenger Regina"],
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	["type" => "to", "email" => "enying@gablini.hu", "name" => "Gablini Enying"],
	
	["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
// echo $emailController->watch();*/
?>