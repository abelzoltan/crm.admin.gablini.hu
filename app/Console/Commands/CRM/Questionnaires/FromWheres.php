<?php
namespace App\Console\Commands\CRM\Questionnaires;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class FromWheres extends Command
{
    protected $signature = "crm:questionnaireFromWheres";
    protected $description = "Kérdőívek - Honnan érkezett válaszok.";

    public function handle()
    {
		$questionnaires = new \App\Http\Controllers\QuestionnaireController;		
		$return = $questionnaires->exportFromWheresLastWeek();
		
		$cron = new CronController();
		$cron->log($this->signature, $return);
    }
}
