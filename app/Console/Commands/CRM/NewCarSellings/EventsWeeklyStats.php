<?php
namespace App\Console\Commands\CRM\NewCarSellings;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsWeeklyStats extends Command
{
    protected $signature = "crm:newcarSellingEventsWeeklyStats";
    protected $description = "Új autó eladásokról heti statisztika";

    public function handle()
    {
		$timeStart = microtime(true);
		$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
		$returnDatas = $newcarSellings->statEventsWeekly();	
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
