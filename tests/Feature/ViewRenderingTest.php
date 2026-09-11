<?php

use Jeremykenedy\LaravelNotifications\Support\Frameworks;

it('renders the notification center for every css framework', function (string $css) {
    $this->usingCssFramework($css);
    $user = $this->makeUser();
    $this->notify($user, 'Invoice ready', 'Invoice 1234 has been generated.');

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Invoice ready')
        ->assertSee('Invoice 1234 has been generated.');
})->with(Frameworks::CSS);

it('renders the send page for every css framework', function (string $css) {
    $this->usingCssFramework($css);
    $this->makeRole('admin');

    $this->actingAs($this->makeUser())
        ->get(route('notifications.send.create'))
        ->assertOk()
        ->assertSee('Admin');
})->with(Frameworks::CSS);

it('renders the empty state for every css framework', function (string $css) {
    $this->usingCssFramework($css);

    $this->actingAs($this->makeUser())
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee(__('notifications::notifications.all_caught_up'));
})->with(Frameworks::CSS);

it('renders the archived tab for every css framework', function (string $css) {
    $this->usingCssFramework($css);
    $user = $this->makeUser();
    $notification = $this->notify($user, 'Filed away');
    $this->service()->archive($user, $notification->id);

    $this->actingAs($user)
        ->get(route('notifications.index', ['archived' => 1]))
        ->assertOk()
        ->assertSee('Filed away');
})->with(Frameworks::CSS);

it('serves markup belonging to the selected css framework', function () {
    $render = function (string $css): string {
        $this->usingCssFramework($css);
        $user = $this->makeUser();
        $this->notify($user);

        return $this->actingAs($user)->get(route('notifications.index'))->getContent();
    };

    $tailwind = $render('tailwind');
    $bootstrap = $render('bootstrap5');

    expect($tailwind)->toContain('container mx-auto')
        ->and($tailwind)->not->toContain('btn btn-outline-secondary')
        ->and($bootstrap)->toContain('btn btn-outline-secondary')
        ->and($bootstrap)->not->toContain('container mx-auto');
});

it('falls back to the default framework when the configured one ships no views', function () {
    config(['notifications.css_framework' => 'not-a-framework']);

    expect(Frameworks::css())->toBe(Frameworks::DEFAULT_CSS);
});

it('escapes notification content in the alpine filter expression', function () {
    $user = $this->makeUser();
    $this->notify($user, "Quote ' and \\ backslash", 'Body');

    $content = $this->actingAs($user)->get(route('notifications.index'))->getContent();

    expect($content)->toContain('x-show=')
        ->and(substr_count($content, 'x-cloak'))->toBeGreaterThan(0);
});

it('requires authentication for the notification center', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});
