<?php

use Illuminate\Notifications\Console\NotificationTableCommand;
use Illuminate\Support\Facades\Schema;

it('adds the archived_at column to the notifications table', function () {
    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();
});

it('never creates the notifications table itself', function () {
    // Laravel's own create_notifications_table does Schema::create without a
    // guard, so a package that creates the table first makes that migration fail.
    Schema::drop('notifications');

    $this->packageMigration()->up();

    expect(Schema::hasTable('notifications'))->toBeFalse();
});

it('adds the column once the application has created the table', function () {
    Schema::drop('notifications');
    $this->packageMigration()->up();

    $this->createNotificationsTable();
    $this->packageMigration()->up();

    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();
});

it('sorts after any migration an application generates for the notifications table', function () {
    $generated = '2030_12_31_235959_create_notifications_table.php';

    $names = collect(glob(__DIR__.'/../../database/migrations/*.php'))
        ->map(fn ($path) => basename($path))
        ->filter(fn ($name) => str_contains($name, 'archived_at'))
        ->sort()
        ->values();

    expect($names->last())->toBeGreaterThan($generated);
});

it('lets laravel create the notifications table before the package adds its column', function () {
    Schema::drop('notifications');

    $stub = dirname((new ReflectionClass(NotificationTableCommand::class))->getFileName())
        .'/stubs/notifications.stub';
    expect(file_exists($stub))->toBeTrue();

    $applicationMigrations = sys_get_temp_dir().'/notifications-app-'.uniqid();
    mkdir($applicationMigrations);
    copy($stub, $applicationMigrations.'/2026_10_09_100000_create_notifications_table.php');

    // The same ordering php artisan migrate uses across every migration path.
    $files = app('migrator')->getMigrationFiles([__DIR__.'/../../database/migrations', $applicationMigrations]);

    foreach ($files as $path) {
        (require $path)->up();
    }

    expect(Schema::hasTable('notifications'))->toBeTrue()
        ->and(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();

    array_map('unlink', glob($applicationMigrations.'/*'));
    rmdir($applicationMigrations);
});

it('does nothing on a table that already has the column', function () {
    $this->packageMigration()->up();
    $this->packageMigration()->up();

    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();
});

it('removes the column on rollback and tolerates a missing table', function () {
    $this->packageMigration()->down();
    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeFalse();

    Schema::drop('notifications');
    $this->packageMigration()->down();

    expect(Schema::hasTable('notifications'))->toBeFalse();
});

it('does not drop a table it did not create on rollback', function () {
    $this->packageMigration()->down();

    expect(Schema::hasTable('notifications'))->toBeTrue();
});
