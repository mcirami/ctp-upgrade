<?php

use Illuminate\Database\Seeder;

class ClicksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (\App\User::withRole(3)->get() as $affiliate) {
            $offerIds = $affiliate->offers->pluck('idoffer');
            for ($i = 0; $i < 200; $i++) {
                $click = factory(\App\Click::class)->make();
                $click->offer_idoffer = $offerIds[rand(0, count($offerIds) - 1)];
                $click->rep_idrep = $affiliate->idrep;
                $click->save();

                if (rand(0, 4) == 0) {
                    $freeSignUp = new \App\FreeSignUp();
                    $freeSignUp->user_id = $affiliate->idrep;
                    $freeSignUp->click_id = $click->idclicks;
                    $freeSignUp->timestamp = \Carbon\Carbon::now();
                    $freeSignUp->save();
                }

                if (rand(0, 4) == 1) {
                    $pConversion = factory(\App\PendingConversion::class)->make();
                    $pConversion->click_id = $click->idclicks;
                    $pConversion->save();
                }

                if (rand(0, 4) == 2) {
                    $conversion = factory(\App\Conversion::class)->make();
                    $conversion->user_id = $affiliate->idrep;
                    $conversion->click_id = $click->idclicks;
                    $conversion->save();

                    if (rand(0, 2) == 0) {
                        $deduction = factory(\App\Deduction::class)->make();
                        $deduction->conversion_id = $conversion->id;
                        $deduction->save();
                    }
                }

            }
        }
    }
}
