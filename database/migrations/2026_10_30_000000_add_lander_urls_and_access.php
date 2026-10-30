<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lander_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('lander_access')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('lander_access');
        });
        Schema::dropIfExists('lander_urls');
    }
};
