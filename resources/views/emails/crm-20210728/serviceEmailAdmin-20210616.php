<?php
/*$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"rows" => "",
];

$emailController->variables["rows"] .= "
	<tr>
		<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Típus</td>
		<td style='width: 20%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Munkalapszám / Ajánlat sorszáma</td>
		<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Ügyfél</td>
		<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Adatlap link</td>
		<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Kérdőív link</td>
	</tr>
";
foreach($eventsForSend AS $eventID => $eventRow)
{
		$emailController->variables["rows"] .= "
			<tr>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$eventRow["typeName"]."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$eventRow["sheetNumber"]."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'>".$eventRow["customerName"]."</td>
				<td style='padding: 5px 1%; border: 1px solid #000;'><a href='".env("PATH_CRM_WEB")."service-events/".$eventRow["id"]."' target='_blank'>Adatlap</a></td>
				<td style='padding: 5px 1%; border: 1px solid #000;'><a href='".$eventRow["questionnaireLink"]."' target='_blank'>Kérdőív</a></td>
			</tr>
		";
}

$brandForSubject = (!empty($email->brand)) ? " - ".mb_convert_case($email->brand, MB_CASE_TITLE, "UTF-8") : "";

$emailController->subject = "[GABLINI-CRM] ".$email->emailSubject.$brandForSubject;
$emailController->body = $emailController->setBody("crm/".$email->email);
$emailController->addresses = [
	["type" => "to", "email" => "pataki.attila@gablini.hu", "name" => "Pataki Attila"],
	
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	["type" => "to", "email" => "enying@gablini.hu", "name" => "Gablini Enying"],
	
	["type" => "to", "email" => "sepsei.nandor@gablini.hu", "name" => "Sepsei Nándor"],
	["type" => "to", "email" => "erdelyi.janos@gablini.hu", "name" => "Erdélyi János"],
	["type" => "to", "email" => "nemeth.nandor@gablini.hu", "name" => "Németh Nándor"],
	["type" => "to", "email" => "zsinka.zoltan@gablini.hu", "name" => "Zsinka Zoltán"],
	["type" => "to", "email" => "lengyel.tamas@gablini.hu", "name" => "Lengyel Tamás"],
	
	["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();*/
?>