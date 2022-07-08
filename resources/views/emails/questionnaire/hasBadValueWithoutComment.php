<?php

#Basic datas
$emailController->frameName = "gablini";
$emailController->variables = [
//    "PATH_WEB" => PATH_WEB,
    "header" => $GLOBALS["site"]->data->name,
    "siteName" => $GLOBALS["site"]->data->name,
//    "qLink" => PATH_CRM_WEB."questionnaire-answers/details/".$answer["id"],
    "links" => "",
];

$emailController->subject .= "Alacsony értékelést kaptunk megjegyzés nélkül";
$emailController->body = $emailController->setBody("questionnaire/hasBadValuesWithoutComments");
$addressListURL = "crm-kerdoiv-kitoltes-alacsony-ertekeles-megjegyzes-nelkul";

foreach ($return["links"] as $link) {
    $emailController->variables["links"] .= "
        <tr>
            <td style='width: 100%; padding: 5px 1%; border: 1px solid #000; font-weight: bold;'><a href=$link target='_blank'>$link</a></td>
        </tr>
    ";
}

if(isset($addressListURL))
{
    $webAddresses = new \App\Http\Controllers\WebAddressController;
    $addressList = $webAddresses->getAddressesForSendingByURL($addressListURL);

    $emailController->addresses = $addressList["all"];
    $emailController->send();
}

    print "<pre>";
    print_r($emailController->variables["links"]);
    print "</pre>";
    die;
