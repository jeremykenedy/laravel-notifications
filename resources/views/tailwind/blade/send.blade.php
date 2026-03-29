@extends('layouts.app')

@section('template_title', 'Send Notification')

@section('content')
<div class="container mx-auto max-w-4xl px-4 py-8">
    <x-ui::breadcrumbs :items="[['label' => 'Notifications', 'url' => route('notifications.index')], ['label' => 'Send']]" />

    <x-ui::card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Send Notification</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $userCount }} total users</span>
            </div>
        </x-slot>

        @include('partials.errors')

        <form method="POST" action="{{ route('notifications.send.store') }}" x-data="{ audience: 'all', sendEmail: false }">
            @csrf

            <div class="space-y-5">
                <x-ui::form-group label="Title" for="title" required>
                    <x-ui::input type="text" name="title" id="title" :value="old('title')" placeholder="e.g. System Maintenance Scheduled" required maxlength="255" />
                </x-ui::form-group>

                <x-ui::form-group label="Message" for="message" required>
                    <x-ui::textarea name="message" id="message" rows="4" placeholder="The notification message..." required maxlength="1000">{{ old('message') }}</x-ui::textarea>
                </x-ui::form-group>

                <x-ui::form-group label="Type" for="type">
                    <x-ui::select name="type" id="type">
                        <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info (blue)</option>
                        <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success (green)</option>
                        <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning (yellow)</option>
                        <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger (red)</option>
                        <option value="system" {{ old('type') === 'system' ? 'selected' : '' }}>System (purple)</option>
                    </x-ui::select>
                </x-ui::form-group>

                <x-ui::form-group label="Audience" for="audience" required>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="all" x-model="audience" class="text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">All Users</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="audience" value="role" x-model="audience" class="text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Specific Role</span>
                        </label>
                    </div>
                </x-ui::form-group>

                <div x-show="audience === 'role'" x-cloak x-transition>
                    <x-ui::form-group label="Select Role" for="role_id">
                        <x-ui::select name="role_id" id="role_id">
                            <option value="">Choose a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }} (Level {{ $role->level }})</option>
                            @endforeach
                        </x-ui::select>
                    </x-ui::form-group>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui::form-group label="Action URL" for="action_url" hint="Optional link">
                        <x-ui::input type="url" name="action_url" id="action_url" :value="old('action_url')" placeholder="https://..." />
                    </x-ui::form-group>
                    <x-ui::form-group label="Button Text" for="action_text">
                        <x-ui::input type="text" name="action_text" id="action_text" :value="old('action_text', 'View')" placeholder="View" maxlength="50" />
                    </x-ui::form-group>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                    <x-ui::checkbox name="send_email" id="send_email" value="1" x-model="sendEmail" />
                    <div>
                        <label for="send_email" class="text-sm font-medium text-gray-900 dark:text-gray-100 cursor-pointer">Also send via email</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Emails every user in the selected audience</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-ui::button href="{{ route('notifications.index') }}" variant="secondary" outline>Cancel</x-ui::button>
                    <x-ui::button type="submit" variant="primary" icon="bell">Send Notification</x-ui::button>
                </div>
            </div>
        </form>
    </x-ui::card>
</div>
@endsection
