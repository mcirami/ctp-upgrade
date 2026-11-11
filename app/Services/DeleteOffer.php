<?php

namespace App\Services;

use App\Offer;
use Illuminate\Support\Facades\DB;

class DeleteOffer
{
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $offer = Offer::whereKey($id)->lockForUpdate()->first();
            if (!$offer) {
                return; // Safe when two management tabs delete the same offer.
            }

            // Delete targeting children before their parents; retain unrelated rules.
            $rules = DB::table('rule')->where('offer_idoffer', $id)->pluck('idrule');
            $geoRules = DB::table('geo_rule')->whereIn('rule_idrule', $rules)->pluck('idgeo_rule');
            $deviceRules = DB::table('device_rule')->whereIn('rule_idrule', $rules)->pluck('iddevice_rule');
            DB::table('country_list')->whereIn('geo_rule_idgeo_rule', $geoRules)->delete();
            DB::table('device_list')->whereIn('device_rule_iddevice_rule', $deviceRules)->delete();
            DB::table('geo_rule')->whereIn('rule_idrule', $rules)->delete();
            DB::table('device_rule')->whereIn('rule_idrule', $rules)->delete();
            DB::table('rule')->whereIn('idrule', $rules)->delete();

            foreach (['rep_has_offer', 'user_offer_caps', 'offer_caps'] as $table) {
                DB::table($table)->where('offer_idoffer', $id)->delete();
            }
            DB::table('bonus_offers')->where('offer_id', $id)->delete();

            // Other offers survive. Keep their restrictions, but stop redirecting to a dead offer.
            DB::table('offer')->where('parent', $id)->update(['parent' => null]);
            DB::table('offer_caps')->where('redirect_offer', $id)->update(['redirect_offer' => null]);
            DB::table('rule')->where('redirect_offer', $id)->update(['redirect_offer' => 0, 'deny' => 1]);
            DB::table('predefined_geo_rules')->where('redirect_offer', $id)->update(['redirect_offer' => null, 'deny' => 1]);

            // Clicks, conversions, payouts, adjustments, aggregates and postback receipts are history.
            DB::table('deleted_offers')->insert([
                'idoffer' => $id,
                'offer_name' => 'Deleted offer #'.$id,
                'campaign_id' => $offer->campaign_id,
                'deleted_at' => now(),
            ]);
            $offer->delete();
        });
    }
}
