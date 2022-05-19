<?php
#Basic settings
$this->MAIN = ["URL" => $GLOBALS["URL"]->getURLdata()];
$this->MAIN["SITE"] = $site->data;
$VIEW["vars"] = [];
$routes = $GLOBALS["URL"]->routes;
$siteURL = $site->data->url;


$this->MAIN["headerLink"] = $headerLink = "https://gablini.hu";
$this->MAIN["error"] = NULL;
$qID = $cID = $fID = $uID = NULL;

if(!isset($routes[0]) OR empty($routes[0])) { $URL->header($headerLink); }
else
{
	#Meta
	$VIEW["titlePrefix"] = $site->data->titlePrefix;
	$VIEW["titleSuffix"] = $site->data->titleSuffix;
	$VIEW["title"] = "";
	$VIEW["nameDir"] = "";
	$VIEW["name"] = "questionnaire"; 
	$VIEW["meta"] = [
		"keywords" => "",
		"description" => "Gablini Kérdőív kitöltés",
		"og:title" => $VIEW["title"],
		"og:image" => PATH_ROOT_WEB."pics/logo-facebook.png",
		"og:description" => "Gablini Kérdőív kitöltés",
		"og:site_name" => $site->data->name,
		"og:type" => "website",
		"og:url" => $GLOBALS["URL"]->currentURL,
	];
	
	#Get questionnaire and customer
	$qForm = new \App\Http\Controllers\QuestionnaireController;	
	$row = $qForm->getQuestionnaireByURL($routes[0]);

	if($row !== false)
	{
		$qID = $row["id"];
		#Active error
		if(!empty($row["activeError"])) 
		{ 
			if($row["activeError"] == "activeFrom") { $this->MAIN["error"] = "active-from"; } 
			elseif($row["activeError"] == "activeTo") { $this->MAIN["error"] = "active-to"; } 
			else { $this->MAIN["error"] = "active"; } 
		}
		#No questions
		elseif(count($row["questions"]) == 0) { $this->MAIN["error"] = "no-questions"; }
		else
		{
			if(!isset($routes[1]) OR empty($routes[1])) { $this->MAIN["error"] = "no-customer-hash"; }
			else
			{			
				$customers = new \App\Http\Controllers\CustomerController;
				$customer = $customers->getCustomerByHash($routes[1]);
				if($customer !== false)
				{
					$cID = $customer["id"];				
					
					if(!isset($routes[2]) OR empty($routes[2])) { $this->MAIN["error"] = "no-foreign-hash"; }
					else
					{
						if(!isset($row["type"]) OR !isset($row["type"]["controller"])) { $this->MAIN["error"] = "no-foreign-controller"; }
						else
						{
							$foreignController = $row["type"]["controller"];
							$foreignMethod = $row["type"]["data"]->foreignMethod;
							$foreignRow = $foreignController->$foreignMethod($routes[2]);
							if($foreignRow !== false)
							{
								$fID = $foreignRow["id"];
								if($foreignRow["data"]->customer == $customer["data"]->id)
								{
									$this->MAIN["questionnaire"] = $row;
									$VIEW["title"] = $VIEW["meta"]["og:title"] = $row["name"];
									
									$this->MAIN["customer"] = $customer;
									$this->MAIN["foreignRow"] = $foreignRow;
									$VIEW["vars"]["customerCodeName"] = $customers->codeName;
									
									#Answer exists
									if(!empty($foreignRow["data"]->questionnaireAnswer)){ $this->MAIN["error"] = "existing-answer"; }
									elseif($qForm->answerExists($qID, $cID, $fID) !== false){ $this->MAIN["error"] = "existing-answer"; }
									else
									{
										if(isset($foreignRow["data"]->questionnaire) AND !empty($foreignRow["data"]->questionnaire) AND $foreignRow["data"]->questionnaire != $row["id"]){ $this->MAIN["error"] = "wrong-questionnaire"; }
										else
										{
											#User
											if(isset($routes[3]) AND !empty($routes[3]))
											{
												$uID = $GLOBALS["users"]->model->getUserByToken($routes[3], "id");
												if(!isset($uID) OR empty($uID) OR !$uID) { $uID = NULL; }
											}
											
											#From where
											$this->MAIN["questionnaireModel"] = $qForm->model;
											
											### OKAY ###
											if(isset($_POST["process"]) AND $_POST["process"]) 
											{
												if(!isset($_POST["questionnaire"]) OR $_POST["questionnaire"] != $row["url"]) { $postError = "questionnaire"; }
												elseif(!isset($_POST["customer"]) OR $_POST["customer"] != $customer["code"]) { $postError = "customer"; }
												elseif(!isset($_POST["hash1"]) OR $_POST["hash1"] != $routes[1]) { $postError = "hash1"; }
												elseif(!isset($_POST["hash2"]) OR $_POST["hash2"] != $routes[2]) { $postError = "hash2"; }
												else { $postError = NULL; }
												
												if($postError !== NULL)
												{
													$datas = [
														"requestedURL" => $URL->htaccess,
														"error" => $postError,
														"json" => $_POST,
													];
													$qForm->log("process-error", $qID, $cID, $fID, $datas, $uID);
													$URL->header($headerLink);
												}
												else
												{
													$_SESSION[SESSION_PREFIX."answerReturn"] = $qForm->newAnswer($qID, $cID, $fID, $uID, $_POST);
													$URL->redirect($routes);
												}
											}
										}
									}
								}
								#Foreign row error
								else { $this->MAIN["error"] = "customer-id-match"; }
							}
							else { $this->MAIN["error"] = "wrong-foreign-hash"; }
						}
					}
				}
				#Customer error
				else { $this->MAIN["error"] = "wrong-customer-hash"; }
			}
		}
	}
	#Questionnaire error
	else { $this->MAIN["error"] = "no-match"; }
}

#Logo
if(!empty($this->MAIN["error"])) { $VIEW["vars"]["logo"] = $qForm->picDir."gablini.png"; }
elseif($row !== false AND !empty($row["logo"])) { $VIEW["vars"]["logo"] = $row["logo"]; }
else { $VIEW["vars"]["logo"] = ""; }

#Log view
$datas = [
	"requestedURL" => $URL->htaccess,
	"error" => $this->MAIN["error"],
];
$qForm->log("view", $qID, $cID, $fID, $datas, $uID);
?>
