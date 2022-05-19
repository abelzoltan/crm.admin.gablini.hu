<?php
$questionnaires = new \App\Http\Controllers\QuestionnaireController;
$qRow = $questionnaires->model->getQuestionnaire($eventDetails["data"]->questionnaire);

$emailController->frameName = "gablini-questionnaire";
$emailController->variables = [
	"PATH_WEB" => env("PATH_CRM_WEB"),
	"bodyBg" => $qRow->color3,
	"headerBorder" => $qRow->color2,
	"headerBg" => $qRow->color1,
	"siteName" => "Gablini Kft.",
	"name" => $eventDetails["customerName"],
	"qLink" => $eventDetails["questionnaireLink"],
	"eventSubject" => $eventDetails["text"],
	"logo" => "cid:".$qRow->logo,
	"signature" => "cid:logo-gablini-email.png",
];

$emailController->images = [
	[
		"path" => $questionnaires->picDirInner.$qRow->logo,
		"web" => $questionnaires->picDir.$qRow->logo,
		"name" => $qRow->logo,
	],
	[
		"path" => public_path("pics/logo-gablini-email.png"),
		"web" => env("PATH_CRM_WEB")."pics/logo-gablini-email.png",
		"name" => "logo-gablini-email.png",
	],
];

$emailController->subject = $email->emailSubject;
$emailController->body = $emailController->setBody("crm/".$email->email);
$emailController->addresses = [
	["type" => "to", "email" => $eventDetails["customer"]["email"], "name" => $eventDetails["customerName"]],
	// ["type" => "bcc", "email" => "mate@juizz.hu", "name" => "Nagy Máté"],
	// ["type" => "bcc", "email" => "info@juizz.hu", "name" => "Juizz Info"],
];

$emailController->send();
?>