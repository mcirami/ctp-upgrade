<?php
namespace Database\Seeders;

use App\Privilege;
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
        foreach (range(1, 5) as $index) {
            $userName = sprintf('SUN%02d', $index);

            $user = User::where('user_name', $userName)->first();


			if(!$user) {
				$user                 = new User();
				$user->user_name      = $userName;
				$user->first_name     = $userName;
				$user->last_name      = $userName;
				$user->email          = strtolower( $userName ) . '@' . strtolower( $userName ) . '.com';
				$user->password       = Hash::make( $userName . '!!!' );
				$user->status         = 1;
				$user->referrer_repid = 1012;
				$user->rep_timestamp  = Carbon::now();
				$user->company_name   = '';
				$user->save();
			}

	        Privilege::updateOrCreate(
		        ['rep_idrep' => $user->idrep],
		        [
			        'is_god' => 0,
			        'is_manager' => 0,
			        'is_admin' => 0,
			        'is_rep' => 1,
		        ]
	        );
        }
    }
}
