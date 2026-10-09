<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Jeremykenedy\LaravelNotifications\Support\Colors;
use Jeremykenedy\LaravelNotifications\Support\Frameworks;
use Jeremykenedy\LaravelNotifications\Support\Settings;

it('renders the settings page for every css framework', function (string $css) {
    $this->usingCssFramework($css);

    $response = $this->actingAs($this->makeUser())
        ->get(route('notifications.settings.edit'))
        ->assertOk();

    foreach (Colors::keys() as $key) {
        $response->assertSee('name="colors['.$key.']"', false);
    }

    expect($response->getContent())->not->toContain('notifications::notifications.');
})->with(Frameworks::CSS);

it('shows a native colour input seeded with the current value', function () {
    config(['notifications.colors.info' => '#010203']);

    $this->actingAs($this->makeUser())
        ->get(route('notifications.settings.edit'))
        ->assertOk()
        ->assertSee('type="color"', false)
        ->assertSee('#010203', false);
});

it('saves colours from the form and shows them on the next load', function () {
    $user = $this->makeUser();
    $payload = array_fill_keys(Colors::keys(), '#123456');

    $this->actingAs($user)
        ->put(route('notifications.settings.update'), ['colors' => $payload])
        ->assertRedirect(route('notifications.settings.edit'))
        ->assertSessionHas('success');

    expect(app(Settings::class)->stored()['info'])->toBe('#123456');
});

it('rejects a colour that is not a six digit hex', function (string $bad) {
    $payload = array_fill_keys(Colors::keys(), '#123456');
    $payload['info'] = $bad;

    $this->actingAs($this->makeUser())
        ->put(route('notifications.settings.update'), ['colors' => $payload])
        ->assertSessionHasErrors('colors.info');

    expect(app(Settings::class)->stored())->toBe([]);
})->with(['red', '#abc', '123456', '#12345g', '#1234567', '']);

it('requires every colour to be present', function () {
    $payload = array_fill_keys(Colors::keys(), '#123456');
    unset($payload['badge']);

    $this->actingAs($this->makeUser())
        ->put(route('notifications.settings.update'), ['colors' => $payload])
        ->assertSessionHasErrors('colors.badge');
});

it('restores the shipped defaults when reset', function () {
    $user = $this->makeUser();
    app(Settings::class)->save(['info' => '#000111']);

    $this->actingAs($user)
        ->delete(route('notifications.settings.reset'))
        ->assertRedirect(route('notifications.settings.edit'))
        ->assertSessionHas('success');

    expect(app(Settings::class)->stored())->toBe([])
        ->and(config('notifications.colors.info'))->toBe('#2563eb');
});

it('explains itself instead of failing when the settings table is missing', function () {
    $user = $this->makeUser();
    Schema::drop(config('notifications.settings.table'));

    $this->actingAs($user)->get(route('notifications.settings.edit'))->assertOk();

    $this->actingAs($user)
        ->put(route('notifications.settings.update'), ['colors' => array_fill_keys(Colors::keys(), '#123456')])
        ->assertSessionHasErrors('colors');
});

it('rejects guests on every settings route', function (string $name, string $method) {
    $this->call($method, route($name))->assertRedirect(route('login'));
})->with([
    ['notifications.settings.edit', 'GET'],
    ['notifications.settings.update', 'PUT'],
    ['notifications.settings.reset', 'DELETE'],
]);

it('routes the settings paths to the settings controller, not the id routes', function (string $method, string $action) {
    $request = Request::create('/notifications/settings', $method);

    expect(app('router')->getRoutes()->match($request)->getActionMethod())->toBe($action);
})->with([
    ['GET', 'edit'],
    ['PUT', 'update'],
    ['DELETE', 'reset'],
]);

it('can be taken off the routing table on its own', function () {
    $this->usingConfig(['notifications.settings.route_enabled' => false]);

    expect(app('router')->getRoutes()->getByName('notifications.settings.edit'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('notifications.index'))->not->toBeNull();
});

it('honours a custom prefix and middleware', function () {
    $this->usingConfig([
        'notifications.settings.prefix'     => 'admin/notification-colors',
        'notifications.settings.middleware' => ['web'],
    ]);

    expect(app('router')->getRoutes()->getByName('notifications.settings.edit')->uri())
        ->toBe('admin/notification-colors');

    $this->get('/admin/notification-colors')->assertOk();
});

it('extends the layout the application names', function () {
    expect(config('notifications.settings.blade_extended'))->toBe('layouts.app');
});
