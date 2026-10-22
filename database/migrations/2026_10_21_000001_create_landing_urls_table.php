<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingUrlsTable extends Migration
{
    public function up()
    {
        Schema::connection('master')->create('landing_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->boolean('status');
            $table->unsignedInteger('company_id')->index();
            $table->dateTime('timestamp');
        });
    }

    public function down()
    {
        Schema::connection('master')->dropIfExists('landing_urls');
    }
}
