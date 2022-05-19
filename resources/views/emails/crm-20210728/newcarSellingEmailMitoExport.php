<?php 
#Datas
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"days" => $days,
	"date" => $return["date"],
	"table" => "",
];
$emailController->attachments = [];

#Brand datas
$emailController->variables["table"] = "
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; vertical-align: middle;'>
		<tr>
			<td style='width: 35%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Márka</td>
			<td style='width: 10%; padding: 5px 1%; border: 1px solid #000; font-weight: bold; text-align: center;'>Sikeres?</td>
			<td style='width: 20%; padding: 5px 1%; border: 1px solid #000; font-weight: bold; text-align: center;'>Események<br>száma</td>
			<td style='width: 35%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Fájlnév</td>
		</tr>
";
foreach($return["brands"] AS $brand => $data)
{
	if($data !== false)
	{
		$emailController->attachments[] = ["path" => $data["filePathInner"], "name" => $data["fileName"]];
		$emailController->variables["table"] .= "
			<tr>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$data["brandName"]."</td>
				<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>Igen</td>
				<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>".$data["rowCount"]."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$data["fileName"]."</td>
			</tr>
		";
	}
	else
	{
		$emailController->variables["table"] .= "
			<tr>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$brand."</td>
				<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'><strong>NEM!</strong></td>
				<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>-</td>
				<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>-</td>
			</tr>
		";
	}
}
$emailController->variables["table"] .= "</table>";

#Sending datas
$emailController->subject = "[GABLINI-CRM] Új autó átadás - esemény márkánkénti exportok (elmúlt ".$days." nap)";
$emailController->body = $emailController->setBody("crm/newcar_sellings_mito_export");
$emailController->addresses = [
	["type" => "to", "email" => "gablini.peter@gablini.hu", "name" => "Gablini Péter"],
	["type" => "to", "email" => "gotz.peter@gablini.hu", "name" => "Götz Péter"],
	["type" => "to", "email" => "csurgai.gabor@gablini.hu", "name" => "Csurgai Gábor"],
	
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	["type" => "to", "email" => "hr@gablini.hu", "name" => "Gablini HR"],
	["type" => "to", "email" => "spenger.regina@gablini.hu", "name" => "Spenger Regina"],
	
	["type" => "to", "email" => "polak.gabriella@gablini.hu", "name" => "Polák Gabriella"],
	["type" => "to", "email" => "dammne.demko.angela@gablini.hu", "name" => "Dammné Demkó Angéla"],
	["type" => "to", "email" => "varszegi.boglarka@gablini.hu", "name" => "Várszegi Boglárka"],
	["type" => "to", "email" => "molnar.ivett@gablini.hu", "name" => "Molnár Ivett"],
	
	// ["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
// $emailController->send();
?>