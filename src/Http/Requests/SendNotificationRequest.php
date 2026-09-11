<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    /**
     * Access is already gated by the middleware on the send routes.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'message'     => ['required', 'string', 'max:1000'],
            'audience'    => ['required', 'in:all,role'],
            'role_id'     => ['required_if:audience,role', 'nullable', 'integer'],
            'type'        => ['nullable', 'in:info,success,warning,danger,system'],
            'action_url'  => ['nullable', 'url', 'max:255'],
            'action_text' => ['nullable', 'string', 'max:50'],
            'send_email'  => ['nullable', 'boolean'],
        ];
    }

    public function audience(): string
    {
        return (string) $this->input('audience');
    }

    public function roleId(): ?int
    {
        $roleId = $this->input('role_id');

        return $roleId === null || $roleId === '' ? null : (int) $roleId;
    }
}
