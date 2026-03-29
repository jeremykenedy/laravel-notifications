@extends('layouts.app')

@section('template_title')
    {{ __('notifications::notifications.notifications') }}
@endsection

@push('template_linked_css')
<style>
    @keyframes pulse-dot-bs4 { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.4)} }
    .animate-pulse-dot-bs4 { animation: pulse-dot-bs4 2s ease-in-out infinite; }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width: 896px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>{{ __('notifications::notifications.notifications') }}</h2>
        @if(!$showArchived && !$notifications->isEmpty())
            <div>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa fa-check-double mr-1"></i>{{ __('notifications::notifications.mark_all_read') }}</button>
                    </form>
                    <form method="POST" action="{{ route('notifications.archive-all') }}" class="d-inline ml-1">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa fa-archive mr-1"></i>Archive All</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy-all') }}" id="delete-all-notifs" class="d-inline ml-1">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteAllModal"><i class="fa fa-trash mr-1"></i>Delete All</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Archive Toggle + Search --}}
    @if($totalCount > 0)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="{{ route('notifications.index') }}" class="nav-link py-1 px-3 {{ !$showArchived ? 'active' : '' }}"><i class="fa fa-inbox mr-1"></i>Inbox</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('notifications.index', ['archived' => 1]) }}" class="nav-link py-1 px-3 {{ $showArchived ? 'active' : '' }}">
                    <i class="fa fa-archive mr-1"></i>Archived
                    @if($archivedCount > 0)
                        <span class="badge badge-secondary ml-1">{{ $archivedCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
        <div class="input-group input-group-sm" style="max-width: 200px;">
            <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
            <input type="text" class="form-control" placeholder="Filter..." id="notifSearch">
        </div>
    </div>
    @endif

    @if(config('notifications.flash_messages', false) && session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                @if($showArchived)
                    <i class="fa fa-archive fa-3x text-muted d-block mb-3"></i>
                    <h5>No archived notifications</h5>
                    <p class="text-muted mb-0">Archived notifications will appear here.</p>
                @else
                    <i class="fa fa-bell fa-3x text-muted d-block mb-3"></i>
                    <h5>All caught up</h5>
                    <p class="text-muted mb-0">{{ __('notifications::notifications.no_notifications') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="list-group">
            @foreach($notifications as $notification)
                <div class="list-group-item notif-item {{ $notification->read_at ? '' : 'list-group-item-light border-left border-primary' }}" style="{{ $notification->read_at ? '' : 'border-left-width: 3px !important;' }}" data-search="{{ strtolower(($notification->data['title'] ?? '') . ' ' . ($notification->data['message'] ?? '')) }}">
                    <div class="d-flex justify-content-between align-items-start">
                        {{-- Icon --}}
                        <div class="mr-3 position-relative" style="width:36px;height:36px;flex-shrink:0;">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $notification->read_at ? 'bg-light' : 'bg-primary-light' }}" style="width:36px;height:36px;{{ $notification->read_at ? '' : 'background:rgba(0,123,255,.1);' }}">
                                @if($notification->read_at)
                                    <i class="fa fa-check text-muted"></i>
                                @else
                                    <i class="fa fa-bell text-primary"></i>
                                @endif
                            </span>
                            @unless($notification->read_at)
                                <span class="position-absolute rounded-circle bg-success border border-white animate-pulse-dot-bs4" style="width:10px;height:10px;top:-2px;right:-2px;"></span>
                            @endunless
                        </div>

                        {{-- Content --}}
                        <div class="flex-grow-1 mr-3">
                            @if(isset($notification->data['title']))
                                <h6 class="mb-1">{{ $notification->data['title'] }}</h6>
                            @endif
                            <p class="mb-1 text-secondary">{{ $notification->data['message'] ?? class_basename($notification->type) }}</p>
                            <div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="small ml-2">{{ $notification->data['action_text'] ?? 'View' }} &rarr;</a>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex-shrink-0">
                            @if($showArchived)
                                <form method="POST" action="{{ route('notifications.unarchive', $notification->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Restore" data-toggle="tooltip"><i class="fa fa-undo"></i></button>
                                </form>
                            @else
                                @if($notification->read_at)
                                    <form method="POST" action="{{ route('notifications.unread', $notification->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Mark as unread" data-toggle="tooltip"><i class="fa fa-envelope"></i></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="{{ __('notifications::notifications.mark_read') }}" data-toggle="tooltip"><i class="fa fa-check"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('notifications.archive', $notification->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Archive" data-toggle="tooltip"><i class="fa fa-archive"></i></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" id="delete-notif-{{ $notification->id }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteModal" data-form-id="delete-notif-{{ $notification->id }}" title="{{ __('notifications::notifications.delete') }}" data-toggle="tooltip"><i class="fa fa-times"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-3">{{ $notifications->links() }}</div>
        @endif
    @endif

    {{-- Delete single modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Delete Notification</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body"><p>Are you sure you want to permanently delete this notification?</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="deleteModalConfirm">Delete</button></div>
            </div>
        </div>
    </div>

    {{-- Delete all modal --}}
    <div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Delete All Notifications</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body"><p>Are you sure you want to delete all notifications? This cannot be undone.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" onclick="document.getElementById('delete-all-notifs').submit();">Delete All</button></div>
            </div>
        </div>
    </div>

    <script>
    $(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('#deleteModal').on('show.bs.modal', function(e) {
            var trigger = $(e.relatedTarget);
            var formId = trigger.data('form-id');
            if (formId) {
                $('#deleteModalConfirm').off('click').on('click', function() {
                    document.getElementById(formId).submit();
                });
            }
        });
        // Client-side filter
        $('#notifSearch').on('input', function() {
            var q = this.value.toLowerCase();
            $('.notif-item').each(function() {
                $(this).toggle(!q || $(this).data('search').indexOf(q) !== -1);
            });
        });
    });
    </script>
</div>
@endsection
