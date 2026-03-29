{{--
    Notification Bell with unread count badge.
    Include in your navbar: @include('notifications::partials.bell')

    Features:
    - Shows bell icon with red badge for unread count
    - Polls /notifications/count every 30 seconds
    - Listens for real-time WebSocket updates via Echo
    - Badge shows 99+ when over 99
    - Badge hidden when count is 0
--}}
@auth
<div class="relative" x-data="notifBell()" x-init="init()">
    <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-md text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC] hover:bg-gray-100 dark:hover:bg-[#1b1b18] transition-colors" title="Notifications">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
        <span x-show="count > 0" x-cloak x-text="count > 99 ? '99+' : count" class="absolute -top-1 -right-1 flex items-center justify-center h-[18px] min-w-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full"></span>
    </a>
</div>
<script>
function notifBell() {
    return {
        count: 0, interval: null,
        init() {
            this.fetchCount();
            this.interval = setInterval(() => this.fetchCount(), {{ config('notifications.bell.poll_interval_ms', 30000) }});
            this.listenRealtime();
        },
        fetchCount() {
            fetch('{{ route("notifications.count") }}', { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content } })
                .then(r => r.json()).then(d => { this.count = d.count || 0; }).catch(() => {});
        },
        listenRealtime() {
            if (typeof window.Echo === 'undefined') return;
            window.Echo.private('notifications.{{ auth()->id() }}')
                .listen('.notification.created', (e) => { this.count = e.count || (this.count + 1); });
        },
        destroy() { if (this.interval) clearInterval(this.interval); }
    };
}
</script>
@endauth
