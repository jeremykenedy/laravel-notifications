<div>
    <div class="container mx-auto max-w-3xl px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.notifications') }}</h1>
            <button wire:click="markAllAsRead" class="cursor-pointer px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400">
                {{ __('notifications::notifications.mark_all_read') }}
            </button>
        </div>

        <ul class="space-y-2 list-none p-0 m-0">
            @forelse($notifications as $notification)
                <li class="p-4 rounded-lg border {{ $notification->read_at ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50' : 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            @if(isset($notification->data['title']))
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notification->data['title'] }}</p>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $notification->data['message'] ?? class_basename($notification->type) }}</p>
                            <time datetime="{{ $notification->created_at->toIso8601String() }}" class="text-xs text-gray-400 dark:text-gray-500 mt-1 block">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>
                        <div class="flex gap-1 flex-shrink-0">
                            @unless($notification->read_at)
                                <button wire:click="markAsRead('{{ $notification->id }}')" class="cursor-pointer px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                    {{ __('notifications::notifications.mark_read') }}
                                </button>
                            @endunless
                            <button wire:click="delete('{{ $notification->id }}')" class="cursor-pointer px-2 py-1 text-xs rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 focus:outline-none focus:ring-2 focus:ring-red-400">
                                {{ __('notifications::notifications.delete') }}
                            </button>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-center py-8 text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.no_notifications') }}</li>
            @endforelse
        </ul>

        @if($notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
