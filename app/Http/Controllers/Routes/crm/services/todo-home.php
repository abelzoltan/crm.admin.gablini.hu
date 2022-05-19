<?php 
if(isset($routes[1]) AND empty($routes[1])) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
else
{
	#Set view data
	$VIEW["title"] = "Mai teendők listája";
	$VIEW["name"] = "services.todo-home";
	$VIEW["vars"]["navSM"] = true;
	
	#Table header		
	$VIEW["vars"]["LIST"] = [
		"panelName" => "Mai hívásra megjelölt ügyfelek",
		// "order" => ["column" => 0, "type" => "desc"],
		"dataPageLength" => 5,
		"table" => [
			"id" => "service-todo-table",
			"header" => [
				[
					"name" => "#",
					"class" => "", 
					"style" => "width: 40px;", 
				],
				[
					"name" => "Gyártmány", 
					"class" => "", 
					"style" => "width: 80px;", 
				],
				[
					"name" => "Dátum", 
					"class" => "", 
					"style" => "width: 58px;",
				],
				[
					"name" => "Partner", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Telefonszámok", 
					"class" => "", 
					"style" => "width: 85px;", 
				],
				[
					"name" => "Modell", 
					"class" => "", 
					"style" => "width: 70px;", 
				],
				[
					"name" => "Rendszám", 
					"class" => "", 
					"style" => "width: 70px;",
				],				
				[
					"name" => "Hiba", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Telephely", 
					"class" => "", 
					"style" => "width: 120px;",
				],
				[
					"name" => "Belső megjegyzés és állapot", 
					"class" => "no-sort printing-hidden", 
					"style" => "width: 180px;", 
				],
			],
			"buttons" => ["save"],
			"rows" => [],
		],
	];
	
	#Get event lists and questionnaires
	$services = new \App\Http\Controllers\ServiceController;
	$qForm = new \App\Http\Controllers\QuestionnaireController;
	
	$questionnaires = [5, 2, 6, 4, 3, 8, 7]; // Peugeot, Nissan, Infiniti, Kia, Hyundai, Citroen, General
	$fullEventList = [];
	
	$maxQuestions = 0;
	$questionnairesFull = [];
	
	foreach($questionnaires AS $qID)
	{
		$eventList = $services->getEventsForTodoList($qID);
		$fullEventList = array_merge($fullEventList, $eventList);
		
		$questionnairesFull[$qID] = $qForm->getQuestionnaire($qID);
		$qCount = count($questionnairesFull[$qID]["questions"]);
		if($qCount > $maxQuestions) { $maxQuestions = $qCount; }
	}
	
	$eventStatuses = $services->getEventStatuses(1);
	
	#Table header - questions
	for($i = 1; $i <= $maxQuestions; $i++)
	{
		$classPrinting = ($i == $maxQuestions) ? " printing-hidden" : "";
		$VIEW["vars"]["LIST"]["table"]["header"][] = [
			"name" => "K".$i."", 
			"class" => "no-sort text-center service-todo-question".$classPrinting, 
			"style" => "min-width: 150px;", 
		];		
	}
	
	#Loop events
	$i = 1;
	foreach($fullEventList AS $rowID => $row)
	{
		$customer = $customers->getCustomer($row["data"]->customer, false);
		$car = $customers->getCar($row["data"]->car, false);
		
		$statusList = '<option value="">(Állapot)</option>';
		foreach($eventStatuses AS $eventStatusKey => $eventStatus) 
		{
			$statusList .= '<option value="'.$eventStatus["id"].'"';
			// if($row["statusID"] == $eventStatus["id"]) { $statusList .= ' selected'; }
			$statusList .= '>'.$eventStatus["name"].'</option>';
		}
		
		$qRow = $questionnairesFull[$row["data"]->questionnaire];
		
		$allDetails = $services->jsonDecode($row["data"]->allDetails);
		
		$VIEW["vars"]["LIST"]["table"]["rows"][$row["id"]] = [
			"row" => $row,
			"data" => $row["data"],
			"class" => "panel-row-service-todo-".strtolower($car["brand"]),
			"columns" => [	
				[
					"name" => $i.".",
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $car["brand"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["adminTodoDateOut"],
					"class" => "", 
					"style" => "", 
				],			
				[
					"name" => $customer["name"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => '
						<small>
							K: '.$allDetails["Készrejelentés"].'<br>
							M: '.$allDetails["Mobil"].'<br>
							T: '.$allDetails["Telefon 1"].'
						</small>
					',
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => trim($car["model"], "'"),
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $car["regNumber"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["text"],
					"class" => "", 
					"style" => "", 
				],
				[
					"name" => $row["premise"],
					"class" => "", 
					"style" => "", 
				],			
				[
					"name" => '
						<textarea rows="1" class="form-control services-todo-comment-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="datas['.$row["data"]->id.'][innerComment]" placeholder="Megjegyzés" style="display: block; width: 100%; min-width: 150px; margin-bottom: 5px;"></textarea>
						<select class="form-control services-todo-status services-todo-status-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="datas['.$row["data"]->id.'][status]" style="display: block; width: 100%; min-width: 150px;" onchange="serviceStatusChange('.$row["data"]->id.')">'.$statusList.'</select>
					',
					"class" => "", 
					"style" => "", 
				],
			],
			"buttons" => [
				"save" => [
					"class" => "primary",
					"icon" => "save",
					"href" => "javascript:void(0)",
					"onclick" => "sendAnswer('".$row["id"]."')",
					"title" => "Mentés",
				],
			],	
		];
		
		#Questionnaire - Inputs
		$lastQuestion = end($qRow["questions"]);
		foreach($qRow["questions"] AS $questionID => $question)
		{
			if($question["data"]->inputName == "comment") { $question["question"] .= "<br>(Ügyfél mejegyzése)"; }
			$classPrinting = ($question["id"] == $lastQuestion["id"]) ? " printing-hidden" : "";
			$inputType = $qRow["inputTypes"][$question["data"]->inputType];
			if($inputType["url"] == "hidden")
			{
				$VIEW["vars"]["LIST"]["table"]["rows"][$row["id"]]["columns"][0]["name"] .= '<input type="hidden" class="qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']" value="'.$question["val"].'">';
			}
			else
			{ 
				$val = $question["val"];					
				switch($inputType["url"])
				{
					case "select":
					case "radio":
					case "checkbox":
						$col = '<select class="form-control services-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']';
						if($question["data"]->multiple OR $inputType["url"] == "checkbox") { $col .= '[]'; }
						$col .= '" '.$question["attributesHTML"]; 
						if($question["data"]->multiple OR $inputType["url"] == "checkbox") { $col .= ' size="2"'; }	
						$col .= '>';							
							if(!empty($question["placeholder"])) { $col .= '<option value="">'.$question["placeholder"].'</option>'; }
							foreach($question["options"] AS $option) 
							{ 
								$col .= '<option value="'.$option.'"';
								if($option == $val) { $col .= ' selected'; } 
								$col .= '>'.$option.'</option>';
							}
						$col .= '</select>';
						break;		
					case "text":
					case "email":
					case "number":
					case "date":
					case "textarea":
						$inputTypeHere = $inputType["url"];
					default:
						if(!isset($inputTypeHere)) { $inputTypeHere = "text"; }
						
						if($inputTypeHere == "textarea") { $col = '<textarea rows="1"'; }
						else { $col = '<input type="'.$inputTypeHere.'"'; }							
						$col .= ' class="form-control services-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']"';
						
						if($inputTypeHere != "textarea") { $col .= ' value="'.$val.'"'; }
						$col .= ' '.$question["attributesHTML"].'>';
						if($inputTypeHere == "textarea") { $col .= $val; }
						
						if($inputTypeHere == "textarea") { $col .= '</textarea>'; }
						unset($inputTypeHere);
						break;	
				}
				
				$VIEW["vars"]["LIST"]["table"]["rows"][$row["id"]]["columns"][] = [
					"name" => "<div class='td-question-container td-question-container-".$row["data"]->id."'><div style='font-size: 10px; line-height: 11px; padding-bottom: 2px;'>".$question["question"]."</div>".$col."</div>", 
					"class" => $classPrinting, 
					"style" => "", 
				];
			}			
		}
		$colsLeft = $maxQuestions - count($qRow["questions"]);
		if($colsLeft > 0)
		{
			for($j = 1; $j <= $colsLeft; $j++)
			{
				$VIEW["vars"]["LIST"]["table"]["rows"][$row["id"]]["columns"][] = [
					"name" => "&nbsp;", 
					"class" => "", 
					"style" => "", 
				];	
			}
		}
		$i++;
	}
}
?>