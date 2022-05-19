<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsFillingStats extends Command
{
    protected $signature = "crm:serviceEventsFillingStats";
    protected $description = "Szerviz eseményekről havi statisztika";

    public function handle()
    {
		$timeStart = microtime(true);
		$services = new \App\Http\Controllers\ServiceController;
		$returnDatas = $services->statEventsFillingsMonthly();	
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
