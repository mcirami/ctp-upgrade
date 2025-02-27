<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
	        $table->string('country')->nullable()->after('referrals');
	        $table->string('payout_id')->nullable()->after('referrals');
            $table->string('payout_type')->nullable()->after('referrals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
            $table->dropColumn('country');
			$table->dropColumn('payout_id');
			$table->dropColumn('payout_type');
        });
    }
};
