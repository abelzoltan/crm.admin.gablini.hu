<?php
namespace App\Console\Commands\CRM\NewCarSellings;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsDailyReport extends Command
{
    protected $signature = "crm:newcarSellingEventsDailyReport";
    protected $description = "Új autó eladások kezeléséről napi jelentés";

    public function handle()
    {
		$timeStart = microtime(true);
		$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
		$returnDatas = $newcarSellings->getEventsForDailyReport();	
		$returnDatas2 = $newcarSellings->getEventsForDailyReportBrands();	
		$timeEnd = microtime(true);
		$executionTimeSec = $timeEnd - $timeStart;
		
		$return = [
			"datas" => [
				"all" => $returnDatas,
				"brands" => $returnDatas2,
			],
			"timeStart" => $timeStart,
			"timeEnd" => $timeEnd,
			"executionTimeSec" => $executionTimeSec,
		];
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
