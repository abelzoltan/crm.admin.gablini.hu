<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Questionnaire;

class QuestionnaireController extends BaseController
{
	public $picDirInner = NULL;
	public $picDir = NULL;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Questionnaire";
		$this->model = new \App\Questionnaire($connectionData);
		$this->picDirInner = public_path("pics/kerdoiv/");
		$this->picDir = env("PATH_QUESTIONNAIRE_WEB")."pics/kerdoiv/";
	}
	
	#Get questionnaire
	public function getQuestionnaire($id, $allData = true, $getTypeController = true, $getAnswers = false)
	{
		$row = $this->model->getQuestionnaire($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			#Basic data
			$return = [];
			$return["data"] = $row;
			
			$return["id"] = $row->id;
			$return["code"] = $row->code;
			$return["url"] = $row->url;
			$return["name"] = $row->name;
			
			$return["textTop"] = $row->textTop;
			$return["textBottom"] = $row->textBottom;
			
			$return["date"] = $row->date;
			$return["dateOut"] = strftime("%Y. %B %d.", strtotime($row->date));
			
			$users = new \App\Http\Controllers\UserController;
			$return["user"] = $users->getUser($row->user);
			$return["userName"] = $return["user"]["name"];
			
			#Logo & Colors & Background image
			$return["logo"] = $return["bgImage"] = NULL;
			if(!empty($row->logo)) { $return["logo"] = $this->picDir.$row->logo; }
			if(!empty($row->bgImage)) { $return["bgImage"] = $this->picDir.$row->bgImage; }
			
			$return["colors"] = ["bgTop" => "", "bg" => "", "formTop" => ""];
			if(!empty($row->color1)) { $return["colors"]["bgTop"] = $row->color1; }
			if(!empty($row->color2)) { $return["colors"]["formTop"] = $row->color2; }
			if(!empty($row->color3)) { $return["colors"]["bg"] = $row->color3; }
			
			#Active
			$return["active"] = true;			
			$return["activeError"] = $return["activeFrom"] = $return["activeFromOut"] = $return["activeTo"] = $return["activeToOut"] = NULL;
			$dateNow = date("Y-m-d H:i:s");
			if(!empty($row->activeFrom) AND $row->activeFrom != "0000-00-00" AND $row->activeFrom != "0000-00-00 00:00:00")
			{
				if($dateNow < $row->activeFrom) 
				{ 
					$return["active"] = false; 
					$return["activeError"] = "activeFrom"; 
					
					$return["activeFrom"] = $row->activeFrom;
					$return["activeFromOut"] = strftime("%Y. %B %d. ", strtotime($row->activeFrom));
				}
			}
			if(!empty($row->activeTo) AND $row->activeTo != "0000-00-00" AND $row->activeTo != "0000-00-00 00:00:00")
			{
				if($dateNow > $row->activeTo) 
				{ 
					$return["active"] = false; 
					$return["activeError"] = "activeTo"; 
					
					$return["activeTo"] = $row->activeTo;
					$return["activeToOut"] = strftime("%Y. %B %d. ", strtotime($row->activeTo));
				}
			}
			
			if($allData)
			{
				#Type and foreign row
				$return["type"] = $this->getType($row->type, $getTypeController);
				
				#Input types
				$return["inputTypes"] = $this->getInputTypes();
				
				#Questions
				$return["questions"] = $this->getQuestions($row->id);
				
				#Answers
				if($getAnswers) { $return["answers"] = $this->getAnswers($row->id); }
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getQuestionnaireByCode($code, $allData = true, $getTypeController = true)
	{
		$id = $this->model->getQuestionnaireByCode($code, "id");
		if(!empty($id)) { $return = $this->getQuestionnaire($id, $allData, $getTypeController); }
		else { $return = false; }
		return $return;
	}
	
	public function getQuestionnaireByURL($url, $allData = true, $getTypeController = true)
	{
		$id = $this->model->getQuestionnaireByURL($url, "id");
		if(!empty($id)) { $return = $this->getQuestionnaire($id, $allData, $getTypeController); }
		else { $return = false; }
		return $return;
	}
	
	#Get type
	public function getType($id, $getController = false)
	{
		$row = $this->model->getType($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			if($getController)
			{
				$return["modelName"] = $modelName = "\\App\\".$row->foreignModel;
				$return["controllerName"] = $controllerName = "\\App\\Http\\Controllers\\".$row->foreignModel."Controller";
				$controller = new $controllerName;
				$return["controller"] = $controller;
				$return["tableName"] = $controller->model->tables($row->foreignTable);
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getTypeByURL($url, $getController = false)
	{
		$id = $this->model->getTypeByURL($url, "id");
		if(!empty($id)) { $return = $this->getType($id, $getController); }
		else { $return = false; }
		return $return;
	}
	
	#Get question
	public function getQuestion($id, $allData = true)
	{
		$row = $this->model->getQuestion($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["inputName"] = $row->inputName;
			$return["inputWatch"] = $this->getInputWatchByQuestion($row->id);
			$return["question"] = $return["questionOut"] = $return["questionHTML"] = $row->question;
			$return["text"] = $row->text;
			$return["placeholder"] = $row->placeholder;
			if(!empty($row->baseValue)) 
			{ 
				if($row->inputType == 5 OR $row->multiple)
				{
					$return["val"] = nl2br($row->baseValue); 
					$return["val"] = explode("<br />", $return["val"]); 
					foreach($return["val"] AS $i => $val) { $return["val"][$i] = trim($val); }
				}
				else { $return["val"] = $row->baseValue; }
			}
			if($row->required) 
			{ 
				$return["questionOut"] .= " *";
				$return["questionHTML"] .= " <span class='required'>*</span>";
			}
			
			if($row->inputType == 4) { $row->placeholder = "éééé. hh. nn."; }
			
			$return["attributes"] = $return["attributes2"] = $return["attributesOut"] = [];			
			if(!empty($row->placeholder)) { $return["attributes"]["placeholder"] = $row->placeholder; }				
			if(!empty($row->rows)) { $return["attributes"]["rows"] = $row->rows; }					
			if(!empty($row->min)) { $return["attributes"]["min"] = $row->min; }					
			if(!empty($row->max)) { $return["attributes"]["max"] = $row->max; }	
			
			if($row->required) { $return["attributes2"][] = "required"; }			
			if($row->readonly) { $return["attributes2"][] = "readonly"; }			
			if($row->disabled) { $return["attributes2"][] = "disabled"; }			
			if($row->autofocus) { $return["attributes2"][] = "autofocus"; }			
			if($row->multiple) { $return["attributes2"][] = "multiple"; }
			
			foreach($return["attributes"] AS $attrName => $attrVal) { $return["attributesOut"][] = $attrName.'="'.$attrVal.'"'; }			
			$return["attributesOut"] = implode(" ", $return["attributesOut"]);
			$return["attributes2Out"] = implode(" ", $return["attributes2"]);
			$return["attributesHTML"] = $return["attributesOut"]." ".$return["attributes2Out"];
			
			if(!empty($row->options)) 
			{ 
				$return["options"] = nl2br($row->options); 
				$return["options"] = explode("<br />", $return["options"]); 
				foreach($return["options"] AS $i => $option) { $return["options"][$i] = trim($option); }
			}
			else { $return["options"] = []; }
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getQuestions($questionnaire, $key = "id", $deleted = 0, $orderBy = "orderNumber")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getQuestions($questionnaire, $deleted, $fields, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getQuestion($row->id, false); 
			}
		}
		
		return $return;
	}
	
	#Get input type
	public function getInputType($id)
	{
		$row = $this->model->getInputType($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["url"] = $row->url;
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getInputTypeByURL($url)
	{
		$id = $this->model->getInputTypeByURL($url, "id");
		if(!empty($id)) { $return = $this->getInputType($id); }
		else { $return = false; }
		return $return;
	}
	
	public function getInputTypes($key = "id", $deleted = 0, $orderBy = "orderNumber")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getInputTypes($fields, $deleted, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getInputType($row->id); 
			}
		}
		
		return $return;
	}
	
	#Get answer
	public function getAnswer($id, $allData = true)
	{
		$row = $this->model->getAnswer($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["hasBadValue"] = ($row->hasBadValue) ? true : false;
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($row->date));
			
			$return["questionnaire"] = $row->questionnaire;
			$return["questionnaireCode"] = $row->questionnaireCode;
			$return["questionnaireName"] = $row->questionnaireName;
			
			$return["customerID"] = $row->customer;
			$return["userID"] = $row->user;
			if(!empty($row->user)) { $return["answerByUser"] = true; }
			else { $return["answerByUser"] = false; }
			
			$details = $this->jsonDecode($row->answers);
			$return["customerData"] = $details["customer"];			
			$return["questionsData"] = $details["questions"];			
			$return["answers"] = [];
			$return["watchedQuestions"] = [];
			
			$return["answerComment"] = NULL;
			
			$return["answerSum"] = NULL;
			$return["answerCount"] = NULL;
			$return["answerAvg"] = NULL;
			
			$return["answersValsForStats"] = [];
			
			if($details !== NULL AND isset($details["questions"]))
			{
				foreach($details["questions"] AS $question) 
				{ 
					#Details
					$name = $originalName = $question["question"];
					if($question["required"]) { $name .= " <em>[KÖTELEZŐ!]</em>"; }
					$return["answers"][$this->getQuestionId($question["questionID"])] = $answerRowHere = [
						"originalName" => $originalName,
						"name" => $name,
						"val" => nl2br($question["answer"]),
						"required" => ($question["required"]) ? true : false,
						"watched" => ($question["watched"]) ? true : false,
						"badValue" => ($question["badValue"]) ? true : false,
					];
					
					#Comment
					if($question["inputName"] == "comment") { $return["answerComment"] = $answerRowHere["val"]; }
					
					#Avg, count
					if($question["watched"])
					{
						$return["watchedQuestions"][$question["questionID"]] = [
							"originalName" => $answerRowHere["originalName"],
							"name" => $answerRowHere["name"],
							"val" => $answerRowHere["val"],
							"required" => $answerRowHere["required"],
							"badValue" => $answerRowHere["badValue"],
							"inputWatchID" => $question["inputWatchID"],
							"inputWatchValues" => $question["inputWatchValues"],
						];
					}
					
					if(is_numeric($answerRowHere["val"]))
					{
						$return["answerSum"] += $answerRowHere["val"];
						$return["answerCount"]++;	
						$return["answersValsForStats"][$question["questionID"]] = $answerRowHere["val"];
					}
					else
					{
						$val = NULL;
						
						#Hyundai
						if($row->questionnaire == 3)
						{
							switch($answerRowHere["val"])
							{
								case "Elégtelen":
								case "Elégtelen (1)":
									$val = 1;
									break;
								case "Elégséges":
								case "Elégséges (2)":
									$val = 2;
									break;
								case "Megfelelő":
								case "Megfelelő (3)":
									$val = 3;
									break;
								case "Jó":
								case "Jó (4)":
									$val = 4;
									break;	
								case "Kiváló":
								case "Kiváló (5)":
									$val = 5;
									break;	
							}
						}
						
						if($val !== NULL)
						{
							$return["answerSum"] += $val;
							$return["answerCount"]++;
							$return["answersValsForStats"][$question["questionID"]] = $val;
						}
					}
				}
			}
			$return["answerAvg"] = ($return["answerCount"] > 0) ? $return["answerSum"] / $return["answerCount"] : NULL;
			
			if($allData)
			{
				$customers = new \App\Http\Controllers\CustomerController;
				$return["customerName"] = $return["customerCode"] = $return["userName"] = NULL;
				
				$return["customer"] = $customers->getCustomer($row->customer, false);
				if($return["customer"] !== false) 
				{ 
					$return["customerName"] = $return["customer"]["name"]; 
					$return["customerCode"] = $return["customer"]["code"]; 
				}
				
				if(!empty($row->user)) 
				{ 
					$users = new \App\Http\Controllers\UserController;
					$return["user"] = $users->getUser($row->user);
					if($return["user"] !== false) { $return["userName"] = $return["user"]["name"]; }
				}
			}
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getAnswerByIdentifiers($questionnaire, $customer, $foreignKey, $user = NULL, $allData = true)
	{
		$id = $this->model->getAnswerByIdentifiers($questionnaire, $customer, $foreignKey, $user, "id", 0);
		if(!empty($id)) { $return = $this->getAnswer($id, $allData); }
		else { $return = false; }
		
		return $return;
	}
	
	public function answerExists($questionnaire, $customer, $foreignKey)
	{
		$id = $this->model->getAnswerByIdentifiers($questionnaire, $customer, $foreignKey, NULL, "id", 0);
		if(!empty($id)) { $return = $id; }
		else { $return = false; }
		
		return $return;
	}
	
	public function getAnswers($questionnaire = NULL, $key = "id", $customer = NULL, $foreignKey = NULL, $user = NULL, $deleted = 0, $orderBy = "customer, date DESC", $limit = NULL)
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getAnswers($questionnaire, $customer, $foreignKey, $user, $deleted, $fields, $orderBy, $limit);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getAnswer($row->id, false); 
			}
		}
		
		return $return;
	}
	
	#New answer
	public function newAnswer($questionnaire, $customer, $foreignKey, $user, $datas)
	{
		$return = [
			"basicDatas" => [
				"questionnaire" => $questionnaire,
				"customer" => $customer,
				"foreignKey" => $foreignKey,
				"user" => $user,
			],
			"datas" => $datas,
			"success" => true,
			"errors" => [],
			"required" => [],
			"baseAnswers" => [],
			"answers" => [],
			"answerID" => NULL,
			"hasBadValue" => false,
			"lowestValue" => NULL,
		];
		
		$qForm = $this->getQuestionnaire($questionnaire);
		#Questionnaire not exists / is deleted
		if($qForm === false)
		{
			$return["success"] = false; 
			$return["errors"][] = "questionnaire-not-exists"; 
		}
		elseif($qForm["data"]->del)
		{
			$return["success"] = false; 
			$return["errors"][] = "questionnaire-is-deleted"; 
		}
		#Answer already exists
		elseif($this->answerExists($questionnaire, $customer, $foreignKey) !== false) 
		{ 
			$return["success"] = false; 
			$return["errors"][] = "answer-exists"; 
		}
		#Okay
		else
		{
			#Customer / Foreign row not exists
			if(!$customer) 
			{ 
				$return["success"] = false; 
				$return["errors"][] = "customer-not-exists"; 
			}
			if(!$foreignKey) 
			{ 
				$return["success"] = false; 
				$return["errors"][] = "foreign-row-not-exists"; 
			}
			
			#Okay
			if($return["success"])
			{
				#Base answers (customer)
				if(!isset($datas["_customerName"])) 
				{
					$return["success"] = false; 
					$return["errors"][] = "customerData"; 
				}
				else { $return["baseAnswers"]["Név"] = $datas["_customerName"]; }
				
				if(!isset($datas["_customerCode"])) 
				{
					$return["success"] = false; 
					$return["errors"][] = "customerData"; 
				}
				else { $return["baseAnswers"]["Ügyfélszám"] = $datas["_customerCode"]; }
				
				if(!isset($datas["_customerEmail"])) 
				{
					$return["success"] = false; 
					$return["errors"][] = "customerData"; 
				}
				else { $return["baseAnswers"]["E-mail cím"] = $datas["_customerEmail"]; }
				
				#Dynamic answers
				$inputTypes = $qForm["inputTypes"];

				foreach($qForm["questions"] AS $questionID => $question)
				{
					#Get type and input-watch
					$inputType = $inputTypes[$question["data"]->inputType];
					$inputWatch = $question["inputWatch"];
					
					#Return array
					$answerReturn = [
						"questionID" => $question["id"],
						"question" => $question["question"],
						"inputName" => $question["inputName"],
						"required" => $question["data"]->required,
						"answer" => $question["val"],
						"watched" => 0,
						"badValue" => 0,
					];
					
					#Watch bad values
					if($inputWatch !== false)
					{
						$answerReturn["watched"] = 1;
						$answerReturn["inputWatchID"] = $inputWatch["id"];
						$answerReturn["inputWatchValues"] = $inputWatch["badValues"];
					}
					
					#Hidden input
					if($inputType["url"] == "hidden") 
					{ 
						$answerReturn["answer"] = $question["val"]; 
						#Has bad value?
						if($inputWatch !== false AND in_array($answerReturn["answer"], $inputWatch["badValues"])) 
						{ 
							$answerReturn["badValue"] = 1; 
							$return["hasBadValue"] = true;
						}
					}
					#Other input
					else
					{
						#Required and missing?
						if($question["data"]->required AND (!isset($datas[$question["inputName"]]) OR empty($datas[$question["inputName"]])))
						{
							$return["success"] = false; 
							$return["errors"][] = "required";
							$return["required"][] = $question["inputName"];
						}
						
						#Answer has been sent
						if(isset($datas[$question["inputName"]]))
						{
							#Answer is array
							if(is_array($datas[$question["inputName"]])) 
							{ 
								$answer = implode(", ", $datas[$question["inputName"]]); 
								#Has bad value?
								if($inputWatch !== false)
								{
									foreach($datas[$question["inputName"]] AS $answerItemHere)
									{
										if(in_array($answerItemHere, $inputWatch["badValues"])) 
										{ 
											$answerReturn["badValue"] = 1; 
											$return["hasBadValue"] = true;
											break;
										}
									}
								}
							}
							#Answer is string
							else 
							{ 
								$answer = $datas[$question["inputName"]];
								#Has bad value?
								if($inputWatch !== false AND in_array($answer, $inputWatch["badValues"])) 
								{ 
									$answerReturn["badValue"] = 1; 
									$return["hasBadValue"] = true;
								}
							}
						}
						else 
						{
							$answer = "";
							#Has bad value?
							if($inputWatch !== false AND in_array($answer, $inputWatch["badValues"])) 
							{ 
								$answerReturn["badValue"] = 1; 
								$return["hasBadValue"] = true;
							}
						}
						
						$answerReturn["answer"] = $answer;
					}
					
					#Smallest value
					if(is_numeric($answerReturn["answer"]) AND ($return["lowestValue"] === NULL OR  $return["lowestValue"] >= $answerReturn["answer"])) { $return["lowestValue"] = $answerReturn["answer"]; }
					
					#Save answer
					$return["answers"][] = $answerReturn;
				}
				
				#From where
				$fromWhere = (isset($datas["_fromWhere"])) ? $datas["_fromWhere"] : "";
				$fromWhereText = ($fromWhere == "egyeb" AND isset($datas["_fromWhereText"])) ? $datas["_fromWhereText"] : "";

                if (!$this->checkFromWhereEgyebRequired($fromWhere, $datas, $return)) {
                    $return["success"] = false;
                    $return["errors"][] = "required";
                    $return["required"][] = "fromWhereText";
                }

				$return["answers"][] = [
					"questionID" => NULL,
					"question" => $this->model->fromWhereQuestion,
					"inputName" => "_fromWhere",
					"required" => 1,
					"answer" => $fromWhere,
					"watched" => 0,
					"badValue" => 0,
				];
				$return["answers"][] = [
					"questionID" => NULL,
					"question" => $this->model->fromWhereTextQuestion,
					"inputName" => "_fromWhereText",
					"required" => 1,
					"answer" => $fromWhereText,
					"watched" => 0,
					"badValue" => 0,
				];
				
				#Okay
				if($return["success"])
				{
					#Create answers array
					$answers = [
						"customer" => $return["baseAnswers"],
						"questions" => $return["answers"],
					];
					
					#Basic params
					$params = [
						"questionnaire" => $questionnaire,
						"customer" => $customer,
						"foreignKey" => $foreignKey,
						"user" => $user,
						"date" => date("Y-m-d H:i:s"),
						"questionnaireCode" => $qForm["code"],
						"questionnaireName" => $qForm["name"],
						"answers" => $this->json($answers),
						"hasBadValue" => ($return["hasBadValue"]) ? 1 : 0,
						"lowestValue" => $return["lowestValue"],
						"fromWhere" => (!empty($fromWhere)) ? $fromWhere : NULL,
						"fromWhereText" => (!empty($fromWhereText)) ? $fromWhereText : NULL,
					];
					
					#Insert answer row
					$return["answerID"] = $this->model->myInsert($this->model->tables("answers"), $params);
					
					#Update foreign table with answerID
					if($return["answerID"] > 0 AND isset($qForm["type"]) AND isset($qForm["type"]["controller"]) AND isset($qForm["type"]["data"]) AND isset($qForm["type"]["data"]->foreignTable) AND !empty($qForm["type"]["data"]->foreignTable))
					{
						$foreignController = $qForm["type"]["controller"];
						$table = $foreignController->model->tables($qForm["type"]["data"]->foreignTable);
						$foreignController->model->myUpdate($table, ["questionnaireAnswer" => $return["answerID"]], $foreignKey);
						
						#Previous events (Service events)
						if($qForm["type"]["modelName"] == "Service" AND $qForm["type"]["tableName"] == "events")
						{
							$prevEvents = $foreignController->model->select("SELECT id FROM ".$table." WHERE del = '0' prevEvent = :prevEvent", ["prevEvent" => $foreignKey]);
							if(count($prevEvents) > 0)
							{
								$answerRow = $this->model->getAnswer($return["answerID"]);
								$paramsHere = (array)$answerRow;
								$paramsHere["originalAnswer"] = $answerRow->id;
								unset($paramsHere["id"]);
								
								foreach($prevEvents AS $prevEvent)
								{
									$paramsHere["foreignKey"] = $prevEvent->id;
									$answerIDHere = $this->model->myInsert($this->model->tables("answers"), $paramsHere);
									$foreignController->model->myUpdate($table, ["questionnaireAnswer" => $answerIDHere], $prevEvent->id);
								}
							}
						}
					}
					
					#If has bad value --> email to admin(s)
					if($return["hasBadValue"] AND ($return["lowestValue"] === NULL OR $return["lowestValue"] <= 5)) 
					{ 
						$answer = $this->getAnswer($return["answerID"], false);
						$emailController = new \App\Http\Controllers\EmailController;
						include(DIR_VIEWS."emails/questionnaire/badValues.php");
					}
					
					if(strpos($qForm["code"], "newcarSellingEvents") !== false)
					{
						$event = $foreignController->getEvent($foreignKey);
						$emailController = new \App\Http\Controllers\EmailController;
						include(DIR_VIEWS."emails/crm/newcarSellingEmailQuestionnaireFinished.php");
					}
					
					if($return["lowestValue"] !== NULL AND $return["lowestValue"] >= 9)
					{
						$answer = $this->getAnswer($return["answerID"], false);
						$emailController = new \App\Http\Controllers\EmailController;
						include(DIR_VIEWS."emails/questionnaire/onlyGoodValues.php");
					}
				}
			}
		}
		
		#Log and return
		if($return["success"]) { $logName = "success"; }
		else { $logName = "error"; }
		$this->log("answer-".$logName, $questionnaire, $customer, $foreignKey, ["requestedURL" => (isset($GLOBALS["URL"])) ? $GLOBALS["URL"]->htaccess : NULL, "json" => $return], $user);
		
		return $return;
	}
	
	#Log
	public function log($typeName, $questionnaire, $customer, $foreignKey, $datas = [], $user = NULL)
	{
		return $this->model->log($typeName, $questionnaire, $customer, $foreignKey, $datas, $user);
	}
	
	#Get input watch
	public function getInputWatch($id)
	{
		$row = $this->model->getInputWatch($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["question"] = $row->question;
			$return["badValuesString"] = $row->badValues;
			
			$badValues = str_replace(["\r", "\n"], ["XXXXX", "XXXXX"], $return["badValuesString"]);
			$badValues = str_replace("XXXXXXXXXX", "XXXXX", $badValues);
			$return["badValues"] = explode("XXXXX", $badValues);
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getInputWatchByQuestion($question, $delCheck = 1)
	{
		$id = $this->model->getInputWatchByQuestion($question, "id", $delCheck);
		if(!empty($id)) { $return = $this->getInputWatch($id); }
		else { $return = false; }
		return $return;
	}
	
	#Get answer comment
	public function getAnswerComment($id)
	{
		$row = $this->model->getAnswerComment($id);
		if(!empty($row) AND isset($row->id) AND !empty($row->id))
		{
			$return = [];
			$return["data"] = $row;
			$return["id"] = $row->id;
			$return["userID"] = $row->user;
			$return["answerID"] = $row->answer;
			
			$return["userName"] = $return["user"] = $row->userName;
			$return["userPosition"] = $row->userPosition;
			if(!empty($return["userPosition"])) { $return["user"] .= " (".$return["userPosition"].")"; }
			
			$return["comment"] = strip_tags($row->comment);
			$return["commentHTML"] = nl2br($return["comment"], false);
			
			$return["date"] = $row->date;
			$return["dateOut"] = date("Y. m. d. H:i", strtotime($return["date"]));
		}
		else { $return = false; }
		
		return $return;
	}
	
	public function getAnswerComments($answer = NULL, $user = NULL, $key = "id", $deleted = 0, $orderBy = "date DESC")
	{
		$return = [];
		$fields = "id";
		if(!empty($key) AND $key != "id") { $fields .= ", ".$key; }
		$rows = $this->model->getAnswerComments($answer, $user, $deleted, $fields, $orderBy);
		if(count($rows) > 0)
		{
			foreach($rows AS $i => $row) 
			{ 
				if(!empty($key)) { $keyHere = $row->$key; }
				else { $keyHere = $i; }
				$return[$keyHere] = $this->getAnswerComment($row->id); 
			}
		}
		
		return $return;
	}
	
	public function newAnswerComment($answer, $comment)
	{
		$params = [
			"user" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["id"] : NULL,
			"userName" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["name"] : NULL,
			"userPosition" => (isset($GLOBALS["user"])) ? $GLOBALS["user"]["position"]->nameOut : NULL,
			"answer" => $answer,
			"date" => date("Y-m-d H:i:s"),
			"comment" => $comment,
		];
		
		$id = $this->model->myInsert($this->model->tables("answerComments"), $params);
		
		$emailController = new \App\Http\Controllers\EmailController;
		include(DIR_VIEWS."emails/questionnaire/newComment.php");
		
		return $id;
	}
	
	public function exportAnswersIntoExcel_IncludeAutoloadPath()
	{
		return base_path("app/Http/Controllers/PHPSpreadsheet/vendor/autoload.php");
	}
	
	public function exportAnswersIntoExcel_CreateFile($questionnaireIDs = [])
	{
		$return = [
			"success" => true,
			"error" => NULL,
			"errorMessage" => NULL,
			"fileName" => base_path()."/_questionnaire_answers_exports/".date("Ymd")."_Kerdoiv_export_".uniqid().".xlsx",
			"questionnaires" => [],
		];
			
		#Export is not empty
		if(count($questionnaireIDs) > 0)
		{
			#Excel object
			if(include($this->exportAnswersIntoExcel_IncludeAutoloadPath()))
			{
				$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

				#Basic settings
				$excel->getProperties()->setCreator("Gablini Kft.");
				$excel->getProperties()->setLastModifiedBy("Gablini Kft.");
				$excel->getProperties()->setTitle("Kérdőív válaszok");
				$excel->getProperties()->setSubject("Kérdőív válaszok");
				$excel->getProperties()->setDescription("Kérdőív válaszok");
				
				#Questionnaire list
				$excel->setActiveSheetIndex(0);
				$sheet = $excel->getActiveSheet();
				$sheet->setTitle("KÉRDŐÍVEK");
				
				$sheet->SetCellValue("A1", "Munkalap neve");
				$sheet->SetCellValue("B1", "Kérdőív neve");
				$sheet->SetCellValue("C1", "Kérdőív kódja");
				$sheet->SetCellValue("D1", "Kérdőív URL");
				
				$sheet->getStyle("A1:D1")->getFont()->setBold(true);
				
				$rowIndex = 2;
				foreach($questionnaireIDs AS $questionnaireID)
				{
					$row = $this->model->getQuestionnaire($questionnaireID);
					if(!empty($row) AND isset($row->id) AND !empty($row->id))
					{
						$return["questionnaires"][$row->id] = $row;
						
						$sheet->SetCellValue("A".$rowIndex, $row->excelExportName);
						$sheet->SetCellValue("B".$rowIndex, $row->name);
						$sheet->SetCellValue("C".$rowIndex, $row->code);
						$sheet->SetCellValue("D".$rowIndex, $row->url);
						
						$rowIndex++;
					}
				}
				foreach(range("A", "D") as $columnName) { $sheet->getColumnDimension($columnName)->setAutoSize(true); }
				
				#Set cursor to first sheet
				$sheet->setSelectedCell("A1");

				#Output
				ob_end_clean();
				$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, "Xlsx");
				$writer->save($return["fileName"]);
			}
			else
			{
				$return["success"] = false;
				$return["error"] = "include-PHPSpreadsheet";
			}
		}
		else
		{
			$return["success"] = false;
			$return["error"] = "count-questionnaire-id-list-0";
		}
		
		return $return;
	}
	
	public function exportAnswersIntoExcel_CreateWorksheet($filePath, $questionnaire, $answersLimit = NULL)
	{
		if(include($this->exportAnswersIntoExcel_IncludeAutoloadPath()))
		{
			#Open excel
			$excel = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
			
			#Create sheet
			$sheet = $excel->createSheet();
			$sheet->setTitle($questionnaire->excelExportName);
			
			#Datas
			$answerRows = $this->getAnswers($questionnaire->id, "id", NULL, NULL, NULL, 0, "customer, date DESC", $answersLimit);
			if(count($answerRows) > 0)
			{
				#Header row
				$rowIndex = 1;
				$colIndex = 1;
				
				$fields = [
					"date" => "Kitöltés időpontja",
					"customerName" => "Ügyfél",
					"customerEmail" => "Ügyfél e-mail címe",
					"answerByUser" => "Kitöltő",
					"hasBadValue" => "Alacsony értékelés?",
					"separator1" => "*"
				];
				foreach($fields AS $fieldKey => $field)
				{
					$sheet->getCellByColumnAndRow($colIndex, $rowIndex)->setValue($field);
					$colIndex++;
				}
				
				$firstRowDatas = $answerRows[array_keys($answerRows)[0]];
				if(count($firstRowDatas["answers"]) > 0)
				{
					foreach($firstRowDatas["answers"] AS $answer)
					{
						$sheet->getCellByColumnAndRow($colIndex, $rowIndex)->setValue($answer["originalName"]);
						$colIndex++;
					}
				}
				
				$colIndexLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
				$sheet->getStyle("A".$rowIndex.":".$colIndexLetter.$rowIndex)->getFont()->setBold(true);
				
				#Answer rows
				foreach($answerRows AS $answerRow)
				{
					$rowIndex++;
					$colIndex = 1;
					
					$fields = [
						"date" => $answerRow["dateOut"],
						"customerName" => $answerRow["customerData"]["Név"],
						"customerEmail" => $answerRow["customerData"]["E-mail cím"],
						"answerByUser" => ($answerRow["answerByUser"]) ? "Munkatárs" : "Ügyfél",
						"hasBadValue" => ($answerRow["hasBadValue"]) ? "IGEN" : "Nem",
						"separator1" => "*",
					];
					
					foreach($fields AS $fieldKey => $field)
					{
						$sheet->getCellByColumnAndRow($colIndex, $rowIndex)->setValue($field);
						
						if($fieldKey == "hasBadValue" AND $answerRow["hasBadValue"])
						{
							$colIndexLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
							$sheet->getStyle($colIndexLetter.$rowIndex)->applyFromArray(["font" => ["color" => ["rgb" => "FF0000"]]]);
						}
						
						$colIndex++;
					}
					
					if(count($answerRow["answers"]) > 0)
					{
						foreach($answerRow["answers"] AS $answer)
						{
							$sheet->getCellByColumnAndRow($colIndex, $rowIndex)->setValue(strip_tags($answer["val"]));
							
							if($answer["badValue"])
							{
								$colIndexLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
								$sheet->getStyle($colIndexLetter.$rowIndex)->applyFromArray(["font" => ["color" => ["rgb" => "FF0000"]]]);
							}
							
							$colIndex++;
						}
					}
				}
			}
			
			$answerRows = NULL;
			unset($answerRows);
			
			#Set cursor to first sheet
			$sheet->setSelectedCell("A1");
			
			$excel->setActiveSheetIndex(0);
			$sheet = $excel->getActiveSheet();
			$sheet->setSelectedCell("A1");
			
			#Output
			// ob_end_clean();
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, "Xlsx");
			$writer->save($filePath);	
		}
	}
	
	public function exportAllQuestionnaireAnswersIntoExcelToFTP()
	{
		$return = [
			"excelFile" => $this->exportAnswersIntoExcel_CreateFile([16, 5, 4, 3, 8, 6, 7, 9, 10, 12, 11, 14, 13, 15]),
			"questionnaires" => [],
		];
		if(count($return["excelFile"]["questionnaires"]) > 0)
		{
			foreach($return["excelFile"]["questionnaires"] AS $questionnaire)
			{
				$return["questionnaires"][$questionnaire->id] = $this->exportAnswersIntoExcel_CreateWorksheet($return["excelFile"]["fileName"], $questionnaire, NULL);
			}
		}
		
		return $return;
	}
	
	public function exportFromWheres($dateFrom = NULL, $dateTo = NULL, $sendEmail = true, $dateTextForEmail = NULL, $addressListURL = NULL)
	{
		#Return array
		$return = [
			"rows" => [],
			"fromWhereList" => $this->model->fromWheres(),
			"question" => $this->model->fromWhereQuestion,
			"stats" => [
				"service" => [
					"name" => "Szerviz események",
					"fromWhereList" => [],
					"sum" => 0,
				],
				"newCarSelling" => [
					"name" => "Új autó eladás",
					"fromWhereList" => [],
					"sum" => 0,
				],
				"all" => [
					"name" => "Összesen",
					"fromWhereList" => [],
					"sum" => 0,
				],
			],
			"otherTexts" => [],
		];
		
		if(count($return["fromWhereList"]) > 0)
		{
			foreach($return["stats"] AS $statKey => $stat)
			{
				foreach($return["fromWhereList"] AS $fromWhereKey => $fromWhere)
				{
					if($fromWhere["active"]) { $return["stats"][$statKey]["fromWhereList"][$fromWhereKey] = 0; }
				}
			}
		}
		
		#Query
		$query = "SELECT id, questionnaire, customer, foreignKey, user, date, questionnaireCode, fromWhere, fromWhereText FROM ".$this->model->tables("answers")." WHERE del = '0' AND originalAnswer IS NULL AND fromWhere IS NOT NULL";
		$params = [];
		
		if($dateFrom !== NULL)
		{
			$query .= " AND date >= :dateFrom";
			$params["dateFrom"] = $dateFrom;
		}
		
		if($dateTo !== NULL)
		{
			$query .= " AND date <= :dateTo";
			$params["dateTo"] = $dateTo;
		}
		
		#Store rows into array
		$rows = $this->model->select($query, $params);
		foreach($rows AS $row)
		{
			$return["rows"][$row->id] = $row;
			
			$currentStatKey = (mb_stripos($row->questionnaireCode, "serviceEvents", 0, "utf-8") !== false) ? "service" : "newCarSelling";
			if(!isset($return["stats"][$currentStatKey]["fromWhereList"][$row->fromWhere]))
			{
				foreach($return["stats"] AS $statKey => $stat) { $return["stats"][$statKey]["fromWhereList"][$row->fromWhere] = 0; }
			}
			
			$return["stats"][$currentStatKey]["fromWhereList"][$row->fromWhere]++;
			$return["stats"]["all"]["fromWhereList"][$row->fromWhere]++;
			
			$return["stats"][$currentStatKey]["sum"]++;
			$return["stats"]["all"]["sum"]++;
			
			if($row->fromWhere == "egyeb")
			{
				$return["otherTexts"][$row->id] = [
					"statKey" => $currentStatKey,
					"answerID" => $row->id,
					"customerID" => $row->customer,
					"fromWhereText" => $row->fromWhereText,
				];
			}
		}
		
		#Send e-mail
		if($sendEmail)
		{
			$emailController = new \App\Http\Controllers\EmailController;
			include(DIR_VIEWS."emails/questionnaire/fromWheresReport.php");	
		}
		
		#Return
		// echo "<pre>"; print_r($return);
		return $return;
	}
	
	public function exportFromWheresLastWeek()
	{
		$dateFrom = date("Y-m-d 00:00:00", strtotime("-1 week"));
		$dateTo = date("Y-m-d 23:59:59", strtotime("-1 day"));
		
		$dateTextForEmail = "Előző hét";
		$addressListURL = "crm-kerdoiv-kitoltes-honnan-ertesult-rolunk-heti-ertesito";
		
		return $this->exportFromWheres($dateFrom, $dateTo, true, $dateTextForEmail, $addressListURL);
	}
	
	#JSON encode and decode
	public function json($array)
	{
		return $this->model->json($array);
	}
	
	public function jsonDecode($string)
	{
		return $this->model->jsonDecode($string);
	}

    private function checkFromWhereEgyebRequired($fromWhere, $datas, array $return)
    {
        if ($fromWhere == "egyeb" AND (!isset($datas["_fromWhereText"]) || $datas["_fromWhereText"] == "")) {
            return false;
        }
        return true;
    }

    private function getQuestionId($questionID)
    {
        if (isset($questionID) && $questionID != null) {
            return $questionID;
        } else {
            return uniqid();
        }
    }
}
