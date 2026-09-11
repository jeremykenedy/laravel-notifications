<?php

use Jeremykenedy\LaravelNotifications\Support\Frameworks;

dataset('framework combinations', function () {
    foreach (Frameworks::CSS as $css) {
        foreach (Frameworks::FRONTEND as $frontend) {
            yield "{$css} + {$frontend}" => [$css, $frontend];
        }
    }
});

it('boots artisan with the package commands registered', function () {
    $this->artisan('list')->assertSuccessful();
});

it('installs every css and frontend combination', function (string $css, string $frontend) {
    $this->artisan('notifications:install', ['--css' => $css, '--frontend' => $frontend])
        ->assertSuccessful();

    expect(config('notifications.css_framework'))->toBe($css)
        ->and(config('notifications.frontend'))->toBe($frontend);
})->with('framework combinations');

it('switches every css and frontend combination', function (string $css, string $frontend) {
    $this->artisan('notifications:switch', ['--css' => $css, '--frontend' => $frontend])
        ->assertSuccessful();

    expect(config('notifications.css_framework'))->toBe($css)
        ->and(config('notifications.frontend'))->toBe($frontend);
})->with('framework combinations');

it('switches only the css when only css is given', function () {
    $this->artisan('notifications:switch', ['--css' => 'bootstrap5'])->assertSuccessful();

    expect(config('notifications.css_framework'))->toBe('bootstrap5');
});

it('fails the switch command when given nothing to switch', function () {
    $this->artisan('notifications:switch')->assertFailed();
});

it('rejects an unknown css framework', function (string $command) {
    $this->artisan($command, ['--css' => 'bulma', '--frontend' => 'blade'])->assertFailed();
})->with(['notifications:install', 'notifications:switch']);

it('rejects an unknown frontend framework', function (string $command) {
    $this->artisan($command, ['--css' => 'tailwind', '--frontend' => 'angular'])->assertFailed();
})->with(['notifications:install', 'notifications:switch']);

it('publishes the config file on install', function () {
    $this->artisan('notifications:install', ['--css' => 'tailwind', '--frontend' => 'blade'])
        ->assertSuccessful();

    expect(file_exists(config_path('notifications.php')))->toBeTrue();
});
