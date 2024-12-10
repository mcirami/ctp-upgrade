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
        Schema::table('click_vars', function (Blueprint $table) {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('click_vars');

            if (! array_key_exists('click_vars_sub1_index', $indexesFound)) {
                $table->index('sub1', 'click_vars_sub1_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('click_vars', function (Blueprint $table) {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('click_vars');

            if (array_key_exists('click_vars_sub1_index', $indexesFound)) {
                $table->dropIndex('sub1', 'click_vars_sub1_index');
            }
        });
    }
};
