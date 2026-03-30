<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

class NotificationApiController extends Controller
{
    public function __construct(
        protected NotificationService $service,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', config('notifications.per_page', 20));

        return response()->json($this->service->getAll($request->user(), $perPage));
    }

    public function unread(Request $request): JsonResponse
    {
        return response()->json($this->service->getUnread($request->user()));
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json(['count' => $this->service->unreadCount($request->user())]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->service->markAsRead($request->user(), $id);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->service->markAllAsRead($request->user());

        return response()->json(['message' => "{$count} marked as read."]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);

        return response()->json(['message' => 'Deleted.']);
    }

    public function markAsUnread(Request $request, string $id): JsonResponse
    {
        $this->service->markAsUnread($request->user(), $id);

        return response()->json(['message' => 'Marked as unread.']);
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        $this->service->archive($request->user(), $id);

        return response()->json(['message' => 'Archived.']);
    }

    public function unarchive(Request $request, string $id): JsonResponse
    {
        $this->service->unarchive($request->user(), $id);

        return response()->json(['message' => 'Unarchived.']);
    }

    public function archiveAll(Request $request): JsonResponse
    {
        $count = $this->service->archiveAll($request->user());

        return response()->json(['message' => "{$count} archived."]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $count = $this->service->deleteAll($request->user());

        return response()->json(['message' => "{$count} deleted."]);
    }
}
