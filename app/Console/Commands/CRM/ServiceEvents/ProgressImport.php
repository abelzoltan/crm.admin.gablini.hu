<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class ProgressImport extends Command
{
    protected $signature = "crm:serviceProgressImport";
    protected $description = "Progress export adatok átimportálása";

    public function handle()
    {
		$timeStart = microtime(true);
		$services = new \App\Http\Controllers\ServiceController;
		$importDatas = $services->progressFilesImport();	
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
