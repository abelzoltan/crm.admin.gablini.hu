<?php 
if(isset($_POST["process"]) AND $_POST["process"])
{
	$return = $users->forgotPassword($_POST["email"]);
	
	#Success
	if(empty($return["errorKey"]))
	{
		#Email
		$email = new App\Http\Controllers\EmailController;
		$email->frameName = "gablini";
		$email->variables = [
			"PATH_WEB" => PATH_WEB,
			"header" => $GLOBALS["site"]->data->name,
			"date" => $return["params"]["date"],
			"forgotLink" => PATH_WEB."new-password/".$return["params"]["hash"],
			"name" => $return["user"]["name"],
		];
		
		#Send to user
		$email->subject = "Új jelszó igénylés - ".$GLOBALS["site"]->data->name;
		$email->body = $email->setBody("users-forgot-password");
		$email->addresses = [
			["type" => "to", "email" => $return["user"]["data"]->email, "name" => $return["user"]["name"]],
			// ["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
			// ["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
		];
		$email->send();
		$URL->redirect([$routes[0]], ["success" => "email"]);
	}
	#Error
	else { $URL->redirect([$routes[0]], ["error" => "email"]); }
}
else 
{ 
	$VIEW["title"] = "Elfelejtett jelszó";
	$VIEW["name"] = "without-login.forgot-password"; 
}
?>