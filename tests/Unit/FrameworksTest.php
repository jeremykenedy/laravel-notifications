<?php

use Jeremykenedy\LaravelNotifications\Support\Frameworks;

it('ships views for three css frameworks and five frontends', function () {
    expect(Frameworks::CSS)->toBe(['tailwind', 'bootstrap5', 'bootstrap4'])
        ->and(Frameworks::FRONTEND)->toBe(['blade', 'livewire', 'vue', 'react', 'svelte']);
});

it('validates framework names', function () {
    expect(Frameworks::isValidCss('bootstrap5'))->toBeTrue()
        ->and(Frameworks::isValidCss('bulma'))->toBeFalse()
        ->and(Frameworks::isValidFrontend('svelte'))->toBeTrue()
        ->and(Frameworks::isValidFrontend('angular'))->toBeFalse();
});

it('prefers its own setting over the ui kit setting', function () {
    config(['notifications.css_framework' => 'bootstrap4', 'ui-kit.css_framework' => 'bootstrap5']);

    expect(Frameworks::css())->toBe('bootstrap4');
});

it('falls back to the ui kit setting when it has none of its own', function () {
    config([
        'notifications.css_framework' => null,
        'notifications.frontend'      => null,
        'ui-kit.css_framework'        => 'bootstrap5',
        'ui-kit.frontend'             => 'vue',
    ]);

    expect(Frameworks::css())->toBe('bootstrap5')
        ->and(Frameworks::frontend())->toBe('vue');
});

it('falls back to the package defaults when nothing is configured', function () {
    config(['notifications.css_framework' => null, 'notifications.frontend' => null, 'ui-kit' => null]);

    expect(Frameworks::css())->toBe('tailwind')
        ->and(Frameworks::frontend())->toBe('blade');
});

it('ignores a framework name it does not recognise', function () {
    config(['notifications.css_framework' => 'bulma', 'notifications.frontend' => 'angular']);

    expect(Frameworks::css())->toBe('tailwind')
        ->and(Frameworks::frontend())->toBe('blade');
});

it('has a blade view directory for every css framework it claims', function () {
    foreach (Frameworks::CSS as $css) {
        $path = realpath(__DIR__.'/../../resources/views/'.$css.'/blade');

        expect(is_dir($path))->toBeTrue()
            ->and(is_file($path.'/index.blade.php'))->toBeTrue()
            ->and(is_file($path.'/send.blade.php'))->toBeTrue()
            ->and(is_file($path.'/partials/bell.blade.php'))->toBeTrue();
    }
});

it('has a starter component for every javascript frontend it claims', function () {
    foreach (['vue', 'react', 'svelte'] as $frontend) {
        $dir = realpath(__DIR__.'/../../resources/js/'.$frontend.'/pages');

        expect(is_dir($dir))->toBeTrue()
            ->and(glob($dir.'/NotificationsIndex.*'))->not->toBeEmpty();
    }
});
