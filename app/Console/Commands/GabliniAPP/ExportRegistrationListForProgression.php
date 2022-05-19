<?php
namespace App\Console\Commands\GabliniAPP;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class ExportRegistrationListForProgression extends Command
{
    protected $signature = "gabliniAPP:ExportRegistrationListForProgression";
    protected $description = "APP: regisztrációs lista exportálása CSV-be a Progression számára";

    public function handle()
    {
		$timeStart = microtime(true);
		$app = new \App\Http\Controllers\AppController;
		$returnDatas = $app->saveProgressionCSVOnFTP();	
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
