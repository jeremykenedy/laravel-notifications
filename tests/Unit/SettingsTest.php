<?php

use Illuminate\Support\Facades\Schema;
use Jeremykenedy\LaravelNotifications\Support\Colors;
use Jeremykenedy\LaravelNotifications\Support\Settings;

function settings(): Settings
{
    return app(Settings::class);
}

it('reports the settings table as available once migrated', function () {
    expect(settings()->available())->toBeTrue();
});

it('reports the table as unavailable before it is migrated', function () {
    Schema::drop(config('notifications.settings.table'));

    expect(settings()->available())->toBeFalse();
});

it('stores colours and overlays them onto the config', function () {
    settings()->save(['info' => '#001122', 'success' => '#334455']);

    expect(config('notifications.colors.info'))->toBe('#001122')
        ->and(config('notifications.colors.success'))->toBe('#334455')
        ->and(settings()->stored())->toBe(['info' => '#001122', 'success' => '#334455']);
});

it('lowercases a stored colour', function () {
    settings()->save(['info' => '#AABBCC']);

    expect(settings()->stored()['info'])->toBe('#aabbcc');
});

it('refuses to store a colour that is not a six digit hex', function () {
    settings()->save(['info' => 'rebeccapurple', 'success' => '#1234', 'warning' => '#abcdef']);

    expect(settings()->stored())->toBe(['warning' => '#abcdef']);
});

it('ignores a key that is not a configurable colour', function () {
    settings()->save(['info' => '#001122', 'sidebar' => '#ffffff']);

    expect(settings()->stored())->not->toHaveKey('sidebar');
});

it('keeps one row no matter how many times it saves', function () {
    settings()->save(['info' => '#001122']);
    settings()->save(['info' => '#223344']);

    expect(settings()->model()->newQuery()->count())->toBe(1)
        ->and(settings()->stored()['info'])->toBe('#223344');
});

it('loads stored colours over the shipped defaults', function () {
    settings()->save(['danger' => '#000111']);
    config(['notifications.colors.danger' => '#dc2626']);

    settings()->load();

    expect(config('notifications.colors.danger'))->toBe('#000111');
});

it('leaves the config alone when the settings feature is turned off', function () {
    settings()->save(['danger' => '#000111']);
    config(['notifications.colors.danger' => '#dc2626', 'notifications.settings.enabled' => false]);

    settings()->load();

    expect(config('notifications.colors.danger'))->toBe('#dc2626');
});

it('loads without error before the table exists', function () {
    Schema::drop(config('notifications.settings.table'));

    settings()->load();

    expect(config('notifications.colors.info'))->toBe('#2563eb');
});

it('snapshots the shipped defaults so a reset has something to restore', function () {
    $defaults = settings()->defaults();

    expect($defaults)->toHaveCount(8)
        ->and($defaults['info'])->toBe('#2563eb');
});

it('restores every default and drops the stored row on reset', function () {
    settings()->save(['info' => '#000111', 'danger' => '#000222']);

    settings()->reset();

    expect(settings()->model()->newQuery()->count())->toBe(0)
        ->and(config('notifications.colors.info'))->toBe('#2563eb')
        ->and(config('notifications.colors.danger'))->toBe('#dc2626');
});

it('returns no stored colours when nothing has been saved', function () {
    expect(settings()->stored())->toBe([]);
});

it('keeps every colour key resolvable after a partial save', function () {
    settings()->save(['info' => '#000111']);

    foreach (Colors::keys() as $key) {
        expect(Colors::isValid(Colors::get($key)))->toBeTrue();
    }
});
