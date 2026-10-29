<?php

/** Verify that a fresh install grants every permission to God users. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
]);
DB::purge('sqlite');

Schema::create('permissions', function ($table) {
    $table->increments('id');
    $table->unsignedInteger('aff_id');
    $table->boolean('create_offers')->default(false);
    $table->boolean('view_all_users')->default(false);
    $table->boolean('view_payouts')->default(false);
    $table->boolean('view_adv_reports')->default(false);
    $table->boolean('view_sms_stats')->default(false);
    $table->boolean('create_announcements')->default(false);
    $table->boolean('later_permission')->default(false);
});
Schema::create('privileges', function ($table) {
    $table->increments('idprivileges');
    $table->unsignedInteger('rep_idrep');
    $table->boolean('is_god')->default(false);
});

DB::table('permissions')->insert([
    ['aff_id' => 1, 'create_offers' => 1],
    ['aff_id' => 3, 'create_offers' => 1],
]);
DB::table('privileges')->insert([
    ['rep_idrep' => 1, 'is_god' => 1],
    ['rep_idrep' => 2, 'is_god' => 1],
    ['rep_idrep' => 3, 'is_god' => 0],
]);

$migration = require __DIR__.'/../database/migrations/2026_10_29_000001_grant_all_permissions_to_god_users.php';
$migration->up();
$migration->up(); // Running the migration again must not create duplicate rows.

$columns = array_diff(Schema::getColumnListing('permissions'), ['id', 'aff_id']);
foreach ([1, 2] as $repId) {
    $rows = DB::table('permissions')->where('aff_id', $repId)->get();
    if ($rows->count() !== 1) {
        throw new RuntimeException("Expected one permissions row for God user {$repId}");
    }
    foreach ($columns as $column) {
        if ((int) $rows[0]->{$column} !== 1) {
            throw new RuntimeException("God user {$repId} lacks {$column}");
        }
    }
}

$other = DB::table('permissions')->where('aff_id', 3)->first();
if ((int) $other->create_offers !== 1 || (int) $other->view_all_users !== 0) {
    throw new RuntimeException('Non-God permissions were modified');
}

echo "God permissions migration verified.\n";
