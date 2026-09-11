<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelNotifications\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Exercises the package against an application whose users are keyed by UUID
 * rather than an auto incrementing integer.
 */
class UuidUser extends Authenticatable
{
    use HasUuids;
    use Notifiable;

    protected $table = 'uuid_users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];
}
