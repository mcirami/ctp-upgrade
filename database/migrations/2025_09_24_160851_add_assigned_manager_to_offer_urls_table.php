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
	    Schema::connection('master')->table('offer_urls', function (Blueprint $table) {
		    $table->unsignedInteger('assigned_manager_id')->nullable()->after('company_id');
		    $table->foreign('assigned_manager_id')->references('idrep')->on('rep')->onDelete('set null');
	    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
	    Schema::connection('master')->table('offer_urls', function (Blueprint $table) {
		    $table->dropForeign(['assigned_manager_id']);
		    $table->dropColumn('assigned_manager_id');
	    });
    }
};
