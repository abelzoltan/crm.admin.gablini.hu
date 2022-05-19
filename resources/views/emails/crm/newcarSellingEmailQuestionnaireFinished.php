<?php
$emailController->frameName = "gablini-questionnaire";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"bodyBg" => $qForm["data"]->color3,
	"headerBorder" => $qForm["data"]->color2,
	"headerBg" => $qForm["data"]->color1,
	"siteName" => "Gablini Kft.",
	"name" => $event["customerName"],
	"carName" => $event["carName"],
	"logo" => "cid:".$qForm["data"]->logo,
	"signature" => "cid:logo-gablini-email.png",
	"serviceDetails" => "",
];

$emailController->images = [
	[
		"path" => $this->picDirInner.$qForm["data"]->logo,
		"web" => $this->picDir.$qForm["data"]->logo,
		"name" => $qForm["data"]->logo,
	],
	[
		"path" => public_path("pics/logo-gablini-email.png"),
		"web" => env("PATH_CRM_WEB")."pics/logo-gablini-email.png",
		"name" => "logo-gablini-email.png",
	],
];

if(!empty($event["brand"]) AND !empty($event["premise"]))
{
	$premises = new \App\Http\Controllers\PremiseController;
	$address = $premises->model->selectByField($premises->model->tables("addresses"), "nameOut", $event["premise"]);
	if($address AND isset($address->id) AND !empty($address->id))
	{
		$brands = new \App\Http\Controllers\CarController;
		$brand = $brands->model->getBrandByName($event["brand"]);
		if($brand AND isset($brand->id) AND !empty($brand->id))
		{
			$premiseList = $premises->model->select("SELECT * FROM ".$premises->model->tables("premises")." WHERE address = :address AND brand = :brand", ["address" => $address->id, "brand" => $brand->id]);
			if(is_array($premiseList) AND count($premiseList) > 0)
			{
				$premise = $premiseList;
				$premiseName = ($address->id == 5) ? $address->nameOut : $brand->nameOut." Gablini ".$address->nameOut;
				$addressText = $address->zipCode." ".$address->city." ".$address->address;
				$emailController->variables["serviceDetails"] = "
					<p style='text-align: justify;'>
						<br>
						Az Ön szervize: <strong>".$premiseName."</strong><br>
						Tel.: ".$address->phone."<br>
						E-mail: <a href='mailto:".$address->email."' target='_blank' style='color: inherit;'>".$address->email."</a><br>
						Cím: ".$addressText."
					</p>
				";
				
				if(!isset($GLOBALS["users"])) { $GLOBALS["users"] = new \App\Http\Controllers\UserController; }
				$userGroups = $GLOBALS["users"]->getUsersByPosition($address->id, $brand->id);
				if(count($userGroups) > 0)
				{
					foreach($userGroups AS $userGroup)
					{
						if($userGroup["group"]->id == 3 AND count($userGroup["users"]) > 0) // Szerviz
						{
							$emailController->variables["serviceDetails"] .= "
								<p style='text-align: justify;'><strong>Szerviz munkatársaink:</strong></p>
								<table width='100%' border='0' style='width: 100%; border: 0'>
							";
							foreach($userGroup["users"] AS $userHere)
							{
								$pic = (isset($userHere["pic"])) ? env("CDN_PATH_WEB").$userHere["pic"]["path"]["base"] : $userHere["profilePic"];
								$emailController->variables["serviceDetails"] .= "									
									<tr border='0' style='border: 0;'>
										<td width='150' border='0' style='border: 0;'><img src='".$pic."' alt='".$userHere["name"]."' style='display: block; width: 150px;' width='150'></td>
										<td width='15' border='0' style='border: 0;'>&nbsp;</td>
										<td border='0' style='border: 0;'>
											<strong>".$userHere["name"]."</strong><br>
											".$userHere["position"]->nameOut."<br>
											<a href='mailto:".$userHere["data"]->email."' target='_blank' style='color: inherit;'>".$userHere["data"]->email."</a><br>
											".$userHere["phone"]."<br>
										</td>
									</tr>
								";
							}
							$emailController->variables["serviceDetails"] .= "</table><br>";
						}
					}
				}
			}
		}
	}
}

$emailController->subject = "Köszönjük, hogy a Gablini autószalonok kereskedését választotta!";
$emailController->body = $emailController->setBody("crm/newcar_sellings_questionnaire_finished");

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL("crm-ujauto-kerdoiv-kitoltve-szervizek");
$emailController->addresses = $addressList["all"];

$emailController->addresses[] = ["type" => "to", "email" => $event["customer"]["email"], "name" => $event["customerName"]];

$emailController->send();
// echo $emailController->watch();
?>