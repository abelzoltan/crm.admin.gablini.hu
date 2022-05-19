<?php
if(isset($_POST["login-process"]) AND $_POST["login-process"])
{
	$errorKey = $users->login($_POST["email"], $_POST["password"]);
	#Success
	if(empty($errorKey)) 
	{ 
		if(isset($_POST["remember"]) AND $_POST["remember"])
		{
			$userHere = $users->getUser($_SESSION[USER_ID_KEY]);
			$rememberToken = sha1($userHere["id"]."-".date("YmdHis")."-".$userHere["token"]);
			$users->editUser($userHere["id"], ["remember_token" => $rememberToken]);
			setcookie("rememberMe", $rememberToken, time() + (86400 * 30), "/"); 
		}
		
		$link = PATH_WEB;
		if(!empty($_POST["login-url"])) { $link .= $_POST["login-url"]; }
		if(!empty($_POST["login-get"])) { $link .= "?".$_POST["login-get"]; }
		$URL->header($link); 
	}
	#Error
	else 
	{ 
		if($errorKey == "rank") { $error = "rank"; }
		elseif($errorKey == "del") { $error = "deleted"; }
		else { $error = "datas"; }
		$URL->redirect([], ["error" => $error, "url" => $_POST["login-url"], "get" => $_POST["login-get"]]);
	}
}
else { $URL->redirect(); }
?>
