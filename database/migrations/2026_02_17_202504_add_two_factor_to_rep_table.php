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
        Schema::table('rep', function (Blueprint $table) {
	        $table->text('two_factor_secret')->after('referrer_repid')->nullable();
	        $table->boolean('two_factor_enabled')->after('two_factor_secret')->default(false);
	        $table->json('two_factor_recovery_codes')->after('two_factor_enabled')->nullable();
	        $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rep', function (Blueprint $table) {
	        $table->dropColumn([
		        'two_factor_secret',
		        'two_factor_enabled',
		        'two_factor_recovery_codes',
		        'two_factor_confirmed_at',
	        ]);
        });
    }
};
