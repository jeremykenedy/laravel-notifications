@extends(config('notifications.settings.blade_extended', 'layouts.app'))

@section('template_title')
    {{ __('notifications::notifications.settings_title') }}
@endsection

@section('content')
<div class="container mx-auto max-w-4xl px-4 py-8">
    <nav aria-label="{{ __('notifications::notifications.breadcrumb') }}" class="mb-4">
        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 list-none p-0 m-0">
            <li><a href="{{ route('notifications.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('notifications::notifications.notifications') }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-gray-900 dark:text-gray-100" aria-current="page">{{ __('notifications::notifications.settings_title') }}</li>
        </ol>
    </nav>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <h1 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.settings_title') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.settings_intro') }}</p>
        </div>
        <div class="p-6">
            @include('notifications::partials.color-settings')
        </div>
    </div>
</div>
@endsection
