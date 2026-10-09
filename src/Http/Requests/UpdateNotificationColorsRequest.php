<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Jeremykenedy\LaravelNotifications\Support\Colors;

class UpdateNotificationColorsRequest extends FormRequest
{
    /**
     * Access is already gated by the middleware on the settings routes.
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
        $rules = [];

        foreach (Colors::keys() as $key) {
            $rules['colors.'.$key] = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        foreach (Colors::keys() as $key) {
            $messages['colors.'.$key.'.regex'] = __('notifications::notifications.color_invalid');
            $messages['colors.'.$key.'.required'] = __('notifications::notifications.color_required');
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function colors(): array
    {
        /** @var array<string, string> $colors */
        $colors = $this->validated()['colors'] ?? [];

        return $colors;
    }
}
