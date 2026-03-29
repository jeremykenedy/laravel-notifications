@extends('layouts.app')

@section('template_title', 'Send Notification')

@section('content')
<div class="container py-4" style="max-width:896px;">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Send Notification</h5>
            <span class="text-muted small">{{ $userCount }} total users</span>
        </div>
        <div class="card-body" x-data="{ audience: 'all', sendEmail: false }">
            <form method="POST" action="{{ route('notifications.send.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255" />
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" required maxlength="1000">{{ old('message') }}</textarea>
                    @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="info">Info</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="danger">Danger</option>
                        <option value="system">System</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Audience</label>
                    <div class="d-flex gap-3">
                        <div class="form-check"><input class="form-check-input" type="radio" name="audience" value="all" x-model="audience" checked><label class="form-check-label">All Users</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="audience" value="role" x-model="audience"><label class="form-check-label">Specific Role</label></div>
                    </div>
                </div>
                <div x-show="audience === 'role'" x-cloak class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select">
                        <option value="">Choose...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><label class="form-label">Action URL</label><input type="url" name="action_url" class="form-control" value="{{ old('action_url') }}" /></div>
                    <div class="col-md-6"><label class="form-label">Button Text</label><input type="text" name="action_text" class="form-control" value="{{ old('action_text', 'View') }}" /></div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="send_email" value="1" id="sendEmail" x-model="sendEmail">
                    <label class="form-check-label" for="sendEmail">Also send via email</label>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
