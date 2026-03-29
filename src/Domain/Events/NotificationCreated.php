<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Domain\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly string $message,
        public readonly int $count,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('notifications.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
