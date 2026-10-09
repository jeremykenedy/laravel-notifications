<?php

use Jeremykenedy\LaravelNotifications\Support\Colors;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;
use Jeremykenedy\LaravelNotifications\Support\Settings;

it('publishes the colour variables on the notification center for every framework', function (string $css) {
    $this->usingCssFramework($css);
    $user = $this->makeUser();
    $this->notify($user, 'Invoice ready');

    $content = $this->actingAs($user)->get(route('notifications.index'))->assertOk()->getContent();

    foreach (Colors::keys() as $key) {
        expect($content)->toContain("--notifications-{$key}:");
    }
})->with(Frameworks::CSS);

it('colours a notification row from the saved colour for every framework', function (string $css) {
    $this->usingCssFramework($css);
    app(Settings::class)->save(['success' => '#0a0b0c']);

    $user = $this->makeUser();
    $this->service()->send($user, 'Done', 'All good.', 'success');

    $content = $this->actingAs($user)->get(route('notifications.index'))->assertOk()->getContent();

    expect($content)->toContain('--notifications-success: #0a0b0c;')
        ->and($content)->toContain('var(--notifications-success');
})->with(Frameworks::CSS);

it('uses the read accent once a notification has been read', function () {
    $user = $this->makeUser();
    $notification = $this->service()->send($user, 'Done', 'All good.', 'success');
    $stored = $user->notifications()->firstOrFail();
    $this->service()->markAsRead($user, $stored->id);

    $content = $this->actingAs($user)->get(route('notifications.index'))->assertOk()->getContent();

    expect($content)->toContain('var(--notifications-read');
});

it('never puts an unknown type into a css variable name', function () {
    $user = $this->makeUser();
    $this->service()->send($user, 'Odd', 'Body', 'info');
    $user->notifications()->firstOrFail()->update(['data' => ['title' => 'Odd', 'message' => 'Body', 'type' => 'x); evil {']]);

    $content = $this->actingAs($user)->get(route('notifications.index'))->assertOk()->getContent();

    expect($content)->not->toContain('evil')
        ->and($content)->toContain('var(--notifications-info');
});

it('derives a tint, a row wash, a border and a readable foreground per colour', function () {
    config(['notifications.colors.info' => '#2563eb']);
    $css = Colors::cssVariables();

    expect($css)->toContain('--notifications-info: #2563eb;')
        ->and($css)->toContain('--notifications-info-tint: rgba(37, 99, 235, 0.14);')
        ->and($css)->toContain('--notifications-info-row: rgba(37, 99, 235, 0.07);')
        ->and($css)->toContain('--notifications-info-border: rgba(37, 99, 235, 0.35);')
        ->and($css)->toContain('--notifications-info-on: #ffffff;');
});

it('can be included on any page without the settings routes being hit', function () {
    $colors = view('notifications::partials.colors')->render();

    expect($colors)->toContain('--notifications-info:')
        ->and($colors)->toContain('<style>');
});
