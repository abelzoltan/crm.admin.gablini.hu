<?php
namespace App\Console\Commands\CRM\ServiceEvents;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class EventsWeeklyAdminNoAnswer extends Command
{
    protected $signature = "crm:serviceEventsWeeklyAdminNoAnswer";
    protected $description = "Heti statisztika azokról az ügyfelekről, akik nem szeretnének nyilatkozni VAGY írtak megjegyzést.";

    public function handle()
    {
		$timeStart = microtime(true);
			
		$services = new \App\Http\Controllers\ServiceController;
		$filePath = $services->serviceEventsAdminNoAnswerEmail();	
		
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
