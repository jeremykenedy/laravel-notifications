<?php

use Illuminate\Support\Facades\Schema;

it('adds the archived_at column to the notifications table', function () {
    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();
});

it('creates the notifications table when the application has not created one yet', function () {
    // Laravel generates its notifications migration on demand, so on a fresh
    // install it usually sorts after this one and has not run yet. Skipping
    // would mark this migration complete and lose the column for good.
    Schema::drop('notifications');

    $this->packageMigration()->up();

    expect(Schema::hasTable('notifications'))->toBeTrue()
        ->and(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue()
        ->and(Schema::hasColumn('notifications', 'read_at'))->toBeTrue()
        ->and(Schema::hasColumn('notifications', 'notifiable_id'))->toBeTrue();
});

it('leaves a notification usable after creating the table itself', function () {
    Schema::drop('notifications');
    $this->packageMigration()->up();

    $user = $this->makeUser();
    $this->notify($user, 'Fresh install');

    expect($this->service()->unreadCount($user))->toBe(1);
});

it('can be run twice without failing', function () {
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
