<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Jeremykenedy\LaravelNotifications\Services\NotificationService;

class SendNotificationController extends Controller
{
    public function create()
    {
        $roleModel = config('notifications.role_model', 'App\\Models\\Role');
        $userModel = config('notifications.user_model', 'App\\Models\\User');

        $roles = class_exists($roleModel) ? $roleModel::all() : collect();
        $userCount = class_exists($userModel) ? $userModel::count() : 0;

        return view('notifications::send', compact('roles', 'userCount'));
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'message'     => 'required|string|max:1000',
            'audience'    => 'required|in:all,role',
            'role_id'     => 'required_if:audience,role|nullable|integer',
            'type'        => 'nullable|in:info,success,warning,danger,system',
            'action_url'  => 'nullable|url|max:255',
            'action_text' => 'nullable|string|max:50',
            'send_email'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $service = app(NotificationService::class);
        $audience = $request->input('audience');
        $type = $request->input('type', 'info');

        if ($audience === 'role') {
            $count = $service->sendToRole(
                roleSlug: $this->getRoleSlug($request->input('role_id')),
                title: $request->input('title'),
                message: $request->input('message'),
                type: $type,
                actionUrl: $request->input('action_url'),
                actionText: $request->input('action_text'),
            );
        } else {
            $count = $service->sendToAll(
                title: $request->input('title'),
                message: $request->input('message'),
                type: $type,
                actionUrl: $request->input('action_url'),
                actionText: $request->input('action_text'),
                sendEmail: (bool) $request->input('send_email', false),
            );
        }

        $roleName = $audience === 'role'
            ? $this->getRoleName($request->input('role_id'))
            : 'all';

        return redirect()->route('notifications.send.create')
            ->with('success', "Notification sent to {$count} user(s) ({$roleName}).");
    }

    protected function getRoleSlug(int $roleId): string
    {
        $roleModel = config('notifications.role_model', 'App\\Models\\Role');

        return $roleModel::find($roleId)?->slug ?? 'user';
    }

    protected function getRoleName(int $roleId): string
    {
        $roleModel = config('notifications.role_model', 'App\\Models\\Role');

        return $roleModel::find($roleId)?->name ?? 'Unknown';
    }
}
