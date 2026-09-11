{{--
    Notification bell with an unread count badge.
    Include in a navbar: @include('notifications::partials.bell')
--}}
@auth
<div class="relative" x-data="notifBell()" x-init="init()" x-on:destroy="destroy()">
    <a href="{{ route('notifications.index') }}" class="relative inline-flex p-2 rounded-md text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="{{ __('notifications::notifications.notifications') }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
        @if(config('notifications.bell.show_count', true))
            <span x-show="count > 0" x-cloak x-text="label" class="absolute -top-1 -right-1 flex items-center justify-center h-[18px] min-w-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full" aria-live="polite"></span>
        @endif
    </a>
</div>
@include('notifications::partials.bell-script')
@endauth
