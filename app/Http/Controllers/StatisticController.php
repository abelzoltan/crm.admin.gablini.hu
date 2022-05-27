<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SiteController;
use App\Statistic;

class StatisticController extends BaseController
{
	public $customerCountAllCustomers = NULL;
	public $siteList = NULL;
	
	#Construct
	public function __construct($connectionData = [])
	{
		$this->modelName = "Statistic";
		$this->model = new \App\Statistic($connectionData);
		
		if(!isset($GLOBALS["site"])) { $GLOBALS["site"] = new SiteController; }
		$this->siteList = $GLOBALS["site"]->getSites("url", "id");
	}
	
	#Customer count (All, This year, this month, this week)
	public function customerCount()
	{
		#Basic
		$customers = new CustomerController;
		$this->customerCountAllCustomers = $allCustomers = $customers->countCustomers();
		
		#Types
		if(date("N") == 1) {$dateFromWeek = date("Y-m-d");  }
		else { $dateFromWeek = date("Y-m-d", strtotime("last monday")); }
		$types = [
			"all" => [
				"name" => "Összes ügyfél",
				"dateFrom" => "",
			],
			"thisWeek" => [
				"name" => "Új ügyfelek a héten",
				"dateFrom" => $dateFromWeek,
			],
			"thisMonth" => [
				"name" => "Új ügyfelek a hónapban",
				"dateFrom" => date("Y-m-01"),
			],
			"thisYear" => [
				"name" => "Új ügyfelek az évben",
				"dateFrom" => date("Y-01-01"),
			],
		];
		
		#Return
		$return = [];
		foreach($types AS $typeKey => $typeData)
		{
			if($typeKey == "all") { $customerCount = $allCustomers; }
			else { $customerCount = $customers->countCustomers($typeData["dateFrom"]); }
			
			$percentage = ($customerCount["count"] * 100) / $allCustomers["count"];
			$return[$typeKey] = [
				"name" => $typeData["name"],
				"dateFrom" => $typeData["dateFrom"],
				"count" => $customerCount["count"],
				"countFormatted" => $customerCount["formatted"],
				"percentage" => $percentage,
				"percentageFormatted" => number_format($percentage, 2, ",", " ")."%",
			];
		}

		return $return;
	}
	
	#Customers from where
	public function customersFromWhere()
	{
		$customers = new CustomerController;
		return $customers->customersFromWhere($this->customerCountAllCustomers);
	}
	
	#Count email contactMessages from given date
	public function emailContactMessagesCount($date, $name = "Weboldal ajánlatkérések", $dateOutString = "%Y. %B")
	{
		#Basic
		$return = [];
		$siteList = $this->siteList;
		$email = new EmailController;
		$dbWebController = new BaseController(["name" => MYSQL_CONNECTION_NAME_G2]);
		$dbWeb = $dbWebController->model;
		
		$return["name"] = $name;
		$return["date"] = $date;
		$return["dateOut"] = strftime($dateOutString, strtotime($date));
		$return["count"] = 0;
		$return["countFormatted"] = "";
		$return["percentage"] = 100;
		$return["percentageFormatted"] = "100%";
		$return["rows"] = [];

		#New Sites
		$contacts = $email->model->select("SELECT site, count(id) AS count FROM ".$email->model->tables("contactMessages")." WHERE del = '0' AND date >= :date GROUP BY site ORDER BY site", ["date" => $date]);
		foreach($contacts AS $contact)
		{
			$return["count"] += $contact->count;
			$return["rows"][$siteList[$contact->site]->statOrder] = [
				"color" => $siteList[$contact->site]->statColor,
				"name" => ucfirst($siteList[$contact->site]->url),
				"count" => $contact->count,
				"countFormatted" => number_format($contact->count, 0, "", " ")." db",
				"percentage" => 0,
				"percentageChart" => 0,
				"percentageFormatted" => "",
			];
		}

		#Hyundai
		$contacts2 = $dbWeb->select("SELECT id FROM ajanlatkerok_hyundai WHERE allapot = '' AND datum >= :date", ["date" => $date]);
		$hCount = count($contacts2);
		$return["count"] += $hCount;
		$return["rows"][$siteList[4]->statOrder] = [
			"color" => $siteList[4]->statColor,
			"name" => ucfirst($siteList[4]->url),
			"count" => $hCount,
			"countFormatted" => number_format($hCount, 0, "", " ")." db",
			"percentage" => 0,
			"percentageChart" => 0,
			"percentageFormatted" => "",
		];

		#Percentage, SUM format
		ksort($return["rows"], SORT_NUMERIC);
		$return["countFormatted"] = number_format($return["count"], 0, "", " ")." db";
		foreach($return["rows"] AS $statOrder => $row)
		{
            if ($return["count"] && $return["count"] != 0) {
                $return["rows"][$statOrder]["percentage"] = $percentage = ($row["count"] * 100) / $return["count"];
            }
			$return["rows"][$statOrder]["percentageChart"] = number_format($percentage, 2, ".", " ");
			$return["rows"][$statOrder]["percentageFormatted"] = number_format($percentage, 2, ",", " ")."%";
		}
		
		#Return
		return $return;
	}
}
