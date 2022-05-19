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
];

#Loop brands
foreach($datas["brands"] AS $brandKey => $brandDatas)
{
	$emailController->variables["stats1"] .= "
		<h3>".mb_convert_case($brandKey, MB_CASE_TITLE, "UTF-8")."</h3>
		<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
			<tr>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Munkatárs</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Események<br>száma</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Válaszok</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Átlag</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Üres<br>e-mail cím</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Üres<br>telefonszám</td>
				<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Mindkettő<br>üres</td>
			</tr>
	";
	
	foreach($brandDatas["admins"] AS $adminName => $adminDatas)
	{
		$avg = ($adminDatas["answerAvg"] == "-") ? $adminDatas["answerAvg"] : number_format($adminDatas["answerAvg"], 2, ",", " ")." pont";
		$emailController->variables["stats1"] .= "
			<tr>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".$adminName."</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($adminDatas["eventCount"], 0, ",", " ")." db</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($adminDatas["answerCount"], 0, ",", " ")." db</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".$avg."</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($adminDatas["emptyEmail"], 0, ",", " ")." db</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($adminDatas["emptyPhone"], 0, ",", " ")." db</td>
				<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($adminDatas["emptyBoth"], 0, ",", " ")." db</td>
			</tr>
		";
	}
	
	$emailController->variables["stats1"] .= "</table>";
}

#Global stats
$emailController->variables["stats1"] .= "
	<h3>GLOBÁLIS STATISZTIKA</h3>
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000; font-size: 14px; line-height: 20px;'>
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Márka</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Események<br>száma</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Válaszok</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Átlag</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Üres<br>e-mail cím</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Üres<br>telefonszám</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>Mindkettő<br>üres</td>
		</tr>
";
foreach($datas["brands"] AS $brandKey => $brandDatas)
{
	$avg = ($brandDatas["total"]["answerAvg"] == "-") ? $brandDatas["total"]["answerAvg"] : number_format($brandDatas["total"]["answerAvg"], 2, ",", " ")." pont";
	$emailController->variables["stats1"] .= "
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold;'>".mb_convert_case($brandKey, MB_CASE_TITLE, "UTF-8")."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($brandDatas["total"]["eventCount"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($brandDatas["total"]["answerCount"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".$avg."</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($brandDatas["total"]["emptyEmail"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($brandDatas["total"]["emptyPhone"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000;'>".number_format($brandDatas["total"]["emptyBoth"], 0, ",", " ")." db</td>
		</tr>
	";
}
$avg = ($datas["global"]["answerAvg"] == "-") ? $datas["global"]["answerAvg"] : number_format($datas["global"]["answerAvg"], 2, ",", " ")." pont";
$emailController->variables["stats1"] .= "
		<tr>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>ÖSSZESEN</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".number_format($datas["global"]["eventCount"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".number_format($datas["global"]["answerCount"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".$avg."</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".number_format($datas["global"]["emptyEmail"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".number_format($datas["global"]["emptyPhone"], 0, ",", " ")." db</td>
			<td style='padding: 5px 3px; border: 1px solid #000; font-weight: bold; color: #0000ff;'>".number_format($datas["global"]["emptyBoth"], 0, ",", " ")." db</td>
		</tr>
	</table>
";

#Sending datas
$emailController->subject = "[GABLINI-CRM] Új autó átadás - havi statisztikák";
$emailController->body = $emailController->setBody("crm/newcar_sellings_monthly_stats");
$emailController->addresses = [
	["type" => "to", "email" => "gablini.peter@gablini.hu", "name" => "Gablini Péter"],
	
	["type" => "to", "email" => "gotz.peter@gablini.hu", "name" => "Götz Péter"],
	["type" => "to", "email" => "csurgai.gabor@gablini.hu", "name" => "Csurgai Gábor"],
	
	["type" => "to", "email" => "marketing@gablini.hu", "name" => "Gablini Marketing"],
	
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