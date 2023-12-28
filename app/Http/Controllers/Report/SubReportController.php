<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\SubVarRepository;
use LeadMax\TrackYourStats\Report\Filters;

class SubReportController extends ReportController
{


    public function show()
    {
        $dates = self::getDates();

        $repo = new SubVarRepository(\DB::getPdo());

        $repo->setSubNumber(request()->query('sub', 1));


        $reporter = new Reporter($repo);

        $reporter
            ->addFilter(new Filters\Total(['clicks','unique','conversions','revenue']))
            ->addFilter(new Filters\EarningPerClick('unique', 'revenue'))
            ->addFilter(new Filters\DollarSign(['EPC', 'revenue', 'TOTAL', 'Total']));

        return view('report.sub', compact('reporter', 'dates'));
    }

}
