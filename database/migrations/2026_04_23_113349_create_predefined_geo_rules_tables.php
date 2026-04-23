<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predefined_geo_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rep_idrep')->index();
            $table->string('name');
            $table->unsignedBigInteger('redirect_offer')->nullable();
            $table->boolean('deny')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('predefined_geo_rule_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('predefined_geo_rule_id')->index();
            $table->string('country_code', 8)->index();
            $table->string('country_name')->nullable();
            $table->boolean('cap_status')->default(false);
            $table->integer('cap')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predefined_geo_rule_countries');
        Schema::dropIfExists('predefined_geo_rules');
    }
};
