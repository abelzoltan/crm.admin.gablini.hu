<?php
#Basic datas
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => PATH_WEB,
	"header" => $GLOBALS["site"]->data->name,
	"siteName" => $GLOBALS["site"]->data->name,
	"qLink" => PATH_CRM_WEB."questionnaire-answers/details/".$answer["id"],
	"details" => "",
	"answers" => "",
	"watched" => "",
];

#Details
$serviceEvent = $newcarSellingEvent = false;
if(strpos($answer["questionnaireCode"], "serviceEvents") !== false)
{
	$services = new \App\Http\Controllers\ServiceController; 
	$serviceEvent = $services->getEvent($answer["data"]->foreignKey);
	if($serviceEvent !== false)
	{
		$emailController->variables["details"] .= "
			<tr>
				<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>SZERVIZ ESEMÉNY ADATOK:</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Munkalapszám:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'><a href='".PATH_CRM_WEB."service-events/".$serviceEvent["id"]."' target='_blank'>".$serviceEvent["sheetNumber"]."</a></td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Munkafelvevő:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$serviceEvent["adminName"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Telephely:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$serviceEvent["premise"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Lezárás dátuma:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$serviceEvent["dateClosedPublic"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Rendszám:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$serviceEvent["car"]["regNumber"]."</td>
			</tr>
		";
	}
}
elseif(strpos($answer["questionnaireCode"], "newcarSellingEvents") !== false)
{
	$newcarSellings = new \App\Http\Controllers\NewCarSellingController; 
	$newcarSellingEvent = $newcarSellings->getEvent($answer["data"]->foreignKey);
	if($newcarSellingEvent !== false)
	{
		$emailController->variables["details"] .= "
			<tr>
				<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>ÚJ AUTÓ ÁTADÁS ADATOK:</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Adatlap:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'><a href='".PATH_CRM_WEB."new-car-sellings-events/".$newcarSellingEvent["id"]."' target='_blank'>KLIKK</a></td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Értékesítő:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$newcarSellingEvent["adminName"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Telephely:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$newcarSellingEvent["premise"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Eladás dátuma:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$newcarSellingEvent["dateSellingOut"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Autó:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$newcarSellingEvent["carName"]."</td>
			</tr>
			<tr>
				<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Alvázszám:</td>
				<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$newcarSellingEvent["car"]["bodyNumber"]."</td>
			</tr>
		";
	}
}

$emailController->variables["details"] .= "
	<tr>
		<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>KÉRDŐÍV ÉS KITÖLTÉS ADATOK:</td>
	</tr>
";
if(!empty($answer["questionnaireName"]))
{
	$emailController->variables["details"] .= "
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Kérdőív:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$answer["questionnaireName"]."</td>
		</tr>
	";
}
if(!empty($answer["questionnaireCode"]))
{
	$emailController->variables["details"] .= "
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Kérdőív kódja:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$answer["questionnaireCode"]."</td>
		</tr>
	";
}
if(!empty($answer["dateOut"]))
{
	$emailController->variables["details"] .= "
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Kitöltés időpontja:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$answer["dateOut"]."</td>
		</tr>
	";
}
if($answer["answerByUser"]) { $fillOutBy = "Munkatárs"; }
else { $fillOutBy = "Ügyfél"; }
$emailController->variables["details"] .= "
	<tr>
		<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Kitöltő:</td>
		<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$fillOutBy."</td>
	</tr>
";

#Questions and answers
$emailController->variables["answers"] .= "
	<tr>
		<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>BEKÜLDÖTT ÜGYFÉL ADATOK:</td>
	</tr>
";
foreach($answer["customerData"] AS $dataKey => $dataVal)
{
	if(!empty($dataVal))
	{
		$emailController->variables["answers"] .= "
			<tr>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$dataKey.":</td>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$dataVal."</td>
			</tr>
		";
	}
}
$emailController->variables["answers"] .= "
	<tr>
		<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>VÁLASZOK:</td>
	</tr>
";
foreach($answer["answers"] AS $dataKey => $data)
{
	if(!empty($dataVal))
	{
		$emailController->variables["answers"] .= "
			<tr>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$data["name"].":</td>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$data["val"]."</td>
			</tr>
		";
	}
}

#Watched questions
foreach($answer["watchedQuestions"] AS $dataKey => $data)
{
	if($data["badValue"]) { $rating = '<span style="color: #cc0000;">ALACSONY ÉRTÉKELÉS!</span>'; }
	else { $rating = '<span style="color: #00cc00;">Megfelelő értékelés.</span>'; }
	$emailController->variables["watched"] .= "
		<tr>
			<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; background-color: #f0f0f0;'>".$data["name"]."</td>
		</tr>
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Adott válasz:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$data["val"]."</td>
		</tr>
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Alacsony értékek:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-style: italic;'>".implode("<br>", $data["inputWatchValues"])."</td>
		</tr>
		<tr>
			<td style='width: 33%; padding: 5px 1%; border: 1px solid #000;'>Értékelés:</td>
			<td style='width: 63%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$rating."</td>
		</tr>
	";
}

#Subject, body and sending
$emailController->subject = "[GABLINI-KERDOIV] Alacsony értékelést kaptunk!";
$emailController->body = $emailController->setBody("questionnaire/badValues");

if($serviceEvent !== false)
{
	$emailController->subject .= " - ".$serviceEvent["data"]->premise;
	$addressListURL = "crm-kerdoiv-kitoltes-alacsony-ertekeles-szerviz-";
	
	$sheetNumber = mb_substr($serviceEvent["sheetNumber"], 0, 4, "UTF-8");
	switch($sheetNumber)
	{
		#M3
		case "NMLM": $addressListURL .= "m3"; break;
		#Zugló
		case "NMLZ": $addressListURL .= "zuglo"; break;
		#Gödöllő
		case "NMLG": $addressListURL .= "godollo"; break;
		#Budaörs
		case "NMBO": $addressListURL .= "budaors"; break;
		#N/A
		default: $addressListURL .= "egyeb"; break;
	}
}
elseif($newcarSellingEvent !== false)
{
	$emailController->subject .= " - Új autó átadás [".$newcarSellingEvent["data"]->premise."]";
	$addressListURL = "crm-kerdoiv-kitoltes-alacsony-ertekeles-ujauto-";
	
	switch($newcarSellingEvent["data"]->brand)
	{
		case "kia": 
		case "hyundai": 
		case "nissan": 
		case "peugeot": 
		case "infiniti": 
			$addressListURL .= $newcarSellingEvent["data"]->brand;
			break;
		default: $addressListURL .= "egyeb"; break;
	}	
}

if(isset($addressListURL))
{
	$webAddresses = new \App\Http\Controllers\WebAddressController;
	$addressList = $webAddresses->getAddressesForSendingByURL($addressListURL);
	
	$emailController->addresses = $addressList["all"];
	$emailController->send();
}
?>