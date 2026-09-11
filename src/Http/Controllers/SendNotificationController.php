<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Controllers;

use Illuminate\Routing\Controller;
use Jeremykenedy\LaravelNotifications\Http\Requests\SendNotificationRequest;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

class SendNotificationController extends Controller
{
    public function __construct(
        protected NotificationService $service,
    ) {
    }

    public function create()
    {
        $roleModel = config('notifications.role_model', 'App\\Models\\Role');
        $userModel = config('notifications.user_model', 'App\\Models\\User');

        $roles = class_exists($roleModel) ? $roleModel::all() : collect();
        $userCount = class_exists($userModel) ? $userModel::count() : 0;

        return view('notifications::send', compact('roles', 'userCount'));
    }

    public function send(SendNotificationRequest $request)
    {
        $data = $request->validated();
        $role = $request->audience() === 'role' ? $this->findRole($request->roleId()) : null;

        if ($request->audience() === 'role' && $role === null) {
            return back()
                ->withInput()
                ->withErrors(['role_id' => __('notifications::notifications.role_not_found')]);
        }

        $count = $role !== null
            ? $this->service->sendToRole(
                roleSlug: (string) $role->slug,
                title: $data['title'],
                message: $data['message'],
                type: $data['type'] ?? 'info',
                actionUrl: $data['action_url'] ?? null,
                actionText: $data['action_text'] ?? null,
            )
            : $this->service->sendToAll(
                title: $data['title'],
                message: $data['message'],
                type: $data['type'] ?? 'info',
                actionUrl: $data['action_url'] ?? null,
                actionText: $data['action_text'] ?? null,
                sendEmail: (bool) ($data['send_email'] ?? false),
            );

        return redirect()->route('notifications.send.create')->with(
            'success',
            __('notifications::notifications.sent_to', [
                'count'    => $count,
                'audience' => $role !== null ? $role->name : __('notifications::notifications.audience_all'),
            ]),
        );
    }

    protected function findRole(?int $roleId): ?object
    {
        $roleModel = config('notifications.role_model', 'App\\Models\\Role');

        if ($roleId === null || !class_exists($roleModel)) {
            return null;
        }

        return $roleModel::find($roleId);
    }
}
