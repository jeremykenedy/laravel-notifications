<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Jeremykenedy\LaravelNotifications\Http\Requests\UpdateNotificationColorsRequest;
use Jeremykenedy\LaravelNotifications\Support\Colors;
use Jeremykenedy\LaravelNotifications\Support\Settings;

class NotificationSettingsController extends Controller
{
    public function __construct(
        protected Settings $settings,
    ) {
    }

    public function edit()
    {
        return view('notifications::settings', [
            'colors'   => Colors::all(),
            'defaults' => $this->settings->defaults(),
            'storable' => $this->settings->available(),
        ]);
    }

    public function update(UpdateNotificationColorsRequest $request): RedirectResponse
    {
        if ($missing = $this->tableMissing()) {
            return $missing->withInput();
        }

        $this->settings->save($request->colors());

        return $this->done('settings_saved');
    }

    public function reset(): RedirectResponse
    {
        if ($missing = $this->tableMissing()) {
            return $missing;
        }

        $this->settings->reset();

        return $this->done('settings_reset');
    }

    protected function tableMissing(): ?RedirectResponse
    {
        if ($this->settings->available()) {
            return null;
        }

        return back()->withErrors(['colors' => __('notifications::notifications.settings_table_missing')]);
    }

    protected function done(string $message): RedirectResponse
    {
        return redirect()
            ->route('notifications.settings.edit')
            ->with('success', __('notifications::notifications.'.$message));
    }
}
