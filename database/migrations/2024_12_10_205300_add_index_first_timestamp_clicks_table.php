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
        Schema::table('clicks', function (Blueprint $table) {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('clicks');

            if (! array_key_exists('clicks_first_timestamp_index', $indexesFound)) {
                $table->index('first_timestamp', 'clicks_first_timestamp_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('clicks');

            if (array_key_exists('clicks_first_timestamp_index', $indexesFound)) {
                $table->dropIndex('first_timestamp', 'clicks_first_timestamp_index');
            }

        });
    }
};
