<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    // Kept as a property rather than casts() so the model works on every
    // Laravel version this package declares support for.
    protected $casts = ['value' => 'array'];

    public function getTable(): string
    {
        return config('notifications.settings.table', 'notification_settings');
    }
}
