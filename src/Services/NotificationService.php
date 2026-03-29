<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Notification;
use Jeremykenedy\LaravelNotifications\Notifications\AppNotification;

class NotificationService
{
    // ---- Read ----

    public function unreadCount(Authenticatable $user): int
    {
        return $user->unreadNotifications()->whereNull('archived_at')->count();
    }

    public function getActive(Authenticatable $user, int $perPage = 20): mixed
    {
        return $user->notifications()->whereNull('archived_at')->paginate($perPage);
    }

    public function getAll(Authenticatable $user, int $perPage = 20): mixed
    {
        return $user->notifications()->paginate($perPage);
    }

    public function getArchived(Authenticatable $user, int $perPage = 20): mixed
    {
        return $user->notifications()->whereNotNull('archived_at')->paginate($perPage);
    }

    public function getUnread(Authenticatable $user, int $perPage = 20): mixed
    {
        return $user->unreadNotifications()->whereNull('archived_at')->paginate($perPage);
    }

    public function archivedCount(Authenticatable $user): int
    {
        return $user->notifications()->whereNotNull('archived_at')->count();
    }

    // ---- Mark as read ----

    public function markAsRead(Authenticatable $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    public function markAllAsRead(Authenticatable $user): int
    {
        $count = $user->unreadNotifications()->whereNull('archived_at')->count();
        $user->unreadNotifications()->whereNull('archived_at')->get()->markAsRead();

        return $count;
    }

    // ---- Mark as unread ----

    public function markAsUnread(Authenticatable $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->update(['read_at' => null]);

            return true;
        }

        return false;
    }

    // ---- Archive ----

    public function archive(Authenticatable $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->update(['archived_at' => now(), 'read_at' => $notification->read_at ?? now()]);

            return true;
        }

        return false;
    }

    public function unarchive(Authenticatable $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->update(['archived_at' => null]);

            return true;
        }

        return false;
    }

    public function archiveAll(Authenticatable $user): int
    {
        $query = $user->notifications()->whereNull('archived_at');
        $count = $query->count();
        $query->update(['archived_at' => now()]);

        return $count;
    }

    // ---- Delete ----

    public function delete(Authenticatable $user, string $notificationId): bool
    {
        return (bool) $user->notifications()->where('id', $notificationId)->delete();
    }

    public function deleteAll(Authenticatable $user): int
    {
        return $user->notifications()->delete();
    }

    // ---- Send (programmatic creation) ----

    /**
     * Send a notification to one or more users.
     *
     * @param Authenticatable|iterable $users      Single user or collection
     * @param string                   $title      Notification title
     * @param string                   $message    Notification message body
     * @param string|null              $type       Notification type: info, success, warning, danger, system
     * @param string|null              $actionUrl  Optional action URL
     * @param string|null              $actionText Optional action button text
     * @param bool                     $sendEmail  Also send via email
     * @param string|null              $icon       Optional icon name
     */
    public function send(
        Authenticatable|iterable $users,
        string $title,
        string $message,
        ?string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        bool $sendEmail = false,
        ?string $icon = null,
    ): void {
        $notification = new AppNotification(
            title: $title,
            message: $message,
            type: $type ?? 'info',
            actionUrl: $actionUrl,
            actionText: $actionText,
            sendEmail: $sendEmail,
            icon: $icon,
        );

        if ($users instanceof Authenticatable) {
            $users->notify($notification);
        } else {
            Notification::send($users, $notification);
        }
    }

    /**
     * Send to all users matching a query.
     */
    public function sendToAll(
        string $title,
        string $message,
        ?string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        bool $sendEmail = false,
    ): int {
        $userModel = config('notifications.user_model', 'App\\Models\\User');
        $users = $userModel::all();

        $this->send($users, $title, $message, $type, $actionUrl, $actionText, $sendEmail);

        return $users->count();
    }

    /**
     * Send to users with a specific role.
     */
    public function sendToRole(
        string $roleSlug,
        string $title,
        string $message,
        ?string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
    ): int {
        $userModel = config('notifications.user_model', 'App\\Models\\User');
        $users = $userModel::whereHas('roles', fn ($q) => $q->where('slug', $roleSlug))->get();

        $this->send($users, $title, $message, $type, $actionUrl, $actionText);

        return $users->count();
    }
}
