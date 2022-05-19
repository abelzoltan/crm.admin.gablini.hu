<?php 
$email->frameName = "gablini";
$email->variables = [
	"PATH_WEB" => PATH_WEB,
	"header" => $GLOBALS["site"]->data->name,
	"date" => $msg["data"]->date,
	"type" => $msg["type"],
];

$details = "";
foreach($msg["details"]["basic"] AS $detailsKey => $detailsData)
{
	if(!empty($detailsData["value"]))
	{
		$details .= "
			<tr>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$detailsData["name"].":</td>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$detailsData["value"]."</td>
			</tr>
		";
	}
}
$email->variables["basic"] = $details;

$details = "";
foreach($msg["details"]["data"] AS $detailsKey => $detailsData)
{
	if(!empty($detailsData["value"]))
	{
		$details .= "
			<tr>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$detailsData["name"].":</td>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$detailsData["value"]."</td>
			</tr>
		";
	}
}
$email->variables["data"] = $details;

$details = "";
foreach($msg["details"]["meta"] AS $detailsKey => $detailsData)
{
	if(!empty($detailsData["value"]))
	{
		$details .= "
			<tr>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000;'>".$detailsData["name"].":</td>
				<td style='width: 48%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".$detailsData["value"]."</td>
			</tr>
		";
	}
}
$email->variables["meta"] = $details;

#Types: subject, addresses
$subject = "[GABLINI-APP] ";
if(!empty($msg["data"]->subject)) { $subject .= $msg["data"]->subject; }
else { $subject .= "SOS Help"; }

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL("weboldalak-szerviz-app");
$email->addresses = $addressList["all"];

$email->subject = $subject;
$email->body = $email->setBody("app/email-new-contact");
$email->send();
?>