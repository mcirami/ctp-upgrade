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

            if (! array_key_exists('clicks_rep_idrep_index', $indexesFound)) {
                $table->index('rep_idrep', 'clicks_rep_idrep_index');
            }

            if (! array_key_exists('clicks_offer_idoffer_index', $indexesFound)) {
                $table->index('offer_idoffer', 'clicks_offer_idoffer_index');
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

            if (array_key_exists('clicks_offer_idoffer_index', $indexesFound)) {
                $table->dropIndex('offer_idoffer', 'clicks_offer_idoffer_index');
            }

            if (array_key_exists('clicks_rep_idrep_index', $indexesFound)) {
                $table->dropIndex('rep_idrep', 'clicks_rep_idrep_index');
            }

        });
    }
};
