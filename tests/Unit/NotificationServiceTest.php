<?php

use Jeremykenedy\LaravelNotifications\Services\NotificationService;
use Illuminate\Support\Facades\Notification;
use Jeremykenedy\LaravelNotifications\Notifications\AppNotification;

it('resolves the service as a singleton', function () {
    expect(app(NotificationService::class))
        ->toBeInstanceOf(NotificationService::class)
        ->toBe(app(NotificationService::class));
});

it('counts only unread notifications that are not archived', function () {
    $user = $this->makeUser();
    $read = $this->notify($user, 'Read one');
    $archived = $this->notify($user, 'Archived one');
    $this->notify($user, 'Still unread');

    $this->service()->markAsRead($user, $read->id);
    $this->service()->archive($user, $archived->id);

    expect($this->service()->unreadCount($user))->toBe(1);
});

it('separates active notifications from archived ones', function () {
    $user = $this->makeUser();
    $archived = $this->notify($user, 'Filed away');
    $this->notify($user, 'In the inbox');

    $this->service()->archive($user, $archived->id);

    expect($this->service()->getActive($user)->total())->toBe(1)
        ->and($this->service()->getArchived($user)->total())->toBe(1)
        ->and($this->service()->getAll($user)->total())->toBe(2)
        ->and($this->service()->archivedCount($user))->toBe(1);
});

it('marks a single notification as read and back to unread', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    expect($this->service()->markAsRead($user, $notification->id))->toBeTrue()
        ->and($notification->fresh()->read_at)->not->toBeNull();

    expect($this->service()->markAsUnread($user, $notification->id))->toBeTrue()
        ->and($notification->fresh()->read_at)->toBeNull();
});

it('marks every unread notification as read and reports how many', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    expect($this->service()->markAllAsRead($user))->toBe(2)
        ->and($this->service()->unreadCount($user))->toBe(0);
});

it('leaves archived notifications alone when marking all as read', function () {
    $user = $this->makeUser();
    $archived = $this->notify($user, 'Archived');
    $this->notify($user, 'Active');
    $this->service()->archive($user, $archived->id);

    expect($this->service()->markAllAsRead($user))->toBe(1);
});

it('marks a notification as read when it is archived', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);

    $this->service()->archive($user, $notification->id);

    expect($notification->fresh()->archived_at)->not->toBeNull()
        ->and($notification->fresh()->read_at)->not->toBeNull();
});

it('keeps the original read time when archiving an already read notification', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);
    $this->service()->markAsRead($user, $notification->id);
    $readAt = $notification->fresh()->read_at;

    $this->travelTo(now()->addMinutes(5));
    $this->service()->archive($user, $notification->id);

    expect($notification->fresh()->read_at->timestamp)->toBe($readAt->timestamp);
});

it('restores an archived notification', function () {
    $user = $this->makeUser();
    $notification = $this->notify($user);
    $this->service()->archive($user, $notification->id);

    expect($this->service()->unarchive($user, $notification->id))->toBeTrue()
        ->and($notification->fresh()->archived_at)->toBeNull();
});

it('archives everything that is not already archived', function () {
    $user = $this->makeUser();
    $first = $this->notify($user, 'One');
    $this->notify($user, 'Two');
    $this->service()->archive($user, $first->id);

    expect($this->service()->archiveAll($user))->toBe(1)
        ->and($this->service()->archivedCount($user))->toBe(2);
});

it('marks unread notifications as read when archiving in bulk', function () {
    $user = $this->makeUser();
    $this->notify($user, 'One');
    $this->notify($user, 'Two');

    $this->service()->archiveAll($user);

    expect($user->notifications()->whereNull('read_at')->count())->toBe(0)
        ->and($this->service()->unreadCount($user))->toBe(0);
});

it('keeps the original read time when archiving in bulk', function () {
    $user = $this->makeUser();
    $read = $this->notify($user, 'Already read');
    $this->service()->markAsRead($user, $read->id);
    $readAt = $read->fresh()->read_at;

    $this->travelTo(now()->addMinutes(5));
    $this->service()->archiveAll($user);

    expect($read->fresh()->read_at->timestamp)->toBe($readAt->timestamp);
});

it('sends to a role by email when asked to', function () {
    $admin = $this->makeUser('admin@example.test');
    $admin->roles()->attach($this->makeRole('admin')->id);

    Notification::fake();
    $this->service()->sendToRole('admin', 'Title', 'Message', 'info', null, null, true);

    Notification::assertSentTo(
        $admin,
        AppNotification::class,
        fn ($notification) => in_array('mail', $notification->via($admin), true),
    );
});

it('deletes a single notification and every notification', function () {
    $user = $this->makeUser();
    $first = $this->notify($user, 'One');
    $this->notify($user, 'Two');

    expect($this->service()->delete($user, $first->id))->toBeTrue()
        ->and($user->notifications()->count())->toBe(1);

    expect($this->service()->deleteAll($user))->toBe(1)
        ->and($user->notifications()->count())->toBe(0);
});

it('never touches a notification belonging to another user', function () {
    $owner = $this->makeUser('owner@example.test');
    $other = $this->makeUser('other@example.test');
    $notification = $this->notify($owner);

    expect($this->service()->markAsRead($other, $notification->id))->toBeFalse()
        ->and($this->service()->markAsUnread($other, $notification->id))->toBeFalse()
        ->and($this->service()->archive($other, $notification->id))->toBeFalse()
        ->and($this->service()->unarchive($other, $notification->id))->toBeFalse()
        ->and($this->service()->delete($other, $notification->id))->toBeFalse()
        ->and($notification->fresh())->not->toBeNull();
});

it('returns false for a notification id that does not exist', function () {
    $user = $this->makeUser();

    expect($this->service()->markAsRead($user, 'missing-id'))->toBeFalse()
        ->and($this->service()->archive($user, 'missing-id'))->toBeFalse()
        ->and($this->service()->delete($user, 'missing-id'))->toBeFalse();
});

it('paginates using the page size it is given', function () {
    $user = $this->makeUser();
    foreach (range(1, 5) as $i) {
        $this->notify($user, "Notification {$i}");
    }

    expect($this->service()->getAll($user, 2)->perPage())->toBe(2)
        ->and($this->service()->getAll($user, 2)->total())->toBe(5);
});

it('sends to every user and reports the count', function () {
    $this->makeUser('one@example.test');
    $this->makeUser('two@example.test');

    expect($this->service()->sendToAll('Title', 'Message'))->toBe(2);
});

it('sends only to users holding the given role', function () {
    $admin = $this->makeUser('admin@example.test');
    $this->makeUser('plain@example.test');
    $admin->roles()->attach($this->makeRole('admin')->id);

    expect($this->service()->sendToRole('admin', 'Title', 'Message'))->toBe(1)
        ->and($admin->notifications()->count())->toBe(1);
});

it('stores the action url and text only when an action url is given', function () {
    $user = $this->makeUser();
    $this->service()->send($user, 'Title', 'Message', 'info', '/invoices/1', 'View invoice');
    $this->service()->send($user, 'Plain', 'Message');

    $notifications = $user->notifications()->get()->keyBy(fn ($row) => $row->data['title']);
    $withAction = $notifications['Title'];
    $withoutAction = $notifications['Plain'];

    expect($withAction->data['action_url'])->toBe('/invoices/1')
        ->and($withAction->data['action_text'])->toBe('View invoice')
        ->and($withoutAction->data)->not->toHaveKey('action_url');
});
