<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsMonthlyStats extends Command
{
    protected $signature = "crm:serviceEventsMonthlyStats";
    protected $description = "Szerviz eseményekről havi statisztika";

    public function handle()
    {
		$timeStart = microtime(true);
		$services = new \App\Http\Controllers\ServiceController;
		$returnDatas = $services->statEventsMonthly();	
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
