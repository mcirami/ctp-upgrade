<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTableAndPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('author_id')->index();
                $table->string('title', 150);
                $table->string('type', 30);
                $table->text('body');
                $table->string('attachment_disk', 30)->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_mime', 150)->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->boolean('is_pinned')->default(false)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('permissions', 'create_announcements')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->tinyInteger('create_announcements')->default(0);
            });
        }

        // Network Admins retain full access; regular Admins must be granted this permission.
        $godIds = DB::table('privileges')->where('is_god', 1)->pluck('rep_idrep');
        if ($godIds->isNotEmpty()) {
            DB::table('permissions')->whereIn('aff_id', $godIds)->update(['create_announcements' => 1]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
        if (Schema::hasColumn('permissions', 'create_announcements')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('create_announcements');
            });
        }
    }
}
