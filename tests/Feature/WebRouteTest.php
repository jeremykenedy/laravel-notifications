<?php

it('lists active notifications and hides archived ones from the inbox', function () {
    $user = $this->makeUser();
    $inbox = $this->notify($user, 'In the inbox');
    $filed = $this->notify($user, 'Filed away');
    $this->service()->archive($user, $filed->id);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee($inbox->data['title'])
        ->assertDontSee($filed->data['title']);
});

it('returns the unread count as json', function () {
    $user = $this->makeUser();
    $this->notify($user);

    $this->actingAs($user)
        ->getJson(route('notifications.count'))
        ->assertOk()
        ->assertExactJson(['count' => 1]);
});

it('marks a notification as read', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->actingAs($user)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marks a notification as unread', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);
    $this->service()->markAsRead($user, $notification->id);

    $this->actingAs($user)->post(route('notifications.unread', $notification->id));

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks every notification as read', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)->post(route('notifications.read-all'));

    expect($this->service()->unreadCount($user))->toBe(0);
});

it('archives and restores a notification', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->actingAs($user)->post(route('notifications.archive', $notification->id));
    expect($notification->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($user)->post(route('notifications.unarchive', $notification->id));
    expect($notification->fresh()->archived_at)->toBeNull();
});

it('archives every notification', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)->post(route('notifications.archive-all'));

    expect($this->service()->archivedCount($user))->toBe(2);
});

it('deletes a single notification', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->actingAs($user)->delete(route('notifications.destroy', $notification->id));

    expect($user->notifications()->count())->toBe(0);
});

it('deletes every notification', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->actingAs($user)->delete(route('notifications.destroy-all'));

    expect($user->notifications()->count())->toBe(0);
});

it('will not let one user act on another user notification', function () {
    $owner = $this->makeUser('owner@example.test');
    $intruder = $this->makeUser('intruder@example.test');
    $notification = $this->notify($owner);

    $this->actingAs($intruder)->post(route('notifications.read', $notification->id));
    $this->actingAs($intruder)->delete(route('notifications.destroy', $notification->id));

    expect($notification->fresh())->not->toBeNull()
        ->and($notification->fresh()->read_at)->toBeNull();
});

it('only deletes the notifications of the acting user', function () {
    $owner = $this->makeUser('owner@example.test');
    $other = $this->makeUser('other@example.test');
    $this->notify($owner);
    $this->notify($other);

    $this->actingAs($owner)->delete(route('notifications.destroy-all'));

    expect($owner->notifications()->count())->toBe(0)
        ->and($other->notifications()->count())->toBe(1);
});

it('rejects guests on every web route', function (string $name, string $method) {
    $route = in_array($name, ['notifications.index', 'notifications.count', 'notifications.read-all', 'notifications.archive-all', 'notifications.destroy-all'], true)
        ? route($name)
        : route($name, 'some-id');

    $this->call($method, $route)->assertRedirect(route('login'));
})->with([
    ['notifications.index', 'GET'],
    ['notifications.count', 'GET'],
    ['notifications.read', 'POST'],
    ['notifications.unread', 'POST'],
    ['notifications.read-all', 'POST'],
    ['notifications.archive', 'POST'],
    ['notifications.unarchive', 'POST'],
    ['notifications.archive-all', 'POST'],
    ['notifications.destroy', 'DELETE'],
    ['notifications.destroy-all', 'DELETE'],
]);

it('honours the configured page size', function () {
    config(['notifications.per_page' => 1]);
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $response = $this->actingAs($user)->get(route('notifications.index'))->assertOk();

    $shown = (int) str_contains($response->getContent(), 'One')
        + (int) str_contains($response->getContent(), 'Two');

    expect($shown)->toBe(1);
});
