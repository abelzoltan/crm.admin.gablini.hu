<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Email extends Base
{	
	#Tables
	public function tables($name = "")
	{
		$return = [
			"emails" => $this->dbPrefix."emails",
			"addresses" => $this->dbPrefix."emails_addresses",
			"contactMessages" => $this->dbPrefix."emails_contactMessages",
			"fromReplyTo" => $this->dbPrefix."emails_from_replyTo",
			"sent" => $this->dbPrefix."emails_sent",
				"sent_addresses" => $this->dbPrefix."emails_sent_addresses",
			"skins" => $this->dbPrefix."emails_skins",
			"newsletter" => $this->dbPrefix."emails_newsletter",
				"newsletterRebounds" => $this->dbPrefix."emails_newsletter_rebounds",
				"newsletterInvalids" => $this->dbPrefix."emails_newsletter_invalids",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get contact message
	public function getContactMessages($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("contactMessages"), "id", $id, $field, $delCheck);
	}
	
	public function getContactMessagesByHash($hash, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("contactMessages"), "hash", $hash, $field, $delCheck);
	}
	
	#Newsletter - Robinson-list
	public function newsLetterRobinson($dateFrom = NULL, $dateTo = NULL)
	{
		$table = $this->tables("newsletter");
		$query = "SELECT * FROM ".$table." WHERE del = '1'";
		$params = [];
		
		if(!empty($dateFrom))
		{
			$query .= " AND dateUnsubscribe >= :dateUnsubscribe1";
			$params["dateUnsubscribe1"] = $dateFrom;
		}
		if(!empty($dateTo))
		{
			$query .= " AND dateUnsubscribe <= :dateUnsubscribe2";
			$params["dateUnsubscribe2"] = $dateTo;
		}
		
		$query .= " GROUP BY emailUser, emailDomain ORDER BY emailDomain, emailUser";
		
		return $this->select($query, $params);
	}
	
	#Newsletter - Get lists
	public function newsLetterList($siteList = [], $dateFrom = NULL, $dateTo = NULL)
	{
		$table = $this->tables("newsletter");
		$query = "SELECT * FROM ".$table." WHERE del = '0'";
		$params = [];
		
		if(!empty($siteList))
		{
			$query .= " AND (";
			$queryArray = [];
			foreach($siteList AS $i => $siteID)
			{
				$queryArray[] = "site = :site".$i;
				$params["site".$i] = $siteID;
			}
			$query .= implode(" OR ", $queryArray);
			$query .= ")";
		}
		if(!empty($dateFrom))
		{
			$query .= " AND dateSubscribe >= :dateSubscribe1";
			$params["dateSubscribe1"] = $dateFrom;
		}
		if(!empty($dateTo))
		{
			$query .= " AND dateSubscribe <= :dateSubscribe2";
			$params["dateSubscribe2"] = $dateTo;
		}
		
		$query .= " GROUP BY emailUser, emailDomain ORDER BY emailDomain, emailUser";
		return $this->select($query, $params);
	}
	
	public function newsLetterInvalids()
	{
		return $this->select("SELECT email FROM ".$this->tables("newsletterInvalids")." WHERE del = '0' GROUP BY email ORDER BY email");
	}
	
	public function newsLetterRebounds()
	{
		return $this->select("SELECT email FROM ".$this->tables("newsletterRebounds")." WHERE del = '0' GROUP BY email ORDER BY email");
	}
	
	public function newsLetterUnsubscribes($date = NULL, $emailUser, $emailDomain)
	{
		if($date === NULL) { $date = date("Y-m-d H:i:s"); }
		$params = [
			"date" => $date,
			"emailUser" => $emailUser,
			"emailDomain" => $emailDomain,
		];
		return $this->select("SELECT * FROM ".$this->tables("newsletter")." WHERE del = '1' AND dateUnsubscribe >= :date AND emailUser = :emailUser AND emailDomain = :emailDomain", $params);
	}
}
?>