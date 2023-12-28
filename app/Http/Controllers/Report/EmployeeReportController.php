<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Report\Filters;
use LeadMax\TrackYourStats\Report\Repositories\Employee\AdminEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Employee\ManagerEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;

class EmployeeReportController extends ReportController
{


    private function report(Repository $repository, Request $request)
    {
        $repository->SHOW_AFF_TYPE = $request->query('role', 3);

        $reporter = new \LeadMax\TrackYourStats\Report\Reporter($repository);

        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total([
                'Clicks',
                'UniqueClicks',
                'FreeSignUps',
                'PendingConversions',
                'Conversions',
                'Revenue',
                'Deductions',
                'BonusRevenue',
                'ReferralRevenue',
                'TOTAL'], ['Revenue', 'Deductions', 'BonusRevenue', 'ReferralRevenue']))
            ->addFilter(new Filters\EarningPerClick())
            ->addFilter(new Filters\DollarSign([
                'EPC',
                'Revenue',
                'Deductions',
                'BonusRevenue',
                'ReferralRevenue',
                'TOTAL'
            ]))->addFilter(new Filters\UserToolTip())->addFilter(function ($data) {
                foreach ($data as $key => &$row) {
                    if (isset($row['Clicks']) && is_numeric($row['idrep'])) {
                        $queryString = http_build_query(request()->query());
                        $row['Clicks'] = "<a target='_blank' href='/user/{$row['idrep']}/clicks?{$queryString}'>{$row['Clicks']}</a>";
                    }
                }

                return $data;
            });


        return $reporter;
    }

    public function show(Request $request)
    {
        switch (Session::userType()) {
            case Privilege::ROLE_GOD:
            case Privilege::ROLE_ADMIN:
                $repository = new AdminEmployeeRepository(\DB::getPdo());
                break;
            case Privilege::ROLE_MANAGER:
                $repository = new ManagerEmployeeRepository(\DB::getPdo());
                break;
            default:
                abort(400, 'Unknown user.');
        }

        $dates = self::getDates();
        $reporter = $this->report($repository, $request);


        return view('report.employee', compact('reporter', 'dates'));
    }


}
