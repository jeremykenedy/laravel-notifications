@extends('layouts.app')

@section('template_title')
    {{ __('notifications::notifications.send') }}
@endsection

@php
    $label = 'block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1';
    $control = 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors';
    $btnBase = 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg border transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-gray-900';
    $btnSecondary = $btnBase.' border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-gray-400';
    $btnPrimary = $btnBase.' border-blue-600 bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-400';
@endphp

@section('content')
<div class="container mx-auto max-w-4xl px-4 py-8">
    <nav aria-label="{{ __('notifications::notifications.breadcrumb') }}" class="mb-4">
        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 list-none p-0 m-0">
            <li><a href="{{ route('notifications.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('notifications::notifications.notifications') }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-gray-900 dark:text-gray-100" aria-current="page">{{ __('notifications::notifications.send') }}</li>
        </ol>
    </nav>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <h1 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('notifications::notifications.send') }}</h1>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.total_users', ['count' => $userCount]) }}</span>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div role="alert" class="mb-5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('notifications.send.store') }}" x-data="{ audience: @js(old('audience', 'all')) }">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="title" class="{{ $label }}">{{ __('notifications::notifications.send_field_title') }}</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255" class="{{ $control }}" />
                    </div>

                    <div>
                        <label for="message" class="{{ $label }}">{{ __('notifications::notifications.send_field_body') }}</label>
                        <textarea name="message" id="message" rows="4" required maxlength="1000" class="{{ $control }}">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label for="type" class="{{ $label }}">{{ __('notifications::notifications.send_type') }}</label>
                        <select name="type" id="type" class="{{ $control }}">
                            @foreach(['info', 'success', 'warning', 'danger', 'system'] as $type)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ __('notifications::notifications.type_'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <fieldset>
                        <legend class="{{ $label }}">{{ __('notifications::notifications.send_audience') }}</legend>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="audience" value="all" x-model="audience" class="text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('notifications::notifications.audience_all_label') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="audience" value="role" x-model="audience" class="text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('notifications::notifications.audience_role') }}</span>
                            </label>
                        </div>
                    </fieldset>

                    <div x-show="audience === 'role'" x-cloak x-transition>
                        <label for="role_id" class="{{ $label }}">{{ __('notifications::notifications.send_role') }}</label>
                        <select name="role_id" id="role_id" class="{{ $control }}">
                            <option value="">{{ __('notifications::notifications.send_choose_role') }}</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="action_url" class="{{ $label }}">{{ __('notifications::notifications.send_action_url') }}</label>
                            <input type="url" name="action_url" id="action_url" value="{{ old('action_url') }}" placeholder="https://" class="{{ $control }}" />
                        </div>
                        <div>
                            <label for="action_text" class="{{ $label }}">{{ __('notifications::notifications.send_action_text') }}</label>
                            <input type="text" name="action_text" id="action_text" value="{{ old('action_text', __('notifications::notifications.view')) }}" maxlength="50" class="{{ $control }}" />
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                        <input type="checkbox" name="send_email" id="send_email" value="1" @checked(old('send_email')) class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
                        <div>
                            <label for="send_email" class="text-sm font-medium text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('notifications::notifications.send_email') }}</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('notifications::notifications.send_email_hint') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('notifications.index') }}" class="{{ $btnSecondary }}">{{ __('notifications::notifications.cancel') }}</a>
                        <button type="submit" class="{{ $btnPrimary }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                            {{ __('notifications::notifications.send_submit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
