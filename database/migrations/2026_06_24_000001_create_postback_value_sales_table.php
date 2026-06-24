<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postback_value_sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('source_click_id');
            $table->unsignedInteger('target_offer_id');
            $table->unsignedInteger('generated_click_id')->nullable();
            $table->decimal('postback_value', 12, 2);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['source_click_id', 'target_offer_id'],
                'postback_value_sales_source_offer_unique'
            );
            $table->index('generated_click_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postback_value_sales');
    }
};
