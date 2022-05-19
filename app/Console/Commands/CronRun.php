<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class CronRun extends Command
{
    protected $signature = "cronRun";
    protected $description = "Cron futtatás";

    public function handle()
    {
		$cron = new CronController();
		$cron->log($this->signature, "crm");
    }
}
