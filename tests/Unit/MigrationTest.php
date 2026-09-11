<?php

use Illuminate\Support\Facades\Schema;

it('adds the archived_at column to the notifications table', function () {
    expect(Schema::hasColumn('notifications', 'archived_at'))->toBeTrue();
});

it('does nothing when the notifications table has not been created yet', function () {
    Schema::drop('notifications');

    $this->packageMigration()->up();

    expect(Schema::hasTable('notifications'))->toBeFalse();
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
