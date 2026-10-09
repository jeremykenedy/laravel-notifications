{{--
    Emits the notification colours as CSS custom properties.

    Views cannot put a user chosen colour into a Tailwind or Bootstrap class, so
    every colour and the tints derived from it are published here and the views
    reference var(--notifications-*). Include this once per page that renders
    notifications, or let the index and settings views pull it in for you.
--}}
@once
@php($notificationCssVariables = \Jeremykenedy\LaravelNotifications\Support\Colors::cssVariables())
<style>
:root {
    {!! $notificationCssVariables !!}
}
</style>
@endonce
