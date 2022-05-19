<?php
#Datas
$emailController = new \App\Http\Controllers\EmailController;
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"date" => $dateOut,
	"stats1" => "",
	"stats2" => "",
	"stats3" => "",
	"stats4" => "",
];

#Stats #1
$emailController->variables["stats1"] = "
	<h3>Munkalapok száma</h3>
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; width: 60%;'>Összes beérkező munkalap:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["all"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Ebből e-mailen kiértesített:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["emailSent"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Flottás munkalapok:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["fleet"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Belső (saját céges) munkalapok:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["gablini"], 0, ",", " ")." db</td>
		</tr>
	</table>	
";

#Stats #2
$emailController->variables["stats2"] = "
	<h3>Hiányzó vagy rossz adatok</h3>
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; width: 60%;'>Hiányzó e-mail címes munkalapok (csak e-mail):</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["emptyEmail"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Hiányzó telefonszámos munkalapok (csak tel.):</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["emptyPhone"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Mindkettő hiányzik:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["emptyBoth"], 0, ",", " ")." db</td>
		</tr>
	</table>	
";

#Stats #3
$emailController->variables["stats3"] = "
	<h3>Kérdőív kitöltések</h3>
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; width: 60%;'>Online sikeresen kitöltött kérdőívek:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["answerByCustomer"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Call Center által kitöltött kérdőívek:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["answerByAdmin"], 0, ",", " ")." db</td>
		</tr>
	</table>	
";

#Stats #4
$emailController->variables["stats4"] = "
	<h3>Call Center műveletek</h3>
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; width: 60%;'>Call center által sikeresen (válaszolt vagy nem akar) kitöltött munkalapok:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["statuses"]["success"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Call Center műveletek száma:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["statuses"]["allChanges"], 0, ",", " ")." db</td>
		</tr>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000;'>Elérhető, de el nem ért ügyfelek:</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".number_format($datas["customersNotReached"], 0, ",", " ")." db</td>
		</tr>
	</table>	
";

#Sending datas
$emailController->subject = "[GABLINI-CRM] Új autó átadás - havi kitöltési adatok";
$emailController->body = $emailController->setBody("crm/newcar_sellings_monthly_fillings_stats");
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
	
	["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];
$emailController->send();
// echo $emailController->watch();
?>