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
        Schema::table('rep_has_offer', function (Blueprint $table) {
	        $table->decimal('payout', 8, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rep_has_offer', function (Blueprint $table) {
	        $table->double('payout')->change();
        });
    }
};
