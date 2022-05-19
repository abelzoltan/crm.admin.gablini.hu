<?php
#Datas
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"date" => $dateOut,
	"rows" => "",
	"stats" => "",
	"premise" => "",
];

#Statistics
$stats["all"] = ["name" => "<strong>ÖSSZES MEGKERESÉS (telefonhívás)</strong>", "value" => 0];
$stats["events"] = ["name" => "<strong>MUNKALAPOK SZÁMA</strong>", "value" => 0];
$stats["success"] = ["name" => "<span style='color: #00cc00;'>Sikeres megkeresések száma (Kitöltött + nem nyilatkozik)</span>", "value" => 0];
$stats["eventSuccessQuestionnaire"] = ["name" => "<span style='color: #00cc00;'> => ebből kérdőív kitöltés</span>", "value" => 0];
$stats["error"] = ["name" => "<span style='color: #cc0000;'>SIKERTELEN megkeresések száma (egyéb válasz)</span>", "value" => 0];
$stats["eventError"] = ["name" => "<span style='color: #cc0000;'>Call center lezáratlan ügyek</span>", "value" => 0];
$stats["neutral"] = ["name" => "<span style='color: #0000cc;'>SEMLEGES megkeresések száma</span>", "value" => 0];

#Rows
$emailController->variables["rows"] .= "
	<tr>
		<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Munkalapszám / Ajánlat sorszáma</td>
		<td style='width: 10%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Esemény típusa</td>
		<td style='width: 20%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Ügyfél</td>
		<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Munkatárs</td>
		<td style='width: 10%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Új állapot</td>
		<td style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Megjegyzés</td>
		<td style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>&nbsp;</td>
		<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Kérdőív</td>
	</tr>
";

$statusCounts = [];
foreach($rows AS $row)
{
	if(!in_array($row->user, $allUsers)) { $allUsers[$row->user] = $userController->getUser($row->user, false); }
	if(!in_array($row->event, $allEvents)) { $allEvents[$row->event] = $this->getEvent($row->event, true, false); }
	
	if(!isset($statusCounts[$row->status])) { $statusCounts[$row->status] = 0; }
	$statusCounts[$row->status]++;
	
	$user = $allUsers[$row->user];
	$event = $allEvents[$row->event];
	$status = $allStatuses[$row->status];
	if($status["reportType"] == "success") { $statusColor = "00cc00"; $stats["success"]["value"]++; }
	elseif($status["reportType"] == "error") { $statusColor = "cc0000"; $stats["error"]["value"]++; }
	else { $statusColor = "0000cc"; $stats["neutral"]["value"]++; }
	
	/*if($status["successValue"])
	{
		$statusColor = "00cc00";
		$stats["success"]["value"]++;
	}
	else
	{
		$statusColor = "cc0000";
		$stats["error"]["value"]++;
	}*/
	$stats["all"]["value"]++;
	
	$qCol = ($event["hasQuestionnaireAnswer"]) ? "<a href='".env("PATH_CRM_WEB")."questionnaire-answers/details/".$event["questionnaireAnswer"]["id"]."' target='_blank'>Kérdőív</a>" : "-";
	$qCol2 = ($event["questionnaireAnswer"]["hasBadValue"]) ? "<span style='color: #cc0000;'>ALACSONY<br>ÉRTÉKELÉS!</span>" : "<span style='color: #00cc00;'>OK</span>";
	if(!$event["hasQuestionnaireAnswer"]) { $qCol2 = "&nbsp;"; }
	$emailController->variables["rows"] .= "
		<tr>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$event["sheetNumber"]."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$event["typeName"]."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$event["customerName"]." (".$event["customerEmail"].")</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$user["name"]."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'><span style='color: #".$statusColor."'>".$status["name"]."</span></td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$row->comment."</td>
			<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'><a href='".env("PATH_CRM_WEB")."service-events/".$event["id"]."' target='_blank'>Adatlap</a></td>
			<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>".$qCol."</td>
			<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>".$qCol2."</td>
		</tr>
	";
}

#Statistics
$stats["events"]["value"] = count($allEvents);
$stats["eventError"]["value"] = $stats["events"]["value"] - $stats["success"]["value"];

foreach($allEvents AS $eventKey => $event) { if($event["hasQuestionnaireAnswer"]) { $stats["eventSuccessQuestionnaire"]["value"]++; } } 

foreach($stats AS $key => $statData)
{
	$emailController->variables["stats"] .= "
		<tr>
			<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$statData["name"].":</td>
			<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".number_format($statData["value"], 0, ".", " ")." db</td>
		</tr>
	";
}

#Status counts
$emailController->variables["statusCounts"] = "";
$countTotal = 0;
foreach($allStatuses AS $statusKey => $status)
{
	$countHere = (isset($statusCounts[$status["id"]])) ? $statusCounts[$status["id"]] : 0;
	$countTotal += $countHere;
	$color = ($status["successValue"]) ? "00cc00" : "cc0000";
	$emailController->variables["statusCounts"] .= "
		<tr>
			<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".$status["name"].":</td>
			<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".number_format($countHere, 0, ".", " ")." db</td>
		</tr>
	";
}
$emailController->variables["statusCounts"] .= "
	<tr>
		<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>ÖSSZESEN:</td>
		<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".number_format($countTotal, 0, ".", " ")." db</td>
	</tr>
";

#Sending datas
$emailController->subject = "[GABLINI-CRM] Napi jelentés (".$dateOut.")";
$emailController->body = $emailController->setBody("crm/service_daily_report");
if(isset($fullReport) AND $fullReport) { $addressListURL = "crm-szerviz-napi-jelentes-teljes"; }
else
{
	$emailController->subject .= " - ".$premise->premise;
	$emailController->variables["premise"] = "a(z) <strong>".$premise->premise."</strong> telephelyen ";
	
	if(mb_strpos($premise->premise, "Zugló") OR mb_strpos($premise->premise, "Fogarasi") !== false) { $addressListURL = "crm-szerviz-napi-jelentes-fogarasi"; }
	elseif(mb_strpos($premise->premise, "Gödöllő") !== false) { $addressListURL = "crm-szerviz-napi-jelentes-godollo"; }
	elseif(mb_strpos($premise->premise, "Budaörs") !== false) { $addressListURL = "crm-szerviz-napi-jelentes-budaors"; }
	elseif(mb_strpos($premise->premise, "M3") !== false) { $addressListURL = "crm-szerviz-napi-jelentes-m3"; }
	else { $addressListURL = "crm-szerviz-napi-jelentes-teljes"; }
}

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL($addressListURL);
$emailController->addresses = $addressList["all"];
/*$emailController->addresses = [
	["type" => "to", "email" => "enying@gablini.hu", "name" => "Gablini László"],
	["type" => "bcc", "email" => "nagymat93@gmail.com", "name" => "Nagy Máté"],
];*/

$emailController->send();
// echo $emailController->watch();
?>