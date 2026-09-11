<?php

use Jeremykenedy\LaravelNotifications\Tests\Fixtures\User;
use Illuminate\Support\Facades\Notification;
use Jeremykenedy\LaravelNotifications\Notifications\AppNotification;

it('shows the send form with the roles and the user count', function () {
    $this->makeUser();
    $this->makeRole('admin');

    $this->actingAs($this->makeUser('sender@example.test'))
        ->get(route('notifications.send.create'))
        ->assertOk()
        ->assertSee('Admin')
        ->assertSee('2 total users');
});

it('sends to every user', function () {
    $sender = $this->makeUser('sender@example.test');
    $recipient = $this->makeUser('recipient@example.test');

    $this->actingAs($sender)
        ->post(route('notifications.send.store'), [
            'title'    => 'Maintenance',
            'message'  => 'Scheduled for tonight.',
            'audience' => 'all',
        ])
        ->assertRedirect(route('notifications.send.create'));

    expect($recipient->notifications()->count())->toBe(1)
        ->and($recipient->notifications()->first()->data['title'])->toBe('Maintenance');
});

it('sends to a role when role_id arrives as a form string', function () {
    $admin = $this->makeUser('admin@example.test');
    $plain = $this->makeUser('plain@example.test');
    $role = $this->makeRole('admin');
    $admin->roles()->attach($role->id);

    $this->actingAs($admin)
        ->post(route('notifications.send.store'), [
            'title'    => 'Admins only',
            'message'  => 'For the admin team.',
            'audience' => 'role',
            'role_id'  => (string) $role->id,
        ])
        ->assertRedirect(route('notifications.send.create'))
        ->assertSessionHasNoErrors();

    expect($admin->notifications()->count())->toBe(1)
        ->and($plain->notifications()->count())->toBe(0);
});

it('reports a role that cannot be found instead of sending to the wrong people', function () {
    $sender = $this->makeUser('sender@example.test');

    $this->actingAs($sender)
        ->post(route('notifications.send.store'), [
            'title'    => 'Nowhere',
            'message'  => 'Should not send.',
            'audience' => 'role',
            'role_id'  => '9999',
        ])
        ->assertSessionHasErrors('role_id');

    expect($sender->notifications()->count())->toBe(0);
});

it('carries the action link through to the stored notification', function () {
    $sender = $this->makeUser('sender@example.test');

    $this->actingAs($sender)->post(route('notifications.send.store'), [
        'title'       => 'Invoice',
        'message'     => 'Invoice ready.',
        'audience'    => 'all',
        'type'        => 'success',
        'action_url'  => 'https://example.test/invoices/1',
        'action_text' => 'Open invoice',
    ]);

    $data = $sender->notifications()->first()->data;

    expect($data['type'])->toBe('success')
        ->and($data['action_url'])->toBe('https://example.test/invoices/1')
        ->and($data['action_text'])->toBe('Open invoice');
});

it('rejects a send that is missing required fields', function (array $payload, string $field) {
    $this->actingAs($this->makeUser())
        ->post(route('notifications.send.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(User::first()->notifications()->count())->toBe(0);
})->with([
    'no title'          => [['message' => 'Body', 'audience' => 'all'], 'title'],
    'no message'        => [['title' => 'Title', 'audience' => 'all'], 'message'],
    'no audience'       => [['title' => 'Title', 'message' => 'Body'], 'audience'],
    'unknown audience'  => [['title' => 'Title', 'message' => 'Body', 'audience' => 'nobody'], 'audience'],
    'role with no id'   => [['title' => 'Title', 'message' => 'Body', 'audience' => 'role'], 'role_id'],
    'unknown type'      => [['title' => 'Title', 'message' => 'Body', 'audience' => 'all', 'type' => 'purple'], 'type'],
    'bad action url'    => [['title' => 'Title', 'message' => 'Body', 'audience' => 'all', 'action_url' => 'not a url'], 'action_url'],
]);

it('honours the email checkbox when sending to a role', function () {
    $admin = $this->makeUser('admin@example.test');
    $admin->roles()->attach($this->makeRole('admin')->id);

    Notification::fake();

    $this->actingAs($admin)->post(route('notifications.send.store'), [
        'title'      => 'Admins only',
        'message'    => 'For the admin team.',
        'audience'   => 'role',
        'role_id'    => (string) $admin->roles()->first()->id,
        'send_email' => '1',
    ])->assertSessionHasNoErrors();

    Notification::assertSentTo(
        $admin,
        AppNotification::class,
        fn ($notification) => in_array('mail', $notification->via($admin), true),
    );
});

it('keeps a rejected audience value out of the alpine expression', function () {
    $payload = "all'});window.pwned=1;({a:'";

    // Validation fails, so the form is redisplayed with the rejected value in
    // old input and interpolated back into the Alpine attribute.
    $content = $this->actingAs($this->makeUser())
        ->from(route('notifications.send.create'))
        ->followingRedirects()
        ->post(route('notifications.send.store'), ['title' => 'T', 'message' => 'M', 'audience' => $payload])
        ->getContent();

    expect(preg_match('/<form[^>]*x-data="([^"]*)"/', $content, $matches))->toBe(1);

    // The browser decodes the attribute before Alpine parses it, so entity
    // escaping alone does not keep the value inside the string literal.
    $decoded = html_entity_decode($matches[1], ENT_QUOTES);

    expect($decoded)->toContain('audience:')
        ->and($decoded)->not->toContain("'});");
});

it('rejects guests on the send routes', function () {
    $this->get(route('notifications.send.create'))->assertRedirect(route('login'));
    $this->post(route('notifications.send.store'))->assertRedirect(route('login'));
});
