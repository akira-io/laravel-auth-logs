<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs;

use Illuminate\Database\Eloquent\Model;

final class AuthenticationLog extends Model
{
    protected $guarded = [];

    /**
     * Get the database connection for the model.
     */
    public function getConnectionName(): string
    {
        return config('auth-logs.db_connection') ?? parent::getConnectionName();
    }

    public function getTable(): string
    {
        return config('auth-logs.table_name') ?? parent::getTable();
    }
}
