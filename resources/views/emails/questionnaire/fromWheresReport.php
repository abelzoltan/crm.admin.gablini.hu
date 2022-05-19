<?php
#Basic datas
$dateFromOut = date("Y. m. d.", strtotime($dateFrom));
$dateToOut = date("Y. m. d.", strtotime($dateTo));

$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"question" => $return["question"],	
	"date" => (!empty($dateTextForEmail)) ? $dateTextForEmail." [".$dateFromOut." - ".$dateToOut."]" : $dateFromOut." - ".$dateToOut,
	"stats" => "",
	"otherTexts" => "",
];

#Statistics
$emailController->variables["stats"] .= "<tr>";
	$emailController->variables["stats"] .= "<td style='width: 38%; padding: 5px 1%; border: 1px solid #000;'><strong>Honnan érkezett?</strong></td>";
	foreach($return["stats"] AS $statKey => $stat) { $emailController->variables["stats"] .= "<td style='width: 23%; padding: 5px 1%; border: 1px solid #000;'><strong>".$stat["name"]."</strong></td>"; }
$emailController->variables["stats"] .= "</tr>";
foreach($return["stats"]["all"]["fromWhereList"] AS $fromWhereKey => $count)
{
	$fromWherelabel = (isset($return["fromWhereList"][$fromWhereKey])) ? $return["fromWhereList"][$fromWhereKey]["name"] : $fromWhereKey;
	
	$emailController->variables["stats"] .= "<tr>";
		$emailController->variables["stats"] .= "<td style='padding: 5px 1%; border: 1px solid #000;'>".$fromWherelabel."</td>";
	foreach($return["stats"] AS $statKey => $stat) { $emailController->variables["stats"] .= "<td style='padding: 5px 1%; border: 1px solid #000;'>".$stat["fromWhereList"][$fromWhereKey]."</td>"; }
	$emailController->variables["stats"] .= "</tr>";
	
}
$emailController->variables["stats"] .= "<tr>";
	$emailController->variables["stats"] .= "<td style='padding: 5px 1%; border: 1px solid #000;'>&nbsp;</td>";
	foreach($return["stats"] AS $statKey => $stat) { $emailController->variables["stats"] .= "<td style='padding: 5px 1%; border: 1px solid #000;'><strong>".$stat["sum"]."</strong></td>"; }
$emailController->variables["stats"] .= "</tr>";

#Other texts
$emailController->variables["otherTexts"] .= "
	<tr>
		<td style='width: 23%; padding: 5px 1%; border: 1px solid #000;'><strong>Ügyfél</strong></td>
		<td style='width: 13%; padding: 5px 1%; border: 1px solid #000;'><strong>Progress kód</strong></td>
		<td style='width: 13%; padding: 5px 1%; border: 1px solid #000;'><strong>Típus</strong></td>
		<td style='width: 43%; padding: 5px 1%; border: 1px solid #000;'><strong>Szöveg</strong></td>
	</tr>
";

$customersModel = new \App\Customer;
foreach($return["otherTexts"] AS $answerID => $data)
{
	$statName = (isset($return["stats"][$data["statKey"]])) ? $return["stats"][$data["statKey"]]["name"] : $data["statKey"];
	
	$customerName = $customerProgressCode = "&nbsp;";
	if(!empty($data["customerID"]))
	{
		$customer = $customersModel->getCustomer($data["customerID"]);
		if($customer AND is_object($customer) AND isset($customer->id) AND !empty($customer->id))
		{
			$customerName = $customer->lastName." ".$customer->firstName;
			if(!empty($customer->companyName)) { $customerName .= " (".$customer->companyName.")"; }
			if(!empty($customer->beforeName)) { $customerName = $customer->beforeName." ".$customerName; }
			if(!empty($customer->afterName)) { $customerName .= " ".$customer->afterName; }
			
			if(!empty($customer->progressCode)) { $customerProgressCode = $customer->progressCode; }
		}
	}
	
	$emailController->variables["otherTexts"] .= "
		<tr>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$customerName."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$customerProgressCode."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$statName."</td>
			<td style='padding: 5px 1%; border: 1px solid #000;'>".$data["fromWhereText"]."</td>
		</tr>
	";
} 

#Subject, body and sending
$emailController->subject = "[GABLINI-KERDOIV] Kérdőív - Honnan értesültek a cégről statisztika (".$dateFromOut." - ".$dateToOut.")";
$emailController->body = $emailController->setBody("questionnaire/fromWheresReport");

if(isset($addressListURL) AND !empty($addressListURL))
{
	$webAddresses = new \App\Http\Controllers\WebAddressController;
	$addressList = $webAddresses->getAddressesForSendingByURL($addressListURL);
	
	$emailController->addresses = $addressList["all"];
	// $emailController->addresses = [
		// ["type" => "to", "email" => "nagymat93@gmail.com", "name" => "Nagy Máté"],
	// ];
	$emailController->send();
	// echo "</pre>".$emailController->watch();
}
?>