@extends('layouts.app')

@section('template_title', 'Send Notification')

@section('content')
<div class="container py-4" style="max-width:896px;">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('notifications::notifications.send') }}</h5>
            <span class="text-muted small">{{ $userCount }} total users</span>
        </div>
        <div class="card-body" x-data="{ audience: 'all', sendEmail: false }">
            <form method="POST" action="{{ route('notifications.send.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('notifications::notifications.send_field_title') }}</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255" />
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('notifications::notifications.send_field_body') }}</label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" required maxlength="1000">{{ old('message') }}</textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('notifications::notifications.send_type') }}</label>
                    <select name="type" class="form-select">
                        <option value="info">{{ __('notifications::notifications.type_info') }}</option>
                        <option value="success">{{ __('notifications::notifications.type_success') }}</option>
                        <option value="warning">{{ __('notifications::notifications.type_warning') }}</option>
                        <option value="danger">{{ __('notifications::notifications.type_danger') }}</option>
                        <option value="system">{{ __('notifications::notifications.type_system') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('notifications::notifications.send_audience') }}</label>
                    <div class="d-flex gap-3">
                        <div class="form-check"><input class="form-check-input" type="radio" name="audience" value="all" x-model="audience" checked><label class="form-check-label">{{ __('notifications::notifications.audience_all_label') }}</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="audience" value="role" x-model="audience"><label class="form-check-label">{{ __('notifications::notifications.audience_role') }}</label></div>
                    </div>
                </div>
                <div x-show="audience === 'role'" x-cloak class="mb-3">
                    <label class="form-label">{{ __('notifications::notifications.send_role') }}</label>
                    <select name="role_id" class="form-select">
                        <option value="">{{ __('notifications::notifications.send_choose_role') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><label class="form-label">{{ __('notifications::notifications.send_action_url') }}</label><input type="url" name="action_url" class="form-control" value="{{ old('action_url') }}" /></div>
                    <div class="col-md-6"><label class="form-label">{{ __('notifications::notifications.send_action_text') }}</label><input type="text" name="action_text" class="form-control" value="{{ old('action_text', __('notifications::notifications.view')) }}" /></div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="send_email" value="1" id="sendEmail" x-model="sendEmail">
                    <label class="form-check-label" for="sendEmail">{{ __('notifications::notifications.send_email') }}</label>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">{{ __('notifications::notifications.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('notifications::notifications.send_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
