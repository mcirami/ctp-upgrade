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
            $table->string('status')->default('pending')->after('end_of_week');
			$table->date('start_of_week')->change();
			$table->date('end_of_week')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_logs', function (Blueprint $table) {
	        $table->dropColumn('status');
	        $table->timestamp('start_of_week')->change();
	        $table->timestamp('end_of_week')->change();
        });
    }
};
