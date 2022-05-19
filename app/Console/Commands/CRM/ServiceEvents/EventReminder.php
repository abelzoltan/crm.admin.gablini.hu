<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventReminder extends Command
{
    protected $signature = "crm:serviceEventReminder";
    protected $description = "Szerviz eseményről űrlap kitöltés emlékeztető";

    public function handle()
    {
		$timeStart = microtime(true);
		$services = new \App\Http\Controllers\ServiceController;
		$returnDatas = $services->emailsForCron();	
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
