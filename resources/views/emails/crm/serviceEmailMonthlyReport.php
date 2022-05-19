<?php
#Datas
$emailController->frameName = "gablini";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"header" => "Gablini Kft.",
	"siteName" => "Gablini Kft.",
	"date" => $dateOut,
	"content" => "",
	"stats" => "",
	"stats2" => "",
];

#Stats 2
$stats2 = [];
$stats2["events"] = ["name" => "<strong>MUNKALAPOK SZÁMA</strong>", "value" => 0, "style" => "font-weight: bold;"];
$stats2["all"] = ["name" => "<strong>ÖSSZES MEGKERESÉS (telefonhívás)</strong>", "value" => 0, "value2" => 0, "value3" => 0, "style" => "font-weight: bold;"];
$stats2["success"] = ["name" => "<span style='color: #00cc00;'>SIKERES megkeresések száma</span>", "value" => 0, "value2" => 0, "value3" => 0, "style" => "color: #00cc00;"];
$stats2["error"] = ["name" => "<span style='color: #cc0000;'>SIKERTELEN megkeresések száma</span>", "value" => 0, "value2" => 0, "value3" => 0, "style" => "color: #cc0000;"];
$stats2["neutral"] = ["name" => "<span style='color: #0000cc;'>SEMLEGES megkeresések száma</span>", "value" => 0, "value2" => 0, "value3" => 0, "style" => "color: #0000cc;"];

#Table frame
$tableTop = "
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000;'>
		<tr>
			<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Munkalapszám / Ajánlat sorszáma</td>
			<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Esemény típusa</td>
			<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Ügyfél</td>
			<td style='width: 25%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Munkatárs</td>
			<td colspan='2' style='padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Kérdőív</td>
		</tr>
";
$tableBottom = "</table>";

#Event types
$types = [];
$typeRows = $this->model->getEventTypes();
foreach($typeRows AS $type) { $types[$type->id] = $type; }

#Event list
$eventList = [];
$statusCounts = [];
$statusCountsEnying = [];
$statusCountsNotEnying = [];
foreach($rows AS $row)
{
	if(!in_array($row->user, $allUsers)) { $allUsers[$row->user] = $userController->getUser($row->user, false); }
	if(!in_array($row->event, $allEvents)) { $allEvents[$row->event] = $this->model->getEvent($row->event); }
	$event = $allEvents[$row->event];
	$eventList[$event->premise][$event->adminName][$event->id] = $event;
	
	if(!isset($statusCounts[$row->status])) 
	{ 
		$statusCounts[$row->status] = 0; 
		$statusCountsEnying[$row->status] = 0; 
		$statusCountsNotEnying[$row->status] = 0; 
	}
	$statusCounts[$row->status]++;
	
	$status = $allStatuses[$row->status];
	if($status["reportType"] == "success") { $stats2["success"]["value"]++; }
	elseif($status["reportType"] == "error") { $stats2["error"]["value"]++; }
	else { $stats2["neutral"]["value"]++; }
	$stats2["all"]["value"]++;
	
	if($row->user == 343 OR $row->user == 425) 
	{ 
		$statusCountsEnying[$row->status]++; 
		$stats2["all"]["value2"]++;
		
		if($status["reportType"] == "success") { $stats2["success"]["value2"]++; }
		elseif($status["reportType"] == "error") { $stats2["error"]["value2"]++; }
		else { $stats2["neutral"]["value2"]++; }
	}
	else 
	{ 
		$statusCountsNotEnying[$row->status]++;
		$stats2["all"]["value3"]++;		
		
		if($status["reportType"] == "success") { $stats2["success"]["value3"]++; }
		elseif($status["reportType"] == "error") { $stats2["error"]["value3"]++; }
		else { $stats2["neutral"]["value3"]++; }
	}
}

#Stats 2 (end)
$stats2["events"]["value"] = count($allEvents);
$emailController->variables["stats2"] .= "
	<tr>
		<td style='width: 38%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'></td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Összesen</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Enying</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Helyettes</td>
	</tr>
";	
foreach($stats2 AS $key => $statData)
{
	if($key == "events")
	{
		$emailController->variables["stats2"] .= "
			<tr style='".$statData["style"]."'>
				<td style='width: 38%; padding: 5px 1%; border: 1px solid #000;'>".$statData["name"].":</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>".number_format($statData["value"], 0, ".", " ")." db</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>&nbsp;</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>&nbsp;</td>
			</tr>
		";
	}
	else
	{
		$emailController->variables["stats2"] .= "
			<tr style='".$statData["style"]."'>
				<td style='width: 38%; padding: 5px 1%; border: 1px solid #000;'>".$statData["name"].":</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>".number_format($statData["value"], 0, ".", " ")." db</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>".number_format($statData["value2"], 0, ".", " ")." db</td>
				<td style='width: 18%; padding: 5px 1%; border: 1px solid #000;'>".number_format($statData["value3"], 0, ".", " ")." db</td>
			</tr>
		";
	}
}

#Questionnaire stats
$qStats = [];
foreach($questionnaires AS $qID => $qDetails)
{
	$brand = $qDetails["brand"];
	$qData = $qDetails["questionnaire"];
	
	$qStats[$qID] = [
		"name" => ucfirst($brand)." - ".$qData["name"],
		"answers" => 0,
		"questions" => [],
	];
	foreach($qData["questions"] AS $questionKey => $question)
	{
		$qStats[$qID]["questions"][$question["id"]] = [
			"name" => $question["questionOut"],
			"answers" => $question["options"],
			"givenAnswers" => [],
			"givenAnswerCount" => 0,
		];
	}
}
	
#Body
$content = "";
foreach($premises AS $premise)
{
	$title = true;
	foreach($admins AS $admin)
	{
		if(isset($eventList[$premise][$admin]) AND !empty($eventList[$premise][$admin]))
		{
			if($title) 
			{ 
				$content .= "<br><hr><h2>".$premise."</h2>"; 
				$title = false;
			}
			$content .= "<h3>".$admin."</h3>";
			$content .= $tableTop;
			foreach($eventList[$premise][$admin] AS $eventID => $event)
			{
				$user = $allUsers[$row->user];
				
				#Questionnaire answer datas and stats
				$qCol = "-";
				$qCol2 = "&nbsp;";
				if(!empty($event->questionnaireAnswer))
				{
					$answer = $questionnaireController->getAnswer($event->questionnaireAnswer, false);
					if($answer !== false)
					{
						$qStats[$answer["questionnaire"]]["answers"]++;
						$qCol = "<a href='".env("PATH_CRM_WEB")."questionnaire-answers/details/".$event->questionnaireAnswer."' target='_blank'>Kérdőív</a>";
						
						if($answer["hasBadValue"]) { $qCol2 = "<span style='color: #cc0000;'>ALACSONY<br>ÉRTÉKELÉS!</span>"; }
						
						foreach($answer["questionsData"] AS $answerData)
						{
							if(!isset($qStats[$answer["questionnaire"]]["questions"][$answerData["questionID"]]["givenAnswers"][$answerData["answer"]])) { $qStats[$answer["questionnaire"]]["questions"][$answerData["questionID"]]["givenAnswers"][$answerData["answer"]] = 0; }
							$qStats[$answer["questionnaire"]]["questions"][$answerData["questionID"]]["givenAnswers"][$answerData["answer"]]++;
							if(!empty($qStats[$answer["questionnaire"]]["questions"][$answerData["questionID"]]["givenAnswers"][$answerData["answer"]])) { $qStats[$answer["questionnaire"]]["questions"][$answerData["questionID"]]["givenAnswerCount"]++; }
						}
					}
				}
				
				#Customer
				$customer = $customerController->model->getCustomer($event->customer);
				if($customer AND isset($customer->id) AND !empty($customer->id))
				{
					$customerName = $customer->lastName." ".$customer->firstName;
					$customerEmail = $customer->email;
				}
				else { $customerName = $customerEmail = ""; }
				
				$content .= "
					<tr>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".$event->sheetNumber."</td>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".$types[$event->type]->name."</td>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".$customerName." (".$customerEmail.")</td>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".$user["name"]."</td>
						<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>".$qCol."</td>
						<td style='padding: 5px 1%; border: 1px solid #000; text-align: center;'>".$qCol2."</td>
					</tr>
				";
			}
			$content .= $tableBottom;
		}
	}
}
$emailController->variables["content"] = $content;

#Statistics
$statTableTop = "
	<table style='width: 100%; margin: 0 auto; border-collapse: collapse; border: 1px solid #000;'>
		<tr>
			<td style='width: 45%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Kérdés</td>
			<td style='width: 40%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Válaszlehetőségek (statisztika)</td>
			<td style='width: 15%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Összesítés</td>
		</tr>
";
$statTableBottom = "</table>";
$stats = "";
foreach($qStats AS $qID => $qData)
{
	$stats .= "<hr><h3>".$qData["name"]." <em style='color: #0000cc;'>[".number_format($qData["answers"], 0, ",", " ")." db kitöltés]</em></h3>";
	if($qData["answers"] > 0)
	{
		$stats .= $statTableTop;
		if(isset($qData["questions"]) AND count($qData["questions"]) > 0)
		{
			foreach($qData["questions"] AS $questionID => $question)
			{
				$answers = [];
				if(isset($question["answers"]) AND count($question["answers"]) > 0)
				{
					foreach($question["answers"] AS $answerOption)
					{
						if(isset($question["givenAnswers"][$answerOption]))
						{
							$answerStat = number_format($question["givenAnswers"][$answerOption], 0, ",", " ")." db - ";
							$answerStat .= number_format((($question["givenAnswers"][$answerOption] / $question["givenAnswerCount"]) * 100), 2, ",", " ")."%";
						}
						else { $answerStat = "0 db - 0%"; }
						$answers[] = $answerOption." <em style='color: #0000cc;'>[".$answerStat."]</em>";
					}
				}
				
				$stats .= "
					<tr>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".$question["name"]."</td>
						<td style='padding: 5px 1%; border: 1px solid #000;'>".implode("<br>", $answers)."</td>
						<td style='padding: 5px 1%; border: 1px solid #000;'>
							".number_format($question["givenAnswerCount"], 0, ",", " ")." db válasz
						</td>
					</tr>
				";
			}
		}
		$stats .= $statTableBottom;
	}
	else { $stats .= "<p>Nem töltöttek ki kérdőívet!</p>"; }
}
$emailController->variables["stats"] = $stats;
#Status counts
$emailController->variables["statusCounts"] = "
	<tr>
		<td style='width: 38%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Állapot</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Összesen</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Enying</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>Helyettes</td>
	</tr>
";
$countTotal = 0;
$countTotalEnying = 0;
$countTotalNotEnying = 0;
foreach($allStatuses AS $statusKey => $status)
{
	$countHere = (isset($statusCounts[$status["id"]])) ? $statusCounts[$status["id"]] : 0;
	$countHereEnying = (isset($statusCountsEnying[$status["id"]])) ? $statusCountsEnying[$status["id"]] : 0;
	$countHereNotEnying = (isset($statusCountsNotEnying[$status["id"]])) ? $statusCountsNotEnying[$status["id"]] : 0;
	
	$countTotal += $countHere;
	$countTotalEnying += $countHereEnying;
	$countTotalNotEnying += $countHereNotEnying;
	
	if($status["reportType"] == "success") { $color = "00cc00"; }
	elseif($status["reportType"] == "error") { $color = "cc0000"; }
	else { $color = "000000"; }
	
	$emailController->variables["statusCounts"] .= "
		<tr>
			<td style='width: 38%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".$status["name"].":</td>
			<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".number_format($countHere, 0, ".", " ")." db</td>
			<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".number_format($countHereEnying, 0, ".", " ")." db</td>
			<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; color: #".$color."'>".number_format($countHereNotEnying, 0, ".", " ")." db</td>
		</tr>
	";
}
$emailController->variables["statusCounts"] .= "
	<tr>
		<td style='width: 38%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>ÖSSZESEN:</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".number_format($countTotal, 0, ".", " ")." db</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".number_format($countTotalEnying, 0, ".", " ")." db</td>
		<td style='width: 18%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'>".number_format($countTotalNotEnying, 0, ".", " ")." db</td>
	</tr>
";

#Sending datas
$emailController->subject = "[GABLINI-CRM] Havi jelentés";
$emailController->body = $emailController->setBody("crm/service_monthly_report");

$webAddresses = new \App\Http\Controllers\WebAddressController;
$addressList = $webAddresses->getAddressesForSendingByURL("crm-szerviz-havi-jelentes");
$emailController->addresses = $addressList["all"];

$emailController->send();
// echo $emailController->watch();
?>