<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsList extends Component
{
    use WithPagination;

    public function markAsRead(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function delete(string $id): void
    {
        Auth::user()->notifications()->where('id', $id)->delete();
    }

    public function render()
    {
        return view('notifications::livewire.notifications-list', [
            'notifications' => Auth::user()->notifications()->paginate(20),
        ]);
    }
}
