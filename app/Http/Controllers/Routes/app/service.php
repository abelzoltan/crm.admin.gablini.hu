<?php
if(isset($_POST["process"]) AND $_POST["process"])
{
	$email = new \App\Http\Controllers\EmailController;
	$return = $email->contactMessage($_POST);
	if($return["type"] == "success") 
	{ 
		#Email
		$msg = $email->getContactMessage($return["id"]);
		include(DIR_VIEWS."emails/app/email-new-contact.php");
		
		#Átirányítás
		$_SESSION[SESSION_PREFIX."form-finish"] = true;
		$URL->header($_POST["_referer"]);
	}
	else
	{
		$_SESSION[SESSION_PREFIX."form-data"] = $return;
		$URL->header($_POST["_referer"]);
	}
	exit;
}
else
{	
	$VIEW["name"] = $routes[0];
	$VIEW["title"] = $VIEW["vars"]["navMain"][$routes[0]];
	
	$premises = new \App\Http\Controllers\PremiseController;
	$this->MAIN["premises"] = $premises->getAddresses();
	
	$form = new \App\Http\Controllers\FormController;
	$this->MAIN["FORM"] = $form;
	
	$VIEW["vars"]["car"] = false;
	if(isset($_GET["car"]) AND !empty($_GET["car"]))
	{
		$VIEW["vars"]["car"] = $customers->getCar($_GET["car"]);
		if($VIEW["vars"]["car"] !== false)
		{
			if($VIEW["vars"]["car"]["data"]->customer != $customer["id"]) { $VIEW["vars"]["car"] = false; }
			else { $VIEW["bodyID"] = mb_strtolower($VIEW["vars"]["car"]["data"]->brand, "UTF-8"); }
		}
	}
}
?>