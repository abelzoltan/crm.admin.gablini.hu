<?php
namespace App\Console\Commands\CRM\NewCarSellings;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class ProgressImport extends Command
{
    protected $signature = "crm:newcarSellingProgressImport";
    protected $description = "Progress export adatok átimportálása";

    public function handle()
    {
		$timeStart = microtime(true);
		$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
		$importDatas = $newcarSellings->eventsFilesImport();	
		$timeEnd = microtime(true);
		$executionTimeSec = $timeEnd - $timeStart;
		
		$return = [
			"importDatas" => $importDatas,
			"timeStart" => $timeStart,
			"timeEnd" => $timeEnd,
			"executionTimeSec" => $executionTimeSec,
		];
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
