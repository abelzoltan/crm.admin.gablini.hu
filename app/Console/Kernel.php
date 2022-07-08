<?php
namespace App\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
		Commands\CronRun::class,
		
		Commands\GabliniAPP\ExportRegistrationListForProgression::class,
		
		// Commands\CRM\CustomerPhoneChanges::class,
		
		Commands\CRM\ServiceEvents\ProgressImport::class,
		Commands\CRM\ServiceEvents\EventReminder::class,
		Commands\CRM\ServiceEvents\EventsDailyReport::class,
		Commands\CRM\ServiceEvents\EventsMonthlyReport::class,
		Commands\CRM\ServiceEvents\EventsMitoExport::class,
		Commands\CRM\ServiceEvents\EventsWeeklyAdminNoAnswer::class,
		Commands\CRM\ServiceEvents\EventsWeeklyStats::class,
		Commands\CRM\ServiceEvents\EventsMonthlyStats::class,
		Commands\CRM\ServiceEvents\EventsMonthlyAdminUsersStats::class,
		Commands\CRM\ServiceEvents\EventsQuarterlyStats::class,
		Commands\CRM\ServiceEvents\EventsFillingStats::class,
		
		Commands\CRM\NewCarSellings\ProgressImport::class,
		Commands\CRM\NewCarSellings\EventReminder::class,
		Commands\CRM\NewCarSellings\EventsDailyReport::class,
		Commands\CRM\NewCarSellings\EventsMonthlyReport::class,
		Commands\CRM\NewCarSellings\EventsMitoExport::class,
		Commands\CRM\NewCarSellings\EventsWeeklyStats::class,
		Commands\CRM\NewCarSellings\EventsMonthlyStats::class,
		Commands\CRM\NewCarSellings\EventsFillingStats::class,
		
		Commands\CRM\Questionnaires\ExportAllQuestionnaireAnswers::class,
		Commands\CRM\Questionnaires\FromWheres::class,

        Commands\CRM\Questionnaires\TestCommand::class,
	];

    protected function schedule(Schedule $schedule)
    {
		# https://laravel.com/docs/5.4/scheduling#schedule-frequency-options
		// $schedule->command("cronRun")->dailyAt("00:00");
		$schedule->command("cronRun")->hourly();
		
		#APP
		$schedule->command("gabliniAPP:ExportRegistrationListForProgression")->dailyAt("01:00");
		
		#CRM
		// $schedule->command("crm:customerPhoneChanges")->monthlyOn(1, "06:00");
		
		#CRM - Service events
		$schedule->command("crm:serviceProgressImport")->dailyAt("01:00");
		$schedule->command("crm:serviceEventReminder")->dailyAt("06:00");
		$schedule->command("crm:serviceEventsDailyReport")->dailyAt("19:00");
		$schedule->command("crm:serviceEventsMonthlyReport")->monthlyOn(1, "06:00");
		$schedule->command("crm:serviceEventsWeeklyAdminNoAnswer")->mondays()->at("06:00");
		$schedule->command("crm:serviceEventsWeeklyStats")->mondays()->at("06:00");
		$schedule->command("crm:serviceEventsMonthlyStats")->monthlyOn(1, "06:00");
		$schedule->command("crm:serviceEventsMonthlyAdminUsersStats")->monthlyOn(1, "06:00");
		$schedule->command("crm:serviceEventsFillingStats")->monthlyOn(1, "06:00");
		
		$schedule->command("crm:serviceEventsQuarterlyStats")->monthlyOn(1, "06:30")->when(function(){
			switch(date("n"))
			{
				case 1:
				case 4:
				case 7:
				case 10:
					return true;
					break;
				default:
					return false;
			}
		});
		
		$schedule->command("crm:newcarSellingProgressImport")->dailyAt("22:00");
		$schedule->command("crm:newcarSellingEventReminder")->dailyAt("06:00");
		$schedule->command("crm:newcarSellingEventsDailyReport")->dailyAt("19:00");
		$schedule->command("crm:newcarSellingEventsMonthlyReport")->monthlyOn(1, "06:00");
		$schedule->command("crm:newcarSellingEventsMitoExport")->dailyAt("05:00")->when(function(){
			$cron = \App\Http\Controllers\CronController;
			$lastRun = $cron->getLastCronByType("crm:newcarSellingEventsMitoExport");
			if($lastRun === false) { return true; }
			else
			{
				$dateDiff = round((time() - strtotime($lastRun->date)) / (60 * 60 * 24));
				return ($dateDiff >= 5);
			}
		});
		$schedule->command("crm:newcarSellingEventsWeeklyStats")->mondays()->at("06:00");
		$schedule->command("crm:newcarSellingEventsMonthlyStats")->monthlyOn(1, "06:00");
		$schedule->command("crm:newcarSellingEventsFillingStats")->monthlyOn(1, "06:00");
		
		#Questionnaires
		$schedule->command("crm:questionnaireFromWheres")->mondays()->at("07:00");
    }

    protected function commands()
    {
		require base_path("routes/console.php");
    }
}
