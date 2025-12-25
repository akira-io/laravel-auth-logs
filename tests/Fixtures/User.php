<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Tests\Fixtures;

use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

final class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;
    use AuthLogs;
    use Notifiable;

    protected $guarded = [];
}
