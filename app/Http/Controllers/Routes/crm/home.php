<?php 
$stat = new \App\Http\Controllers\StatisticController;

#Customers
$VIEW["vars"]["customerCount"] = $stat->customerCount();
$VIEW["vars"]["customersFromWhere"] = $stat->customersFromWhere();
$VIEW["vars"]["usersForSearch"] = $customers->getUserListForSearch();

#Contact details for chart (year, month) 
$VIEW["vars"]["contactStat"] = $stat->emailContactMessagesCount(date("Y-m-01"));
$VIEW["vars"]["contactStatYear"] = $stat->emailContactMessagesCount(date("Y-01-01"), "Weboldal ajánlatkérések idén", "%Y");

#View
$VIEW["name"] = "home";
?>