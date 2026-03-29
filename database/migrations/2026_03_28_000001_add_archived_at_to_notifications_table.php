<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('notifications', 'archived_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('read_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notifications', 'archived_at')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
