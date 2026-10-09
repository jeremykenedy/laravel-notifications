<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * The file name sorts after any migration an application generates, because
     * Laravel's own create_notifications_table is timestamped when the application
     * runs make:notifications-table. Running first would either find no table to
     * alter, or create one and make Laravel's own migration fail.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'archived_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('read_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'archived_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
