<?php
namespace App\Console\Commands\CRM;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class CustomerPhoneChanges extends Command
{
    protected $signature = "crm:customerPhoneChanges";
    protected $description = "Ügyfelek - Módosított telefonszámok listája";

    public function handle()
    {
		$customers = new \App\Http\Controllers\CustomerController;
		$customers->customerPhoneChangesMonthlyReport();	
		
		$cron = new CronController();
		$cron->log($this->signature, "OK");
    }
}
