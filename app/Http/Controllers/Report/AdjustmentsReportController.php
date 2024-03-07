<?php

namespace App\Http\Controllers\Report;


use App\Http\Controllers\Report\ReportController;
use App\Privilege;
use Carbon\Carbon;
use LeadMax\TrackYourStats\Offer\AdjustmentsLog;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\AdjustmentsLogRepository;
use LeadMax\TrackYourStats\System\Session;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AdjustmentsReportController extends ReportController
{
    public function show()
    {
        $dates = self::getDates();
        $repo = new AdjustmentsLogRepository(\DB::getPdo());
        $repo->setAction(AdjustmentsLog::ACTION_CREATE_SALE);

        if (Session::userType() == Privilege::ROLE_ADMIN) {
            $repo->showOnlyWithThisSaleLogUserId(Session::userID());
        }

        $reporter = new Reporter($repo);
        $reporter->addFilter(new DollarSign(['paid']));

	    $tz = 'America/New_York';
	    $dateToday = \Illuminate\Support\Carbon::today($tz)->add(1, 'day')->format('Y-m-d H:i:s');
	    $carbonToday = Carbon::createFromFormat('Y-m-d H:i:s', $dateToday, $tz);
	    $dateNow = $carbonToday->setTimezone("UTC");

	    $clicksLog = new Logger('clicks');
	    $clicksLog->pushHandler(new StreamHandler(storage_path('logs/clicks.log')), Logger::INFO);
	    $log = [
		    'now'           => $dateNow,
		    'offerID'       => 905
	    ];
	    $clicksLog->info('Click', $log);

	    if($dateNow > '2024-03-08 04:59:59') {
		    $db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
		    $sql  = "UPDATE offer_caps SET max_cap_status = 0, max_cap_date = NULL WHERE offer_idoffer = 905";
		    $prep = $db->prepare( $sql );
		    $prep->execute();
	    }

        return view('report.adjustments', compact('reporter','dates'));
    }

}