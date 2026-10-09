@extends('layouts.app')

@section('template_title')
    {{ __('notifications::notifications.notifications') }}
@endsection

@push('template_linked_css')
@include('notifications::partials.colors')
<style>
    @keyframes pulse-dot-bs { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.4)} }
    .animate-pulse-dot-bs { animation: pulse-dot-bs 2s ease-in-out infinite; }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width: 896px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ __('notifications::notifications.notifications') }}</h1>
        @if(!$showArchived && !$notifications->isEmpty())
            <div class="d-flex gap-2">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check-all me-1"></i>{{ __('notifications::notifications.mark_all_read') }}</button>
                    </form>
                    <form method="POST" action="{{ route('notifications.archive-all') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-archive me-1"></i>{{ __('notifications::notifications.archive_all') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy-all') }}" id="delete-all-notifs" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllModal"><i class="bi bi-trash me-1"></i>{{ __('notifications::notifications.delete_all') }}</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Archive Toggle + Search --}}
    @if($totalCount > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ul class="nav nav-pills nav-sm">
            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="nav-link py-1 px-3 {{ !$showArchived ? 'active' : '' }}"><i class="bi bi-inbox me-1"></i>{{ __('notifications::notifications.inbox') }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('notifications.index', ['archived' => 1]) }}" class="nav-link py-1 px-3 {{ $showArchived ? 'active' : '' }}">
                    <i class="bi bi-archive me-1"></i>{{ __('notifications::notifications.archived') }}
                    @if($archivedCount > 0)
                        <span class="badge bg-secondary ms-1">{{ $archivedCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
        <div class="input-group input-group-sm" style="max-width: 200px;" x-data="{ search: '' }">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="{{ __('notifications::notifications.filter') }}" x-model="search" id="notifSearch">
        </div>
    </div>
    @endif

    @if(config('notifications.flash_messages', false) && session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('notifications::notifications.close') }}"></button>
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                @if($showArchived)
                    <i class="bi bi-archive display-4 text-muted d-block mb-3"></i>
                    <h5>{{ __('notifications::notifications.no_archived') }}</h5>
                    <p class="text-muted mb-0">{{ __('notifications::notifications.no_archived_hint') }}</p>
                @else
                    <i class="bi bi-bell display-4 text-muted d-block mb-3"></i>
                    <h5>{{ __('notifications::notifications.all_caught_up') }}</h5>
                    <p class="text-muted mb-0">{{ __('notifications::notifications.no_notifications') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="list-group">
            @foreach($notifications as $notification)
                @php
                    // Only a known type may reach the CSS variable name below.
                    $nType = in_array($notification->data['type'] ?? 'info', \Jeremykenedy\LaravelNotifications\Support\Colors::TYPES, true)
                        ? $notification->data['type']
                        : 'info';
                    $accent = $notification->read_at ? 'read' : $nType;
                @endphp
                <div class="list-group-item notif-item"
                    style="background-color: var(--notifications-{{ $accent }}-row); border-start: 3px solid var(--notifications-{{ $accent }});"
                    data-search="{{ strtolower(($notification->data['title'] ?? '') . ' ' . ($notification->data['message'] ?? '')) }}">
                    <div class="d-flex align-items-start gap-3">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 position-relative" style="width:36px;height:36px;">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:36px;height:36px;background-color: var(--notifications-{{ $accent }}-tint); color: var(--notifications-{{ $accent }});">
                                @if($notification->read_at)
                                    <i class="bi bi-check-lg text-muted"></i>
                                @else
                                    <i class="bi bi-bell"></i>
                                @endif
                            </span>
                            @unless($notification->read_at)
                                <span class="position-absolute top-0 end-0 rounded-circle border border-white animate-pulse-dot-bs" style="width:10px;height:10px;background-color: var(--notifications-unread);"></span>
                            @endunless
                        </div>

                        {{-- Content --}}
                        <div class="flex-grow-1">
                            @if(isset($notification->data['title']))
                                <h6 class="mb-1">{{ $notification->data['title'] }}</h6>
                            @endif
                            <p class="mb-1 text-secondary">{{ $notification->data['message'] ?? class_basename($notification->type) }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="small">{{ $notification->data['action_text'] ?? 'View' }} &rarr;</a>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-1 flex-shrink-0">
                            @if($showArchived)
                                <form method="POST" action="{{ route('notifications.unarchive', $notification->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="{{ __('notifications::notifications.unarchive') }}" data-bs-toggle="tooltip"><i class="bi bi-arrow-counterclockwise"></i></button>
                                </form>
                            @else
                                @if($notification->read_at)
                                    <form method="POST" action="{{ route('notifications.unread', $notification->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="{{ __('notifications::notifications.mark_unread') }}" data-bs-toggle="tooltip"><i class="bi bi-envelope"></i></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="{{ __('notifications::notifications.mark_read') }}" data-bs-toggle="tooltip"><i class="bi bi-check"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('notifications.archive', $notification->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="{{ __('notifications::notifications.archive') }}" data-bs-toggle="tooltip"><i class="bi bi-archive"></i></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" id="delete-notif-{{ $notification->id }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-confirm-form="delete-notif-{{ $notification->id }}" title="{{ __('notifications::notifications.delete') }}" data-bs-toggle="tooltip"><i class="bi bi-x"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    @endif

    {{-- Delete single modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ __('notifications::notifications.confirm_delete_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('notifications::notifications.close') }}"></button></div>
                <div class="modal-body"><p>{{ __('notifications::notifications.confirm_delete_body') }}</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('notifications::notifications.cancel') }}</button><button type="button" class="btn btn-danger" id="deleteModalConfirm">{{ __('notifications::notifications.delete') }}</button></div>
            </div>
        </div>
    </div>

    {{-- Delete all modal --}}
    <div class="modal fade" id="deleteAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ __('notifications::notifications.confirm_delete_all_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('notifications::notifications.close') }}"></button></div>
                <div class="modal-body"><p>{{ __('notifications::notifications.confirm_delete_all_body') }}</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('notifications::notifications.cancel') }}</button><button type="button" class="btn btn-danger" onclick="document.getElementById('delete-all-notifs').submit();">{{ __('notifications::notifications.delete_all') }}</button></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) { new bootstrap.Tooltip(el); });
        var deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(e) {
                var trigger = e.relatedTarget;
                if (trigger) {
                    var formId = trigger.getAttribute('data-confirm-form');
                    var confirmBtn = deleteModal.querySelector('#deleteModalConfirm');
                    if (confirmBtn && formId) {
                        confirmBtn.onclick = function() { document.getElementById(formId).submit(); };
                    }
                }
            });
        }
        // Client-side filter
        var searchInput = document.getElementById('notifSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var q = this.value.toLowerCase();
                document.querySelectorAll('.notif-item').forEach(function(item) {
                    item.style.display = (!q || item.dataset.search.includes(q)) ? '' : 'none';
                });
            });
        }
    });
    </script>
</div>
@endsection
