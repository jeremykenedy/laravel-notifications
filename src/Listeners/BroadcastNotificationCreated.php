<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Jeremykenedy\LaravelNotifications\Domain\Events\NotificationCreated;

class BroadcastNotificationCreated
{
    public function handle(NotificationSent $event): void
    {
        if (!config('notifications.broadcast.enabled', true)) {
            return;
        }

        if ($event->channel !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;

        if (!method_exists($notifiable, 'unreadNotifications')) {
            return;
        }

        $userId = method_exists($notifiable, 'getKey') ? $notifiable->getKey() : ($notifiable->id ?? null);

        if ($userId === null) {
            return;
        }

        $data = method_exists($event->notification, 'toArray')
            ? $event->notification->toArray($notifiable)
            : [];

        try {
            broadcast(new NotificationCreated(
                userId: $userId,
                title: $data['title'] ?? 'New Notification',
                message: $data['message'] ?? '',
                count: $notifiable->unreadNotifications()->count(),
            ));
        } catch (\Throwable $e) {
            // A missing or unreachable broadcast driver must not fail the notification itself.
            report($e);
        }
    }
}
