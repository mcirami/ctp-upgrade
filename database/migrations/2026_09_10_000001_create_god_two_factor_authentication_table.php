<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('god_two_factor_authentication', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rep_id')->unique();
            $table->text('encrypted_secret');
            $table->text('recovery_codes')->nullable();
            $table->unsignedBigInteger('last_used_timestep')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('god_two_factor_authentication');
    }
};
