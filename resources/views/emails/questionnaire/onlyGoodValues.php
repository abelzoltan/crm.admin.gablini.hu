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
$serviceEvent = $newcarSellingEvent = $adminUser = $adminName = false;
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
			
		if(!empty($serviceEvent["adminName"])) { $adminName = $serviceEvent["adminName"]; }
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
		
		if(!empty($newcarSellingEvent["adminName"])) { $adminName = $newcarSellingEvent["adminName"]; }
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

#Subject, body and sending
$emailController->subject = "[GABLINI-KERDOIV] Kiemelkedő értékelést kaptunk - Gratulálunk!";
$emailController->body = $emailController->setBody("questionnaire/onlyGoodValues");

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

#Admin user
if($adminName !== false)
{
	$rows = $GLOBALS["users"]->model->select("SELECT id, email, CONCAT(lastName, ' ', firstName) AS name FROM ".$GLOBALS["users"]->model->tables("users")." WHERE del = '0' AND CONCAT(lastName, ' ', firstName) LIKE :name", ["name" => "%".$adminName."%"]);
	$rowCount = count($rows);
	$adminUserAddressType = "to";
	
	if($rowCount == 1) { $adminUser = ["type" => $adminUserAddressType, "email" => $rows[0]->email, "name" => $rows[0]->name]; }
	elseif($rowCount > 1)
	{
		foreach($rows AS $row)
		{
			$rows2 = $GLOBALS["users"]->model->select("SELECT id, userID, saleManType FROM ".$GLOBALS["users"]->model->tables("admin")." WHERE del = '0' userID = :userID", ["userID" => $row->id]);
			if(count($rows2) > 0)
			{
				if($serviceEvent !== false AND in_array($rows2[0]->saleManType, [4, 5, 6, 7]))
				{
					$adminUser = ["type" => $adminUserAddressType, "email" => $row->email, "name" => $row->name];
					break;
				}
				if($newcarSellingEvent !== false AND in_array($rows2[0]->saleManType, [1, 2, 3]))
				{
					$adminUser = ["type" => $adminUserAddressType, "email" => $row->email, "name" => $row->name];
					break;
				}
			}
		}
	}
}

if(isset($addressListURL))
{
	$webAddresses = new \App\Http\Controllers\WebAddressController;
	$addressList = $webAddresses->getAddressesForSendingByURL($addressListURL);
	
	$emailController->addresses = $addressList["all"];
	if($adminUser !== false) { $emailController->addresses[] = $adminUser; }
	// $emailController->addresses = [
		// ["type" => "to", "email" => "nagymat93@gmail.com", "name" => "Nagy Máté"],
	// ];
	$emailController->send();
}
?>