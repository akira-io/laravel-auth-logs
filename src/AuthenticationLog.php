<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TypeError;

/**
 * @property int $id
 * @property int $authenticatable_id
 * @property string $authenticatable_type
 * @property Carbon $login_at
 * @property bool $login_successful
 * @property string $ip_address
 * @property string $user_agent
 * @property array $location
 * @property Carbon|null $logout_at
 * @property bool $cleared_by_user
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $authenticatable
 */
final class AuthenticationLog extends Model
{
    public $timestamps = false;

    protected $fillable
        = [
            'login_at',
            'login_successful',
            'ip_address',
            'user_agent',
            'location',
            'logout_at',
            'cleared_by_user',
        ];

    /**
     * @throws TypeError
     */
    public function getConnectionName(): string
    {

        $connection = config('auth-logs.db_connection', parent::getConnectionName());

        if ($connection === null) {
            return type(config('database.default'))->asString();
        }

        return type($connection)->asString();
    }

    /**
     * @throws TypeError
     */
    public function getTable(): string
    {

        return type(config('auth-logs.table_name', parent::getTable()))->asString(); // @codeCoverageIgnoreLine
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function authenticatable(): MorphTo
    {

        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'login_at' => 'datetime',
            'login_successful' => 'boolean',
            'logout_at' => 'datetime',
            'cleared_by_user' => 'boolean',
            'location' => 'array',
        ];
    }
}
