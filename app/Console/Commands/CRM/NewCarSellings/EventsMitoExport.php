<?php
namespace App\Console\Commands\CRM\NewCarSellings;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsMitoExport extends Command
{
    protected $signature = "crm:newcarSellingEventsMitoExport";
    protected $description = "Új autó eladások eredményéről MITO export.";

    public function handle()
    {
		$timeStart = microtime(true);
		$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
		$returnDatas = $newcarSellings->mitoEmail(5);	
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
