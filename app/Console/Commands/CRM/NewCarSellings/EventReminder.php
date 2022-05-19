<?php
namespace App\Console\Commands\CRM\NewCarSellings;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventReminder extends Command
{
    protected $signature = "crm:newcarSellingEventReminder";
    protected $description = "Új autó eladásokról űrlap kitöltés emlékeztető";

    public function handle()
    {
		$timeStart = microtime(true);
		$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
		$returnDatas = $newcarSellings->emailsForCron();	
		$timeEnd = microtime(true);
		$executionTimeSec = $timeEnd - $timeStart;
		
		$return = [
			"datas" => $returnDatas,
			"timeStart" => $timeStart,
			"timeEnd" => $timeEnd,
			"executionTimeSec" => $executionTimeSec,
		];
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
