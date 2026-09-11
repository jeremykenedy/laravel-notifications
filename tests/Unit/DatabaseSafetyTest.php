<?php

it('runs against an in memory sqlite database only', function () {
    $connection = config('database.default');

    expect(config("database.connections.{$connection}.driver"))->toBe('sqlite')
        ->and(config("database.connections.{$connection}.database"))->toBe(':memory:');
});

it('has no server database connection configured', function () {
    foreach (['mysql', 'mariadb', 'pgsql', 'sqlsrv'] as $driver) {
        expect(config("database.connections.{$driver}"))->toBeNull();
    }
});
