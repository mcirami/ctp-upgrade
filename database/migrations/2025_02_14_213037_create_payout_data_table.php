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
        Schema::create('payout_data', function (Blueprint $table) {
            $table->id();
	        $table->unsignedInteger('rep_idrep');
	        $table->foreign('rep_idrep')->references('idrep')->on('rep');
	        $table->string('payout_type');
	        $table->string('payout_id');
	        $table->string('country');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_data');
    }
};
