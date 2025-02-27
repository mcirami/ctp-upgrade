<?php

namespace App\Console\Commands;

use App\PayoutLog;
use App\User;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyPayoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sum up each user’s paid conversions for the previous week and store in payout_logs.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
	    // 1. Determine the previous week's Monday 00:00 through Sunday 23:59 in EST
	    //    Because we run this Monday at 5am, "now" is the new Monday (5am).
	    //    We want last week’s Monday 00:00 to Sunday 23:59:59.
	    $now = Carbon::now('America/New_York');
	    $startEst = $now->copy()->subWeek()->startOfWeek( CarbonInterface::MONDAY)->setTime(0,0,0);
	    $endEst   = $startEst->copy()->endOfWeek(CarbonInterface::SUNDAY)->setTime(23,59,59);
	    $startUtc = $startEst->clone()->setTimezone('UTC');
	    $endUtc   = $endEst->clone()->setTimezone('UTC');

	    $this->info("Calculating weekly payouts for period:");
	    $this->info("Start: {$startEst} | End: {$endEst}");

	    $users = User::where('status', '=', 1)
	               ->join('conversions', 'conversions.user_id', '=', 'rep.idrep')
	               ->whereBetween('conversions.timestamp', [$startUtc, $endUtc])
	               ->leftJoin('payout_data', 'payout_data.rep_idrep', '=', 'rep.idrep')
	               ->select('rep.idrep', 'payout_data.payout_type', 'payout_data.payout_id', 'payout_data.country', DB::raw('SUM(conversions.paid) as revenue'))
	               ->groupBy('rep.idrep')
	               ->get();

		foreach($users as $user) {
			$userLog = $user->payoutLog()->where('status', 'pending')->where('revenue', '>', 0)->get();
			$totalRevenue = $user->revenue;
			if ($userLog) {
				foreach ($userLog as $log) {
					$totalRevenue += $log->revenue;
					$log->update(['status' => 'rollover']);
				}
			}

			$user->payoutLog()->create([
				'payout_type'   => $user->payout_type,
				'payout_id'     => $user->payout_id,
				'country'       => $user->country,
				'revenue'       => $totalRevenue,
				'start_of_week' => $startEst->format('Y-m-d'),
				'end_of_week'   => $endEst->format('Y-m-d'),
			]);

			$this->info("User {$user->idrep} => revenue: {$totalRevenue}");
		}

	    $this->info('Weekly payout logs created successfully!');
	    return 0;
    }
}
