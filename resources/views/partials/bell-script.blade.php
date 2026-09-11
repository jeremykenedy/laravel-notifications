{{--
    Alpine behaviour shared by every bell partial. Markup and CSS classes live
    in the framework specific partial that includes this file.
--}}
<script>
function notifBell() {
    return {
        count: 0,
        interval: null,
        max: {{ (int) config('notifications.bell.max_count_display', 99) }},
        init() {
            this.fetchCount();
            this.interval = setInterval(() => this.fetchCount(), {{ (int) config('notifications.bell.poll_interval_ms', 30000) }});
            this.listenRealtime();
        },
        get label() {
            return this.count > this.max ? this.max + '+' : this.count;
        },
        fetchCount() {
            fetch('{{ route('notifications.count') }}', {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                },
            })
                .then(response => response.json())
                .then(data => { this.count = data.count || 0; })
                .catch(() => {});
        },
        listenRealtime() {
            if (typeof window.Echo === 'undefined') {
                return;
            }

            window.Echo.private('notifications.{{ auth()->id() }}')
                .listen('.notification.created', event => {
                    this.count = event.count || (this.count + 1);
                });
        },
        destroy() {
            if (this.interval) {
                clearInterval(this.interval);
            }
        },
    };
}
</script>
