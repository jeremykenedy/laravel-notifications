<?php

use Jeremykenedy\LaravelNotifications\Support\Colors;

it('exposes a colour for every notification type plus the accents', function () {
    expect(Colors::TYPES)->toBe(['info', 'success', 'warning', 'danger', 'system'])
        ->and(Colors::ACCENTS)->toBe(['unread', 'read', 'badge'])
        ->and(Colors::keys())->toHaveCount(8);
});

it('accepts only a six digit hex colour', function (string $value, bool $valid) {
    expect(Colors::isValid($value))->toBe($valid);
})->with([
    ['#2563eb', true],
    ['#ABCDEF', true],
    ['#abc', false],
    ['2563eb', false],
    ['#2563eg', false],
    ['#2563eb0', false],
    ['red', false],
    ['', false],
]);

it('reads a configured colour and lowercases it', function () {
    config(['notifications.colors.info' => '#ABCDEF']);

    expect(Colors::get('info'))->toBe('#abcdef');
});

it('falls back to the shipped default when a stored colour is malformed', function () {
    config([
        'notifications.colors.info'                   => 'not-a-colour',
        'notifications.settings.defaults.colors.info' => '#123456',
    ]);

    expect(Colors::get('info'))->toBe('#123456');
});

it('falls back to a neutral grey when there is no usable value at all', function () {
    config(['notifications.colors.info' => null, 'notifications.settings.defaults.colors.info' => null]);

    expect(Colors::get('info'))->toBe('#6b7280');
});

it('converts a hex colour to rgb channels', function () {
    expect(Colors::rgb('#2563eb'))->toBe([37, 99, 235])
        ->and(Colors::rgb('#000000'))->toBe([0, 0, 0])
        ->and(Colors::rgb('#ffffff'))->toBe([255, 255, 255]);
});

it('builds an rgba string at the requested alpha', function () {
    expect(Colors::rgba('#2563eb', 0.14))->toBe('rgba(37, 99, 235, 0.14)')
        ->and(Colors::rgba('#2563eb', 1.0))->toBe('rgba(37, 99, 235, 1)');
});

it('picks readable text for light and dark backgrounds', function () {
    expect(Colors::readableOn('#ffffff'))->toBe('#111827')
        ->and(Colors::readableOn('#fde047'))->toBe('#111827')
        ->and(Colors::readableOn('#000000'))->toBe('#ffffff')
        ->and(Colors::readableOn('#dc2626'))->toBe('#ffffff');
});

it('publishes a custom property set for every colour', function () {
    $css = Colors::cssVariables();

    foreach (Colors::keys() as $key) {
        expect($css)->toContain("--notifications-{$key}:")
            ->and($css)->toContain("--notifications-{$key}-tint:")
            ->and($css)->toContain("--notifications-{$key}-row:")
            ->and($css)->toContain("--notifications-{$key}-border:")
            ->and($css)->toContain("--notifications-{$key}-on:");
    }
});

it('reflects a changed colour in the published custom properties', function () {
    config(['notifications.colors.success' => '#001122']);

    expect(Colors::cssVariables())->toContain('--notifications-success: #001122;')
        ->and(Colors::cssVariables())->toContain('--notifications-success-tint: rgba(0, 17, 34, 0.14);');
});

it('ships a valid default for every colour key', function () {
    $shipped = require __DIR__.'/../../config/notifications.php';

    foreach (Colors::keys() as $key) {
        expect(Colors::isValid($shipped['colors'][$key] ?? null))->toBeTrue();
    }
});
