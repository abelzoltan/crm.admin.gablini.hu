<?php
$questionnaires = new \App\Http\Controllers\QuestionnaireController; 
if(isset($routes[1])) 
{ 
	switch($routes[1])
	{
		case "new-comment":
			if(isset($_POST["process"]) AND $_POST["process"])
			{
				if(isset($_POST["answer"]) AND !empty($_POST["answer"]) AND $_POST["answer"] == $routes[2])
				{
					$_SESSION[SESSION_PREFIX."lastAnswerCommentID"] = $return = $questionnaires->newAnswerComment($_POST["answer"], $_POST["comment"]);
					$URL->header(PATH_WEB.$routes[0]."/details/".$routes[2]."#comments");
				}
				else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
			}
			else { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
			break;
		case "details":
			if(!isset($routes[2]) OR empty($routes[2])) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
			else
			{
				$answer = $questionnaires->getAnswer($routes[2]);
				if($answer === false) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
				else
				{					
					$VIEW["title"] = "Kérdőív válasz adatlap";
					$VIEW["name"] = "questionnaires.answer-details";
					$VIEW["vars"]["answer"] = $answer;
					$VIEW["vars"]["customerCodeName"] = $customers->codeName;
					if(strpos($answer["questionnaireCode"], "serviceEvents") !== false)
					{
						$services = new \App\Http\Controllers\ServiceController; 
						$VIEW["vars"]["serviceEvent"] = $services->getEvent($answer["data"]->foreignKey);
					}
					else { $VIEW["vars"]["serviceEvent"] = false; }
					$VIEW["vars"]["comments"] = $questionnaires->getAnswerComments($answer["id"]);
				}
			}
			break;
		default:
			$URL->redirect([$routes[0]], ["error" => "unknown"]); 
			break;
	}
}
else
{
	$VIEW["title"] = "Kérdőív válaszok";
	// $answerList = $questionnaires->getAnswers();
	$answerList = $questionnaires->getAnswers(NULL, "id", NULL, NULL, NULL, 0, "customer, date DESC", "0, 500");
	
	$VIEW["name"] = "list-panel";
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Kérdőívekre adott válaszok listája",
		"table" => [
			"header" => [
				[
					"name" => "#", 
					"class" => "", 
					"style" => "width: 5%;", 
				],
				[
					"name" => "Kitöltés időpontja", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Kérdőív", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Ügyfél", 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $customers->codeName, 
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => "Ügyfél e-mail", 
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => ["details"],
			"rows" => [],
		],
	];	
	
	$i = 1;	
	foreach($answerList AS $rowID => $row)
	{		
		$VIEW["vars"]["LIST"]["table"]["rows"][$rowID] = [
			"row" => $row,
			"data" => $row["data"],
			"columns" => [	
				[
					"name" => $i.".",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["dateOut"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["questionnaireName"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => (isset($row["customerData"]["Név"])) ? $row["customerData"]["Név"] : "",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => (isset($row["customerData"]["Ügyfélszám"])) ? $row["customerData"]["Ügyfélszám"] : "",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => (isset($row["customerData"]["E-mail cím"])) ? $row["customerData"]["E-mail cím"] : "",
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => [
				"details" => [
					"class" => "primary",
					"icon" => "file-text",
					"href" => $URL->link([$routes[0], "details", $row["data"]->id]),
					"title" => "Válasz adatlapja",
				],
			],	
		];
		$i++;
	}
}
?>