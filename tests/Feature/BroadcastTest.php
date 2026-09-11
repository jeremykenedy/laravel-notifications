<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Notifications\Events\NotificationSent;
use Jeremykenedy\LaravelNotifications\Domain\Events\NotificationCreated;
use Jeremykenedy\LaravelNotifications\Listeners\BroadcastNotificationCreated;
use Jeremykenedy\LaravelNotifications\Notifications\AppNotification;

/**
 * Records anything the package reports so a test can assert the package stayed
 * quiet rather than swallowing a failure.
 */
function recordingExceptionHandler(): object
{
    return new class() implements ExceptionHandler {
        public array $reported = [];

        public function report(Throwable $e): void
        {
            $this->reported[] = $e;
        }

        public function shouldReport(Throwable $e): bool
        {
            return true;
        }

        public function render($request, Throwable $e)
        {
            throw $e;
        }

        public function renderForConsole($output, Throwable $e): void
        {
        }
    };
}

it('broadcasts on the private channel for the user', function () {
    $event = new NotificationCreated(userId: 7, title: 'Title', message: 'Message', count: 3);

    expect($event->broadcastOn()[0]->name)->toBe('private-notifications.7')
        ->and($event->broadcastAs())->toBe('notification.created')
        ->and($event->count)->toBe(3);
});

it('accepts a uuid primary key on the broadcast event', function () {
    $event = new NotificationCreated(userId: 'c2a1f0de-0000-4000-8000-000000000001', title: 'T', message: 'M', count: 1);

    expect($event->broadcastOn()[0]->name)->toBe('private-notifications.c2a1f0de-0000-4000-8000-000000000001');
});

it('broadcasts cleanly for a user keyed by uuid', function () {
    $handler = recordingExceptionHandler();
    $this->app->instance(ExceptionHandler::class, $handler);
    config([
        'notifications.broadcast.enabled' => true,
        'broadcasting.default'            => 'log',
    ]);

    $user = $this->makeUuidUser();
    $user->notify(new AppNotification(title: 'Title', message: 'Message'));

    $listener = new BroadcastNotificationCreated();
    $listener->handle(new NotificationSent($user, new AppNotification(title: 'T', message: 'M'), 'database'));

    expect($handler->reported)->toBeEmpty()
        ->and($user->notifications()->count())->toBe(1);
});

it('ignores channels other than database', function () {
    $handler = recordingExceptionHandler();
    $this->app->instance(ExceptionHandler::class, $handler);
    config(['notifications.broadcast.enabled' => true]);

    $user = $this->makeUser();
    $listener = new BroadcastNotificationCreated();
    $listener->handle(new NotificationSent($user, new AppNotification(title: 'T', message: 'M'), 'mail'));

    expect($handler->reported)->toBeEmpty();
});

it('does nothing at all when broadcasting is turned off', function () {
    config(['notifications.broadcast.enabled' => false, 'broadcasting.default' => 'not-a-driver']);

    $user = $this->makeUser();
    $listener = new BroadcastNotificationCreated();
    $listener->handle(new NotificationSent($user, new AppNotification(title: 'T', message: 'M'), 'database'));

    expect($user->notifications()->count())->toBe(0);
});

it('reports a broadcast failure instead of hiding it', function () {
    $handler = recordingExceptionHandler();
    $this->app->instance(ExceptionHandler::class, $handler);
    config([
        'notifications.broadcast.enabled' => true,
        'broadcasting.default'            => 'does-not-exist',
    ]);

    $user = $this->makeUser();
    $listener = new BroadcastNotificationCreated();
    $listener->handle(new NotificationSent($user, new AppNotification(title: 'T', message: 'M'), 'database'));

    expect($handler->reported)->toHaveCount(1);
});
