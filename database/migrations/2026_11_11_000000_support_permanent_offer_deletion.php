<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep only the identity needed to label history and retain advertiser totals.
        Schema::create('deleted_offers', function (Blueprint $table) {
            $table->unsignedInteger('idoffer')->primary();
            $table->string('offer_name', 155);
            $table->integer('campaign_id');
            $table->timestamp('deleted_at');
        });

        DB::statement('CREATE VIEW report_offers AS
            SELECT idoffer, offer_name, campaign_id, 0 AS is_deleted FROM offer
            UNION ALL
            SELECT idoffer, offer_name, campaign_id, 1 AS is_deleted FROM deleted_offers');

        // Historical clicks intentionally retain their original offer ID after deletion.
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropForeign('fk_clicks_offer1');
        });
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->unsignedInteger('redirect_offer')->nullable()->change();
        });

        // Legacy SQL can explicitly choose IDs. Never attach old history to a new offer.
        foreach (['INSERT', 'UPDATE'] as $event) {
            DB::unprepared("CREATE TRIGGER offer_reserved_id_".strtolower($event)."
                BEFORE {$event} ON offer FOR EACH ROW
                BEGIN
                    IF EXISTS (SELECT 1 FROM deleted_offers WHERE idoffer = NEW.idoffer) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A deleted offer ID cannot be reused';
                    END IF;
                END");
        }
    }

    public function down(): void
    {
        // Reinstating the FK would require destroying history or resurrecting offers.
        if (DB::table('deleted_offers')->exists()) {
            throw new RuntimeException('Cannot roll back permanent offer deletion after offers have been deleted. Retain the reporting history.');
        }

        DB::unprepared('DROP TRIGGER IF EXISTS offer_reserved_id_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS offer_reserved_id_update');
        Schema::table('clicks', function (Blueprint $table) {
            $table->foreign('offer_idoffer', 'fk_clicks_offer1')->references('idoffer')->on('offer');
        });
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->dropForeign('offer_caps_ibfk_2');
        });
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->unsignedInteger('redirect_offer')->nullable(false)->change();
        });
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->foreign('redirect_offer', 'offer_caps_ibfk_2')->references('idoffer')->on('offer');
        });
        DB::statement('DROP VIEW report_offers');
        Schema::drop('deleted_offers');
    }
};
