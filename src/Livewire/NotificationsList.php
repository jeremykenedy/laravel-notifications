<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Livewire;

use Illuminate\Support\Facades\Auth;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsList extends Component
{
    use WithPagination;

    protected NotificationService $service;

    public function boot(NotificationService $service): void
    {
        $this->service = $service;
    }

    public function markAsRead(string $id): void
    {
        $this->service->markAsRead(Auth::user(), $id);
    }

    public function markAllAsRead(): void
    {
        $this->service->markAllAsRead(Auth::user());
    }

    public function delete(string $id): void
    {
        $this->service->delete(Auth::user(), $id);
    }

    public function render()
    {
        return view('notifications::livewire.notifications-list', [
            'notifications' => $this->service->getActive(
                Auth::user(),
                (int) config('notifications.per_page', 20),
            ),
        ]);
    }
}
