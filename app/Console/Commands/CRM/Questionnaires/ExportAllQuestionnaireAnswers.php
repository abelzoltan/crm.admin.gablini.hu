<?php
namespace App\Console\Commands\CRM\Questionnaires;

use Illuminate\Console\Command;
use App\Http\Controllers\CronController;

class ExportAllQuestionnaireAnswers extends Command
{
    protected $signature = "crm:exportAllQuestionnaireAnswers";
    protected $description = "Kérdőívek - Összes kérdőív válasz exportálása Excel fájlba, amit az FTP-re mentünk.";

    public function handle()
    {
		$questionnaires = new \App\Http\Controllers\QuestionnaireController;
		
		// $return = $questionnaires->exportAnswersIntoExcel_CreateFile([2]);
		// $return = $questionnaires->exportAnswersIntoExcel_CreateWorksheet("/var/www/vhosts/infinitimagyarorszag.hu/crm.admin.gablini.hu/_questionnaire_answers_exports/20211011_Kerdoiv_export_61642007ae4f9.xlsx", $questionnaires->model->getQuestionnaire(2), "12000, 3000");
		
		$return = $questionnaires->exportAllQuestionnaireAnswersIntoExcelToFTP();

		
		
		print_r($return);
		
		// $cron = new CronController();
		// $cron->log($this->signature, $return);
    }
}
