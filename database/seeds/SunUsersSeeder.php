<?php
namespace Database\Seeders;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SunUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(3, 4) as $index) {
            $userName = sprintf('SUN%02d', $index);

            if (User::where('user_name', $userName)->exists()) {
                continue;
            }

            $user = new User();
            $user->user_name = $userName;
	        $user->first_name = $userName;
			$user->last_name = $userName;
            $user->email = strtolower($userName) . '@' . strtolower($userName) . '.com';
            $user->password = Hash::make($userName . '!!!');
            $user->status = 1;
            $user->referrer_repid = 1012;
            $user->rep_timestamp = Carbon::now();
            $user->company_name = '';
            $user->save();
        }
    }
}
