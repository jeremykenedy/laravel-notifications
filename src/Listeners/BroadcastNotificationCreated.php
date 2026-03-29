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

        // Only broadcast for database channel notifications
        if ($event->channel !== 'database') {
            return;
        }

        $notifiable = $event->notifiable;

        if (!method_exists($notifiable, 'unreadNotifications')) {
            return;
        }

        $data = method_exists($event->notification, 'toArray')
            ? $event->notification->toArray($notifiable)
            : [];

        try {
            broadcast(new NotificationCreated(
                userId: $notifiable->id,
                title: $data['title'] ?? 'New Notification',
                message: $data['message'] ?? '',
                count: $notifiable->unreadNotifications()->count(),
            ));
        } catch (\Throwable $e) {
            // Silently fail if broadcast driver is unavailable (e.g., testing, no Reverb running)
        }
    }
}
