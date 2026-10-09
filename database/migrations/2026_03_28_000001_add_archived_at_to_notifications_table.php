<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Superseded by 2099_01_01_000000_add_archived_at_to_notifications_table, which
     * sorts after the application's own notifications migration. Kept so installs
     * that already ran this one still find it on disk.
     */
    public function up(): void
    {
    }

    public function down(): void
    {
    }
};
