{{--
    Notification colour settings.

    Include anywhere you want people to be able to recolour notifications:

        @include('notifications::partials.color-settings')

    The widget brings its own markup and styles so it looks the same on
    Tailwind, Bootstrap 5 and Bootstrap 4, and it posts to the package's own
    settings routes. Saved colours are stored in the settings table and
    overlaid onto the config at boot.
--}}
@php
    $colorKeys = \Jeremykenedy\LaravelNotifications\Support\Colors::keys();
    $current = $colors ?? \Jeremykenedy\LaravelNotifications\Support\Colors::all();
    $shipped = $defaults ?? config('notifications.settings.defaults.colors', []);
    $canStore = $storable ?? app(\Jeremykenedy\LaravelNotifications\Support\Settings::class)->available();
@endphp

@include('notifications::partials.color-settings-styles')

<div
    class="notifications-settings"
    x-data="notificationColorSettings({{ \Illuminate\Support\Js::from(['current' => $current, 'defaults' => $shipped]) }})"
>
    @if(session('success'))
        <p class="notifications-settings-flash" role="status">{{ session('success') }}</p>
    @endif

    @error('colors')
        <p class="notifications-settings-error" role="alert">{{ $message }}</p>
    @enderror

    @unless($canStore)
        <p class="notifications-settings-error" role="alert">
            {{ __('notifications::notifications.settings_table_missing') }}
        </p>
    @endunless

    <form method="POST" action="{{ route('notifications.settings.update') }}" class="notifications-settings-form">
        @csrf
        @method('PUT')

        <div class="notifications-settings-grid">
            @foreach($colorKeys as $key)
                <div class="notifications-settings-field">
                    <label class="notifications-settings-label" for="notifications-color-{{ $key }}">
                        {{ __('notifications::notifications.color_'.$key) }}
                    </label>
                    <p class="notifications-settings-hint">{{ __('notifications::notifications.color_'.$key.'_hint') }}</p>

                    <div class="notifications-settings-controls">
                        <input
                            type="color"
                            id="notifications-color-{{ $key }}"
                            name="colors[{{ $key }}]"
                            class="notifications-settings-swatch"
                            x-model="colors.{{ $key }}"
                            aria-describedby="notifications-color-{{ $key }}-hex"
                        >
                        <input
                            type="text"
                            id="notifications-color-{{ $key }}-hex"
                            class="notifications-settings-hex"
                            x-model="colors.{{ $key }}"
                            spellcheck="false"
                            maxlength="7"
                            aria-label="{{ __('notifications::notifications.color_hex', ['name' => __('notifications::notifications.color_'.$key)]) }}"
                        >
                        <button
                            type="button"
                            class="notifications-settings-reset"
                            x-show="colors.{{ $key }}.toLowerCase() !== defaults.{{ $key }}.toLowerCase()"
                            x-cloak
                            @click="colors.{{ $key }} = defaults.{{ $key }}"
                        >{{ __('notifications::notifications.color_reset') }}</button>
                    </div>

                    @error('colors.'.$key)
                        <p class="notifications-settings-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <section class="notifications-settings-previews" aria-label="{{ __('notifications::notifications.color_preview') }}">
            <h3 class="notifications-settings-subtitle">{{ __('notifications::notifications.color_preview') }}</h3>

            @foreach(\Jeremykenedy\LaravelNotifications\Support\Colors::TYPES as $type)
                <div class="notifications-preview-row" :style="rowStyle('{{ $type }}')">
                    <span class="notifications-preview-icon" :style="iconStyle('{{ $type }}')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    </span>
                    <span class="notifications-preview-body">
                        <strong>{{ __('notifications::notifications.color_'.$type) }}</strong>
                        <span>{{ __('notifications::notifications.color_preview_body') }}</span>
                    </span>
                    <span class="notifications-preview-badge" :style="badgeStyle()">3</span>
                </div>
            @endforeach
        </section>

        <div class="notifications-settings-actions">
            <button type="submit" class="notifications-settings-save" @if(!$canStore) disabled @endif>
                {{ __('notifications::notifications.settings_save') }}
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('notifications.settings.reset') }}" class="notifications-settings-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="notifications-settings-reset-all" @if(!$canStore) disabled @endif>
            {{ __('notifications::notifications.settings_reset_all') }}
        </button>
    </form>
</div>

@include('notifications::partials.color-settings-script')
