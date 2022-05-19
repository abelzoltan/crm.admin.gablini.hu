<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsMonthlyAdminUsersStats extends Command
{
    protected $signature = "crm:serviceEventsMonthlyAdminUsersStats";
    protected $description = "Szerviz események: havi statisztika a munkatársak teljesítményéről";

    public function handle()
    {
		$timeStart = microtime(true);	
		
		$services = new \App\Http\Controllers\ServiceController;
		$filePath = $services->serviceEventsAdminUsersStatsCSV();	
		$services->serviceEventsAdminUsersStatsEmail($filePath);	
		
		$timeEnd = microtime(true);
		$executionTimeSec = $timeEnd - $timeStart;
		
		$return = [
			"filePath" => $filePath,
			"timeStart" => $timeStart,
			"timeEnd" => $timeEnd,
			"executionTimeSec" => $executionTimeSec,
		];
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
