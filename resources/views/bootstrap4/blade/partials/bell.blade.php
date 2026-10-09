{{--
    Notification bell with an unread count badge.
    Include in a navbar: @include('notifications::partials.bell')
--}}
@auth
@include('notifications::partials.colors')
<div class="position-relative d-inline-block" x-data="notifBell()" x-init="init()" x-on:destroy="destroy()">
    <a href="{{ route('notifications.index') }}" class="position-relative d-inline-flex p-2 text-muted" aria-label="{{ __('notifications::notifications.notifications') }}">
        <i class="fa fa-bell" aria-hidden="true"></i>
        @if(config('notifications.bell.show_count', true))
            <span x-show="count > 0" x-cloak x-text="label" class="badge badge-pill position-absolute" style="top: 0; right: 0; background-color: var(--notifications-badge); color: var(--notifications-badge-on);" aria-live="polite"></span>
        @endif
    </a>
</div>
@include('notifications::partials.bell-script')
@endauth
