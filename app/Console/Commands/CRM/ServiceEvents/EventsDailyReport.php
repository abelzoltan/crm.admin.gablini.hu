<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsDailyReport extends Command
{
    protected $signature = "crm:serviceEventsDailyReport";
    protected $description = "Szerviz események kezeléséről napi jelentés";

    public function handle()
    {
		$timeStart = microtime(true);
		$services = new \App\Http\Controllers\ServiceController;
		$returnDatas = $services->getEventsForDailyReport();	
		$returnDatas2 = $services->getEventsForDailyReportPremise();	
		$timeEnd = microtime(true);
		$executionTimeSec = $timeEnd - $timeStart;
		
		$return = [
			"datas" => [
				"all" => $returnDatas,
				"premises" => $returnDatas2,
			],
			"timeStart" => $timeStart,
			"timeEnd" => $timeEnd,
			"executionTimeSec" => $executionTimeSec,
		];
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
