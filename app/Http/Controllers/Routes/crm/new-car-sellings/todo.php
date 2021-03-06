<?php 
if(!isset($routes[1]) OR empty($routes[1])) { $URL->redirect([$routes[0], "general"]); }
elseif($routes[1] == "work-rows-check")
{
	#Get admin todo list
	$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
	$questionnaires = [17]; // Peugeot, Nissan, Infiniti, Kia, Hyundai, Citroen, General
	$eventIDList = [];
	
	$maxQuestions = 0;
	$questionnairesFull = [];
	
	#Loop todo lists
	foreach($questionnaires AS $qID)
	{
		$eventList = $newcarSellings->getEventsForTodoList($qID);
		foreach($eventList AS $eventKey => $event) { $eventIDList[] = $event["id"]; }
	}
	
	#Output
	echo json_encode($eventIDList);
	exit;
}
elseif($routes[1] == "work-phone")
{
	#Get customer
	$customerID = (isset($_POST["customer"]) AND !empty($_POST["customer"])) ? $_POST["customer"] : false;
	if($customerID != false)
	{
		$customers = new \App\Http\Controllers\CustomerController;
		$customer = $customers->getCustomer($customerID, false);
		if($customer != false)
		{
			$type = NULL;
			$value = isset($_POST["value"]) ? $_POST["value"] : NULL;
			if(isset($_POST["type"]))
			{
				if($_POST["type"] == "phone" OR $_POST["type"] == "mobile") { $type = $_POST["type"]; }
			}
			if($type !== NULL)
			{
				$customers->changeCustomer($customerID, [$type => $value], $customer["data"]);
				$customers->changeCustomerPhone($customer["data"]->progressCode, $value, $type);
				$ajaxMsg = "ok";
			}
			else { $ajaxMsg = "error"; }
		}
		else { $ajaxMsg = "error"; }
	}
	else { $ajaxMsg = "error"; }
	
	echo $ajaxMsg;
	exit;
}
elseif($routes[1] == "work-status")
{
	#Get event
	$eventID = (isset($_POST["eventID"]) AND !empty($_POST["eventID"])) ? $_POST["eventID"] : false;
	if($eventID != false)
	{
		#Get datas
		if(isset($_POST) AND !empty($_POST))
		{
			#Get comment and status
			$innerComment = $status = NULL;
			if(isset($_POST["comment"]))
			{
				if(!empty($_POST["comment"])) { $innerComment = $_POST["comment"]; }
				unset($_POST["comment"]);
			}
			if(isset($_POST["status"]))
			{
				if(!empty($_POST["status"])) { $status = $_POST["status"]; }
				unset($_POST["status"]);
			}
			
			if(!empty($innerComment) OR !empty($status))
			{
				#Process
				$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
				$newcarSellings->newEventStatusChange($eventID, $status, $innerComment);
				$statusData = $newcarSellings->getEventStatus($status);
				
				$params = ["status" => $status];
				if($statusData["successValue"]) 
				{ 
					$params["adminTodo"] = 0; 
					$params["adminTodoSuccess"] = 1; 
				}
				
				#Call later
				// Days
				if($statusData["id"] == 4) { $params["adminTodoDate"] = date("Y-m-d 00:00:00", strtotime("+1 day")); }
				elseif($statusData["id"] == 9) { $params["adminTodoDate"] = date("Y-m-d 00:00:00", strtotime("+2 days")); }
				elseif($statusData["id"] == 10) { $params["adminTodoDate"] = date("Y-m-d 00:00:00", strtotime("+3 days")); }
				// Minutes
				elseif($statusData["id"] == 11) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+20 Minutes")); }
				elseif($statusData["id"] == 12) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+30 Minutes")); }
				// Hours
				elseif($statusData["id"] == 13) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+1 hour")); }
				elseif($statusData["id"] == 16) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+2 hours")); }
				elseif($statusData["id"] == 17) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+3 hours")); }
				elseif($statusData["id"] == 18) { $params["adminTodoDate"] = date("Y-m-d H:i:00", strtotime("+4 hours")); }
				
				#Fleet
				elseif($statusData["id"] == 14) 
				{
					$customers = new \App\Http\Controllers\CustomerController;
					$customerID = $newcarSellings->model->getEvent($eventID, "customer");
					$progressCode = $customers->model->getCustomer($customerID, "progressCode");
					
					$userIDHere = (defined("USERID")) ? USERID : NULL;
					$customers->addMarketingDisabled($progressCode, "event: ".$eventID.", status: 14, user: ".$userIDHere);				
				}
				
				#Wrong phone number
				elseif($statusData["id"] == 15) 
				{
					$customerID = $newcarSellings->model->getEvent($eventID, "customer");
					$customers = new \App\Http\Controllers\CustomerController;
					
					$events = $newcarSellings->getEventsForTodoList();
					if(count($events > 0))
					{
						foreach($events AS $event)
						{
							if($event["data"]->customer == $customerID AND $event["data"]->id != $eventID)
							{
								$newcarSellings->newEventStatusChange($event["data"]->id, $status, $innerComment." [eredeti esem??ny: ".$eventID."]");
								$newcarSellings->editEvent($event["data"]->id, $params);
							}
						}
					}
				}
				
				$newcarSellings->editEvent($eventID, $params);
				$ajaxMsg = "ok";
			}
			else { $ajaxMsg = "Az ??llapot vagy megjegyz??s megad??sa k??telez??!"; }	
		}
		else { $ajaxMsg = "A program nem kapott adatokat!"; }
	}
	else { $ajaxMsg = "Az elad??s nem azonos??that??!"; }
	
	echo $ajaxMsg;
	exit;
}
elseif($routes[1] == "work")
{
	#Get event
	$eventID = (isset($_POST["eventID"]) AND !empty($_POST["eventID"])) ? $_POST["eventID"] : false;
	if($eventID != false)
	{
		#Get datas
		$qDatas = (isset($_POST["qDatas"]) AND !empty($_POST["qDatas"])) ? $_POST["qDatas"] : false;
		if($qDatas != false)
		{
			#Get inputs
			$inputs = (isset($qDatas[$eventID]) AND !empty($qDatas[$eventID])) ? $qDatas[$eventID] : false;
			if($inputs != false)
			{
				#Comment and status
				$innerComment = $status = NULL;
				if(isset($inputs["_innerComment"]))
				{
					if(!empty($inputs["_innerComment"])) { $innerComment = $inputs["_innerComment"]; }
					unset($inputs["_innerComment"]);
				}
				
				#Controllers
				$qestionnaires = new \App\Http\Controllers\QuestionnaireController;
				$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
				$customers = new \App\Http\Controllers\CustomerController;
				
				#Get event
				$event = $newcarSellings->getEvent($eventID, false);
				if($event !== false)
				{
					$customer = $customers->getCustomer($event["customerID"], false);
					if($customer !== false)
					{
						$datas = $inputs;
						$datas["_customerCode"] = $customer["code"];
						$datas["_customerName"] = $customer["name"];
						$datas["_customerEmail"] = $customer["email"];

						$return = $qestionnaires->newAnswer($event["questionnaireID"], $event["customerID"], $event["id"], $GLOBALS["user"]["id"], $datas);
						
						if($return["success"]) 
						{ 
							$status = 8;
							$newcarSellings->newEventStatusChange($event["id"], $status, $innerComment);
							$newcarSellings->editEvent($event["id"], ["adminTodo" => 0, "adminTodoSuccess" => 1, "status" => $status]);
							$ajaxMsg = "ok"; 
						}
						elseif(!empty($return["errors"]))
						{
							$ajaxMsg = "Hib??s k??rd????v kit??lt??s:";
							if(in_array("questionnaire-not-exists", $return["errors"])) { $ajaxMsg .= "<br>Nem l??tez?? k??rd????v!"; }
							if(in_array("questionnaire-is-deleted", $return["errors"])) { $ajaxMsg .= "<br>T??r??lt k??rd????v!"; }
							if(in_array("answer-exists", $return["errors"])) { $ajaxMsg .= "<br>M??r l??tezik v??lasz!"; }
							if(in_array("customer-not-exists", $return["errors"])) { $ajaxMsg .= "<br>Az ??gyf??l nem azonos??that??!"; }
							if(in_array("foreign-row-not-exists", $return["errors"])) { $ajaxMsg .= "<br>Az elad??s nem azonos??that??!"; }
							if(in_array("customerData", $return["errors"])) { $ajaxMsg .= "<br>Hi??nyz?? ??gyf??l adatok!"; }
							if(in_array("required", $return["errors"])) 
							{
								$requiredString = implode(", ", $return["required"]);
								$ajaxMsg .= "<br>A k??vetkez?? adatok kit??lt??se k??telez??: <em>".$requiredString."</em>"; 
							}
						}
						else { $ajaxMsg = "Hib??s k??rd????v kit??lt??s!"; }
					}
					else { $ajaxMsg = "Az ??gyf??l nem azonos??that??!"; }
				}
				else { $ajaxMsg = "Az elad??s nem l??tezik!"; }
			}
			else { $ajaxMsg = "A program nem kapott k??rd????v adatokat!"; }
		}
		else { $ajaxMsg = "A program nem kapott adatokat!"; }
	}
	else { $ajaxMsg = "Az elad??s nem azonos??that??!"; }
	
	echo $ajaxMsg;
	exit;
}
else
{
	#Set view data
	$VIEW["title"] = "Mai teend??k list??ja";
	$VIEW["name"] = "new-car-sellings.todo";
	$VIEW["vars"]["navSM"] = true;
	
	#Set questionnaire by list type || If unknown type: redirect
	$questionnaireURL = "uj-auto-eladas-visszajelzes";
	switch($routes[1])
	{
		case "hyundai":
		case "kia":
		case "nissan":
		case "peugeot":
		case "infiniti":
		case "citroen":
		case "2022":
			$panelBrand = strtoupper($routes[1]);
			$questionnaireURL .= "-".$routes[1];
			break;
		case "general":
			break;
		default:
			$panelBrand = "M??RKAF??GGETLEN";
			$URL->redirect([$routes[0], "general"]);
			break;
	}
	
	#Get questionnaire
	$qForm = new \App\Http\Controllers\QuestionnaireController;	
	$error =NULL;
	$qRow = $qForm->getQuestionnaireByURL($questionnaireURL);
	if($qRow !== false)
	{
		$qID = $qRow["id"];
		#Active error
		if(!empty($qRow["activeError"])) 
		{ 
			if($qRow["activeError"] == "activeFrom") { $error = "active-from"; } 
			elseif($qRow["activeError"] == "activeTo") { $error = "active-to"; } 
			else { $error = "active"; } 
		}
		#No questions
		elseif(count($qRow["questions"]) == 0) { $error = "no-questions"; }
	}
	#Questionnaire error
	else { $error = "no-match"; }
	
	#If questionnaire is OKAY
	if(empty($error))
	{
		#Print
		if(isset($_GET["todo-csv-export"]) AND $_GET["todo-csv-export"])
		{
			if($export === false) { $URL->redirect([$routes[0], $routes[1]], ["error" => "unknown"]); }
			else
			{
				#Export
				$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
				$datas = [
					"questionnaire" => $qRow,
					"brand" => $routes[1],
					"panelBrand" => $panelBrand,
					"eventList" => $newcarSellings->getEventsForTodoList($qRow["id"]),
				];
				$export = $newcarSellings->todoExport($datas);
				
				#Filename and settings
				header("Content-Type: text/csv; charset=".$export["charset"]);
				header("Content-Disposition: attachment; filename=".$export["fileName"]);
				$output = fopen("php://output", "w");
				
				#Content
				fputcsv($output, $export["headerRow"], ";");
				foreach($export["rows"] AS $row) { fputcsv($output, $row, ";"); }
			}
			exit;
		}		
		else
		{
			#Store questionnaire datas for view
			$VIEW["vars"]["questionnaireName"] = $qRow["name"]." <em>(".$qRow["code"].")</em>";
			
			#Table header		
			$VIEW["vars"]["LIST"] = [
				"panelName" => "Mai h??v??sra megjel??lt ".$panelBrand." ??gyfelek",
				"order" => ["column" => 0, "type" => "desc"],
				"dataPageLength" => 5,
				"fixed" => ["left" => 8],
				"table" => [
					"id" => "newcar-selling-todo-table",
					"header" => [
						[
							"name" => "Gy??rtm??ny", 
							"class" => "", 
							"style" => "width: 80px;", 
						],
						[
							"name" => "Modell", 
							"class" => "", 
							"style" => "width: 70px;", 
						],
						[
							"name" => "D??tum", 
							"class" => "", 
							"style" => "width: 70px;", 
						],
						[
							"name" => "Partnern??v", 
							"class" => "", 
							"style" => "width: 150px;", 
						],
						[
							"name" => "Telefonsz??m", 
							"class" => "", 
							"style" => "width: 85px;", 
						],
						[
							"name" => "Mobil", 
							"class" => "", 
							"style" => "width: 85px;", 
						],
						[
							"name" => "Rendsz??m", 
							"class" => "", 
							"style" => "width: 70px;", 
						],				
						[
							"name" => "Bels?? megjegyz??s", 
							"class" => "no-sort printing-hidden", 
							"style" => "width: 150px;", 
						],
						[
							"name" => "??llapot", 
							"class" => "no-sort printing-hidden", 
							"style" => "width: 100px;", 
						],
					],
					"buttons" => ["save"],
					"rows" => [],
				],
			];
			
			#Table header - questions
			$i = 1;
			$lastQuestion = end($qRow["questions"]);
			foreach($qRow["questions"] AS $questionID => $question)
			{
				if($qRow["inputTypes"][$question["data"]->inputType]["url"] != "hidden") 
				{ 
					if($question["data"]->inputName == "comment") { $question["question"] .= "<br>(??gyf??l mejegyz??se)"; }
					
					$classPrinting = ($question["id"] == $lastQuestion["id"]) ? " printing-hidden" : "";
					$VIEW["vars"]["LIST"]["table"]["header"][] = [
						"name" => "<span class='printing-hidden'>".$i.". ".$question["question"]."</span><span class='printing-visible'>K".$i."</span>", 
						"class" => "no-sort printing-text-center newcar-selling-todo-question".$classPrinting, 
						"style" => "min-width: 150px;", 
					];
				}
				$i++;			
			}
			
			#Table header -  from where question
			$VIEW["vars"]["LIST"]["table"]["header"][] = [
				"name" => "<span class='printing-hidden'>".$i.". ".$qForm->model->fromWhereQuestion."</span><span class='printing-visible'>K".$i."</span>", 
				"class" => "no-sort printing-text-center newcar-selling-todo-question", 
				"style" => "min-width: 150px;", 
			];
			$fromWhereAnswerList = '<option value="">'.$qForm->model->fromWhereQuestionPlaceholder.'</option>';
			foreach($qForm->model->fromWheres() AS $fromWhereKey => $fromWhere) 
			{
				if($fromWhere["active"]) { $fromWhereAnswerList .= '<option value="'.$fromWhereKey.'">'.$fromWhere["name"].'</option>'; }
			}
			
			#Create objects from controllers
			$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
			$customers = new \App\Http\Controllers\CustomerController;
			
			#Status list
			$eventStatuses = $newcarSellings->getEventStatuses(1);
			
			#Event list
			$eventList = $newcarSellings->getEventsForTodoList($qRow["id"]);
			foreach($eventList AS $rowID => $row)
			{
				$customer = $customers->getCustomer($row["data"]->customer, false);
				$car = $customers->getCar($row["data"]->car, false);
				
				$statusList = '<option value="">(??llapot)</option>';
				foreach($eventStatuses AS $eventStatusKey => $eventStatus) 
				{
					$statusList .= '<option value="'.$eventStatus["id"].'">'.$eventStatus["name"].'</option>';
				}
				
				$allDetails = $newcarSellings->jsonDecode($row["data"]->allDetails);
				
				$VIEW["vars"]["LIST"]["table"]["rows"][$rowID] = [
					"row" => $row,
					"data" => $row["data"],
					"columns" => [	
						[
							"name" => $car["brand"],
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => trim($car["model"], "'"),
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
								<span class="printing-visible">'.$customer["phoneOut"].'</span>
								<div class="input-group printing-hidden">
									<input type="text" class="form-control newcar-selling-todo-phone-'.$row["data"]->id.' name="phone-'.$row["data"]->id.'" placeholder="Telefonsz??m" value="'.$customer["phone"].'" style="min-width: 110px;">
									<span class="input-group-addon"><a onclick="phoneChange(this, '.$customer["id"].', \'phone\')" class="display-block text-center cursor-pointer" alt="Ment??s ??s csere" title="Ment??s ??s csere"><i class="fa fa-floppy-o"></i></a></span>
								</div>
							',
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => '
								<span class="printing-visible">'.$customer["mobileOut"].'</span>
								<div class="input-group printing-hidden">
									<input type="text" class="form-control newcar-selling-todo-mobile-'.$row["data"]->id.' name="mobile-'.$row["data"]->id.'" placeholder="Mobil" value="'.$customer["mobile"].'" style="min-width: 110px;">
									<span class="input-group-addon"><a onclick="phoneChange(this, '.$customer["id"].', \'mobile\')" class="display-block text-center cursor-pointer" alt="Ment??s ??s csere" title="Ment??s ??s csere"><i class="fa fa-floppy-o"></i></a></span>
								</div>
							',
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => $car["regNumber"],
							"class" => "", 
							"style" => "", 
						],					
						[
							"name" => '<textarea rows="1" class="form-control newcar-selling-todo-comment-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.'][_innerComment]" placeholder="Megjegyz??s"></textarea>',
							"class" => "", 
							"style" => "", 
						],
						[
							"name" => '
								<select class="form-control newcar-selling-todo-status newcar-selling-todo-status-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.'][_status]" onchange="newcarSellingStatusChange('.$row["data"]->id.')">'.$statusList.'</select>',
							"class" => "", 
							"style" => "", 
						],
					],
					"buttons" => [
						"save" => [
							"class" => "primary newcar-selling-todo-btn-".$row["data"]->id,
							"icon" => "save",
							"href" => "javascript:void(0)",
							"onclick" => "sendAnswer('".$row["id"]."')",
							"title" => "Ment??s",
						],
					],	
				];
				
				#Questionnaire - Inputs
				foreach($qRow["questions"] AS $questionID => $question)
				{
					$classPrinting = ($question["id"] == $lastQuestion["id"]) ? " printing-hidden" : "";
					$inputType = $qRow["inputTypes"][$question["data"]->inputType];
					if($inputType["url"] == "hidden")
					{
						$VIEW["vars"]["LIST"]["table"]["rows"][$rowID]["columns"][0]["name"] .= '<input type="hidden" class="qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']" value="'.$question["val"].'">';
					}
					else
					{ 
						$val = $question["val"];					
						switch($inputType["url"])
						{
							case "select":
							case "radio":
							case "checkbox":
								$col = '<select class="form-control newcar-selling-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']';
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
								$col .= ' class="form-control newcar-selling-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.']['.$question["inputName"].']"';
								
								if($inputTypeHere != "textarea") { $col .= ' value="'.$val.'"'; }
								$col .= ' '.$question["attributesHTML"].'>';
								if($inputTypeHere == "textarea") { $col .= $val; }
								
								if($inputTypeHere == "textarea") { $col .= '</textarea>'; }
								unset($inputTypeHere);
								break;	
						}
						
						$VIEW["vars"]["LIST"]["table"]["rows"][$rowID]["columns"][] = [
							"name" => $col, 
							"class" => $classPrinting, 
							"style" => "", 
						];
					}			
				}
				
				#From where question
				$VIEW["vars"]["LIST"]["table"]["rows"][$rowID]["columns"][] = [
					"name" => '
						<select class="form-control newcar-selling-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" name="qDatas['.$row["data"]->id.'][_fromWhere]" required onchange="fromWhereChange(this, '.$row["data"]->id.')">'.$fromWhereAnswerList.'</select>
						<input class="form-control printing-hidden newcar-selling-todo-question-'.$row["data"]->id.' qDatas'.$row["data"]->id.'" type="text" name="qDatas['.$row["data"]->id.'][_fromWhereText]" style="display: none;" id="newcar-selling-todo-question-fromWhereText-'.$row["data"]->id.'">
					', 
					"class" => "", 
					"style" => "", 
				];
			}
		}
	}
	else { $VIEW["vars"]["todoError"] = $error; }
}
?>
