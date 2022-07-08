<?php
namespace App\Console\Commands\CRM\Questionnaires;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = "crm:test";

    public function handle()
    {
        $questionnaires = new \App\Http\Controllers\QuestionnaireController;
        $return = $questionnaires->getAnswer(32797);

//            print "<pre>";
//            print_r($return);
//            print "</pre>";
//            die;
    }
}
