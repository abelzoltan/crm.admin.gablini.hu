<?php
#View
if(isset($_POST["process"]) AND $_POST["process"])
{
	$return = $app->changeLoyaltyCardNumber($appUser["data"]->id, $_POST);
	
	if($return["type"] == "success") { $_SESSION[SESSION_PREFIX."form-finish"] = true; }
	else { $_SESSION[SESSION_PREFIX."form-data"] = $return; }
	
	$URL->header($_POST["_referer"]);
	exit;
}
else
{	
	$VIEW["name"] = $routes[0];
	$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];

	$form = new \App\Http\Controllers\FormController;
	$this->MAIN["FORM"] = $form;
}
?>