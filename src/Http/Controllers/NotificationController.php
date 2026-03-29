<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $service,
    ) {
    }

    public function count(Request $request)
    {
        return response()->json([
            'count' => $this->service->unreadCount($request->user()),
        ]);
    }

    public function index(Request $request)
    {
        $perPage = (int) config('notifications.per_page', 20);
        $showArchived = $request->boolean('archived');

        $notifications = $showArchived
            ? $this->service->getArchived($request->user(), $perPage)
            : $this->service->getActive($request->user(), $perPage);

        $unreadCount = $this->service->unreadCount($request->user());
        $archivedCount = $this->service->archivedCount($request->user());
        $totalCount = $request->user()->notifications()->count();

        return view('notifications::index', compact('notifications', 'unreadCount', 'archivedCount', 'showArchived', 'totalCount'));
    }

    public function markAsRead(Request $request, string $id)
    {
        $this->service->markAsRead($request->user(), $id);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAsUnread(Request $request, string $id)
    {
        $this->service->markAsUnread($request->user(), $id);

        return redirect()->back()->with('success', 'Notification marked as unread.');
    }

    public function markAllAsRead(Request $request)
    {
        $count = $this->service->markAllAsRead($request->user());

        return redirect()->back()->with('success', "{$count} notifications marked as read.");
    }

    public function archive(Request $request, string $id)
    {
        $this->service->archive($request->user(), $id);

        return redirect()->back()->with('success', 'Notification archived.');
    }

    public function unarchive(Request $request, string $id)
    {
        $this->service->unarchive($request->user(), $id);

        return redirect()->back()->with('success', 'Notification restored.');
    }

    public function archiveAll(Request $request)
    {
        $count = $this->service->archiveAll($request->user());

        return redirect()->back()->with('success', "{$count} notifications archived.");
    }

    public function destroy(Request $request, string $id)
    {
        $this->service->delete($request->user(), $id);

        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function destroyAll(Request $request)
    {
        $this->service->deleteAll($request->user());

        return redirect()->back()->with('success', 'All notifications deleted.');
    }
}
