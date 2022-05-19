<?php 
if(isset($_POST["process"]) AND $_POST["process"])
{
	$return = $users->setForgotPassword($_POST["hash"], $_POST["password1"], $_POST["password2"]);
	if($return["success"]) { $URL->redirect([], ["success" => "new-password"]); }
	elseif($return["errorKey"] == "hash") { $URL->redirect(["forgot-password"], ["error" => "token"]); }
	elseif($return["errorKey"] == "passwordMismatch") { $URL->redirect([$routes[0], $_POST["hash"]], ["error" => "password"]); }
}
else
{
	if(isset($routes[1]))
	{
		$id = $users->getForgotPassword($routes[1]);
		if(isset($id) AND !empty($id)) 
		{
			$VIEW["title"] = "Új jelszó igénylése";
			$VIEW["name"] = "without-login/new-password"; 
		}
		else { $URL->redirect(["forgot-password"], ["error" => "token"]); }
	}
	else { $URL->redirect(); }
}	
?>