<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The base install predates several permission columns. Grant every column
        // present after the earlier schema migrations have run.
        $permissionColumns = array_diff(Schema::getColumnListing('permissions'), ['id', 'aff_id']);
        $allPermissions = array_fill_keys($permissionColumns, 1);

        foreach (DB::table('privileges')->where('is_god', 1)->pluck('rep_idrep') as $repId) {
            DB::table('permissions')->updateOrInsert(['aff_id' => $repId], $allPermissions);
        }
    }

    public function down(): void
    {
        // Granted permissions should not be revoked by rolling back this migration.
    }
};
