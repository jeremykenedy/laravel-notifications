@extends(config('notifications.settings.blade_extended', 'layouts.app'))

@section('template_title')
    {{ __('notifications::notifications.settings_title') }}
@endsection

@section('content')
<div class="container py-4" style="max-width: 896px;">
    <nav aria-label="{{ __('notifications::notifications.breadcrumb') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('notifications.index') }}">{{ __('notifications::notifications.notifications') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('notifications::notifications.settings_title') }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h1 class="h5 mb-1">{{ __('notifications::notifications.settings_title') }}</h1>
            <p class="text-body-secondary mb-0 small">{{ __('notifications::notifications.settings_intro') }}</p>
        </div>
        <div class="card-body">
            @include('notifications::partials.color-settings')
        </div>
    </div>
</div>
@endsection
