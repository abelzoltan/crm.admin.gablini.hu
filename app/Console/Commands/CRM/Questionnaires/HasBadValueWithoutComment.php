<?php
namespace App\Console\Commands\CRM\Questionnaires;

use App\Http\Controllers\CronController;
use Illuminate\Console\Command;

class HasBadValueWithoutComment extends Command {

    protected $signature = "crm:hasBadValueWithoutComments";
    protected $description = "Kérdőívek - Alacsony értékelés megjegyzés nélkül";

    public function handle()
    {
        $questionnaires = new \App\Http\Controllers\QuestionnaireController;
        $return = $questionnaires->processHasBadValuesWithoutComments();

        $cron = new CronController();
        $cron->log($this->signature, $return);
    }

}
