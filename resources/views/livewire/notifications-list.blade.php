<div>
    <div class="container mx-auto max-w-3xl px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Notifications</h1>
            <button wire:click="markAllAsRead" class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg">Mark All Read</button>
        </div>
        @forelse($notifications as $n)
            <div class="p-4 rounded-lg border mb-2 {{ $n->read_at ? 'border-gray-200 bg-white dark:bg-gray-800' : 'border-blue-200 bg-blue-50 dark:bg-blue-900/20' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm">{{ $n->data['message'] ?? $n->type }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex gap-1">
                        @unless($n->read_at)<button wire:click="markAsRead('{{ $n->id }}')" class="px-2 py-1 text-xs bg-gray-200 rounded">Read</button>@endunless
                        <button wire:click="delete('{{ $n->id }}')" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500">No notifications.</div>
        @endforelse
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</div>
