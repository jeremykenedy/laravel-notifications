@extends('layouts.app')

@section('template_title')
    {{ __('notifications::notifications.notifications') }}
@endsection

@push('template_linked_css')
<style>
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .6; transform: scale(1.4); }
    }
    .animate-pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }
    [data-tooltip] { position: relative; }
    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 6px);
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.4;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s;
        z-index: 50;
        background: #1f2937;
        color: #f9fafb;
    }
    :is(.dark) [data-tooltip]::after { background: #e5e7eb; color: #111827; }
    [data-tooltip]:hover::after,
    [data-tooltip]:focus-visible::after { opacity: 1; }
    @media (prefers-reduced-motion: reduce) {
        .animate-pulse-dot { animation: none; }
    }
</style>
@endpush

@php
    $btnBase = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-900';
    $btnSecondary = $btnBase.' border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-gray-400';
    $btnDanger = $btnBase.' border-red-300 dark:border-red-800 text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:ring-red-400';
    $btnSolidDanger = $btnBase.' border-red-600 bg-red-600 text-white hover:bg-red-700 focus:ring-red-400';
    $iconBtn = 'cursor-pointer p-1.5 rounded-md text-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-900';
@endphp

@section('content')
<div class="container mx-auto max-w-4xl px-4 py-8" x-data="{
    search: '',
    deleteFormId: null,
    deleteAllForm: false,
}">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.notifications') }}</h1>
        <div class="flex items-center gap-2">
            @if(!$showArchived && !$notifications->isEmpty())
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                        @csrf
                        <button type="submit" class="{{ $btnSecondary }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('notifications::notifications.mark_all_read') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('notifications.archive-all') }}" class="inline">
                        @csrf
                        <button type="submit" class="{{ $btnSecondary }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                            {{ __('notifications::notifications.archive_all') }}
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy-all') }}" id="delete-all-notifs" class="inline">
                    @csrf @method('DELETE')
                    <button type="button" class="{{ $btnDanger }}" @click="deleteAllForm = true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        {{ __('notifications::notifications.delete_all') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Archive toggle and filter --}}
    @if($totalCount > 0)
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('notifications.index') }}" @if(!$showArchived) aria-current="page" @endif class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ !$showArchived ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" /></svg>
                {{ __('notifications::notifications.inbox') }}
            </a>
            <a href="{{ route('notifications.index', ['archived' => 1]) }}" @if($showArchived) aria-current="page" @endif class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $showArchived ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                {{ __('notifications::notifications.archived') }}
                @if($archivedCount > 0)
                    <span class="ml-0.5 inline-flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-600 px-1.5 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $archivedCount }}</span>
                @endif
            </a>
        </div>
        <div class="relative">
            <label for="notification-filter" class="sr-only">{{ __('notifications::notifications.filter') }}</label>
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            <input id="notification-filter" type="search" x-model="search" placeholder="{{ __('notifications::notifications.filter') }}" class="pl-8 pr-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-48 transition-colors" />
        </div>
    </div>
    @endif

    @if(config('notifications.flash_messages', false) && session('success'))
        <div role="status" class="mb-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <div class="text-center py-12">
                @if($showArchived)
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.no_archived') }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.no_archived_hint') }}</p>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.all_caught_up') }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.no_notifications') }}</p>
                @endif
            </div>
        </div>
    @else
        <ul class="space-y-2 list-none p-0 m-0">
            @foreach($notifications as $notification)
                @php
                    $searchText = strtolower(trim(($notification->data['title'] ?? '').' '.($notification->data['message'] ?? '')));
                    $nType = $notification->data['type'] ?? 'info';
                    $iconBg = match($nType) {
                        'success' => 'bg-green-100 dark:bg-green-900/40',
                        'warning' => 'bg-yellow-100 dark:bg-yellow-900/40',
                        'danger'  => 'bg-red-100 dark:bg-red-900/40',
                        'system'  => 'bg-purple-100 dark:bg-purple-900/40',
                        default   => $notification->read_at ? 'bg-gray-100 dark:bg-gray-700' : 'bg-blue-100 dark:bg-blue-900/40',
                    };
                @endphp
                <li
                    x-show="search === '' || @js($searchText).includes(search.toLowerCase())"
                    x-cloak
                    class="flex items-start gap-4 p-4 rounded-lg border transition-colors {{ $notification->read_at ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50' : 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20' }}"
                >
                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5 relative">
                        <span class="inline-flex items-center justify-center h-9 w-9 rounded-full {{ $iconBg }}">
                            @if($notification->read_at)
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            @else
                                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                            @endif
                        </span>
                        @unless($notification->read_at)
                            <span class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-green-500 border-2 border-white dark:border-gray-900 animate-pulse-dot"></span>
                            <span class="sr-only">{{ __('notifications::notifications.unread') }}</span>
                        @endunless
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        @if(isset($notification->data['title']))
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notification->data['title'] }}</p>
                        @endif
                        <p class="text-sm text-gray-600 dark:text-gray-300 {{ isset($notification->data['title']) ? 'mt-0.5' : '' }}">{{ $notification->data['message'] ?? class_basename($notification->type) }}</p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <time datetime="{{ $notification->created_at->toIso8601String() }}" class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</time>
                            @if(isset($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $notification->data['action_text'] ?? __('notifications::notifications.view') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if($showArchived)
                            <form method="POST" action="{{ route('notifications.unarchive', $notification->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="{{ $iconBtn }} hover:text-blue-600 hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-900/20 focus:ring-blue-400" data-tooltip="{{ __('notifications::notifications.unarchive') }}" aria-label="{{ __('notifications::notifications.unarchive') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" /></svg>
                                </button>
                            </form>
                        @else
                            @if($notification->read_at)
                                <form method="POST" action="{{ route('notifications.unread', $notification->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $iconBtn }} hover:text-yellow-600 hover:bg-yellow-50 dark:hover:text-yellow-400 dark:hover:bg-yellow-900/20 focus:ring-yellow-400" data-tooltip="{{ __('notifications::notifications.mark_unread') }}" aria-label="{{ __('notifications::notifications.mark_unread') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V18" /></svg>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $iconBtn }} hover:text-blue-600 hover:bg-blue-50 dark:hover:text-blue-400 dark:hover:bg-blue-900/20 focus:ring-blue-400" data-tooltip="{{ __('notifications::notifications.mark_read') }}" aria-label="{{ __('notifications::notifications.mark_read') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.archive', $notification->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="{{ $iconBtn }} hover:text-gray-600 hover:bg-gray-50 dark:hover:text-gray-300 dark:hover:bg-gray-700 focus:ring-gray-400" data-tooltip="{{ __('notifications::notifications.archive') }}" aria-label="{{ __('notifications::notifications.archive') }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" id="delete-notif-{{ $notification->id }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="button" @click="deleteFormId = 'delete-notif-{{ $notification->id }}'" class="{{ $iconBtn }} hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/20 focus:ring-red-400" data-tooltip="{{ __('notifications::notifications.delete') }}" aria-label="{{ __('notifications::notifications.delete') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    @endif

    {{-- Delete single confirm modal --}}
    <div x-show="deleteFormId !== null" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="delete-one-title">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="deleteFormId !== null" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="deleteFormId = null"></div>
            <div x-show="deleteFormId !== null" @keydown.escape.window="deleteFormId = null" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative w-full max-w-md transform rounded-xl bg-white dark:bg-gray-800 p-6 text-left shadow-xl">
                <h3 id="delete-one-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.confirm_delete_title') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.confirm_delete_body') }}</p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" class="{{ $btnSecondary }}" @click="deleteFormId = null">{{ __('notifications::notifications.cancel') }}</button>
                    <button type="button" class="{{ $btnSolidDanger }}" @click="if (deleteFormId) { document.getElementById(deleteFormId).submit(); } deleteFormId = null;">{{ __('notifications::notifications.delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete all confirm modal --}}
    <div x-show="deleteAllForm" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="delete-all-title">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="deleteAllForm" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="deleteAllForm = false"></div>
            <div x-show="deleteAllForm" @keydown.escape.window="deleteAllForm = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative w-full max-w-md transform rounded-xl bg-white dark:bg-gray-800 p-6 text-left shadow-xl">
                <h3 id="delete-all-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.confirm_delete_all_title') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.confirm_delete_all_body') }}</p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" class="{{ $btnSecondary }}" @click="deleteAllForm = false">{{ __('notifications::notifications.cancel') }}</button>
                    <button type="button" class="{{ $btnSolidDanger }}" @click="document.getElementById('delete-all-notifs').submit(); deleteAllForm = false;">{{ __('notifications::notifications.delete_all') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
