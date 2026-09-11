<?php

use Illuminate\Http\Request;

it('returns paginated notifications', function () {
    $user = $this->makeUser();
    $this->notify($user, 'First');

    $this->actingAs($user)
        ->getJson(route('api.notifications.index'))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.data.title', 'First');
});

it('honours the per_page query parameter', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)
        ->getJson(route('api.notifications.index', ['per_page' => 1]))
        ->assertOk()
        ->assertJsonPath('per_page', 1)
        ->assertJsonCount(1, 'data');
});

it('returns unread notifications and the unread count', function () {
    $user = $this->makeUser();
    $read = $this->notify($user, 'Read');
    $this->notify($user, 'Unread');
    $this->service()->markAsRead($user, $read->id);

    $this->actingAs($user)->getJson(route('api.notifications.unread'))
        ->assertOk()
        ->assertJsonPath('total', 1);

    $this->actingAs($user)->getJson(route('api.notifications.count'))
        ->assertOk()
        ->assertExactJson(['count' => 1]);
});

it('marks a notification as read and back to unread', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->actingAs($user)->postJson(route('api.notifications.read', $notification->id))->assertOk();
    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->actingAs($user)->postJson(route('api.notifications.mark-unread', $notification->id))->assertOk();
    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks every notification as read and reports the count', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)
        ->postJson(route('api.notifications.read-all'))
        ->assertOk()
        ->assertJsonPath('message', '2 marked as read.');
});

it('archives and restores a notification', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->actingAs($user)->postJson(route('api.notifications.archive', $notification->id))->assertOk();
    expect($notification->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($user)->postJson(route('api.notifications.unarchive', $notification->id))->assertOk();
    expect($notification->fresh()->archived_at)->toBeNull();
});

it('archives everything and reports the count', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)
        ->postJson(route('api.notifications.archive-all'))
        ->assertOk()
        ->assertJsonPath('message', '2 archived.');

    expect($this->service()->archivedCount($user))->toBe(2);
});

it('deletes a single notification and then all of them', function () {
    $user = $this->makeUser();
    $first = $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)->deleteJson(route('api.notifications.destroy', $first->id))->assertOk();
    expect($user->notifications()->count())->toBe(1);

    $this->actingAs($user)
        ->deleteJson(route('api.notifications.destroy-all'))
        ->assertOk()
        ->assertJsonPath('message', '1 deleted.');

    expect($user->notifications()->count())->toBe(0);
});

it('routes archive-all and delete-all without being shadowed by the id routes', function () {
    $user = $this->makeUser();
    $this->notify($user);

    expect(app('router')->getRoutes()->match(
        Request::create('/api/notifications/archive-all', 'POST')
    )->getActionMethod())->toBe('archiveAll');

    expect(app('router')->getRoutes()->match(
        Request::create('/api/notifications', 'DELETE')
    )->getActionMethod())->toBe('destroyAll');
});

it('scopes every write to the authenticated user', function () {
    $owner = $this->makeUser('owner@example.test');
    $intruder = $this->makeUser('intruder@example.test');
    $notification = $this->notify($owner);

    $this->actingAs($intruder)->postJson(route('api.notifications.read', $notification->id));
    $this->actingAs($intruder)->postJson(route('api.notifications.archive', $notification->id));
    $this->actingAs($intruder)->deleteJson(route('api.notifications.destroy', $notification->id));

    expect($notification->fresh())->not->toBeNull()
        ->and($notification->fresh()->read_at)->toBeNull()
        ->and($notification->fresh()->archived_at)->toBeNull();
});

it('rejects guests on every api route', function (string $name, string $method, bool $needsId) {
    $route = $needsId ? route($name, 'some-id') : route($name);

    $this->json($method, $route)->assertUnauthorized();
})->with([
    ['api.notifications.index', 'GET', false],
    ['api.notifications.unread', 'GET', false],
    ['api.notifications.count', 'GET', false],
    ['api.notifications.read', 'POST', true],
    ['api.notifications.mark-unread', 'POST', true],
    ['api.notifications.read-all', 'POST', false],
    ['api.notifications.archive', 'POST', true],
    ['api.notifications.unarchive', 'POST', true],
    ['api.notifications.archive-all', 'POST', false],
    ['api.notifications.destroy', 'DELETE', true],
    ['api.notifications.destroy-all', 'DELETE', false],
]);

it('can be turned off without touching the web routes', function () {
    $this->usingConfig(['notifications.api.enabled' => false]);

    expect(app('router')->getRoutes()->getByName('api.notifications.index'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('notifications.index'))->not->toBeNull();
});

it('takes the whole package off the routing table when routes are disabled', function () {
    $this->usingConfig(['notifications.routes.enabled' => false]);

    expect(app('router')->getRoutes()->getByName('api.notifications.index'))->toBeNull()
        ->and(app('router')->getRoutes()->getByName('notifications.index'))->toBeNull();
});

it('ships sanctum as the default api guard', function () {
    $shipped = require __DIR__.'/../../config/notifications.php';

    expect($shipped['api']['middleware'])->toBe(['api', 'auth:sanctum'])
        ->and($shipped['api']['prefix'])->toBe('api/notifications');
});
