<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Concerns;

use Akira\LaravelAuthLogs\AuthenticationLog;

trait AuthLogs
{
    /**
     * Get the authentications for the user.
     */
    public function authenticationLogs()
    {

        return $this->morphMany(AuthenticationLog::class, 'authenticatable')
            ->latest('login_at');
    }

    /**
     * Get the latest authentication for the user.
     */
    public function latestAuthentication()
    {

        return $this->morphOne(AuthenticationLog::class, 'authenticatable')
            ->latestOfMany('login_at');
    }

    /**
     * Get the  notification via .
     */
    public function notifyAuthenticationLogVia(): array
    {

        return config('auth-logs.notification_via', ['mail']);
    }

    /**
     * Get the  last login at.
     */
    public function lastLoginAt()
    {

        return $this->authentications()
            ->first()?->login_at;
    }

    /**
     * Get the  last successful login at.
     */
    public function lastSuccessfulLoginAt()
    {

        return $this->authentications()
            ->whereLoginSuccessful(true)
            ->first()?->login_at;
    }

    /**
     * Get the  last login ip.
     */
    public function lastLoginIp()
    {

        return $this->authentications()
            ->first()
            ?->ip_address;
    }

    /**
     * Get the  last successful login ip.
     */
    public function lastSuccessfulLoginIp()
    {

        return $this->authentications()->whereLoginSuccessful(true)
            ->first()
            ?->ip_address;
    }

    /**
     * Get the  previous login at.
     */
    public function previousLoginAt()
    {

        return $this->authentications()
            ->skip(1)
            ->first()
            ?->login_at;
    }

    /**
     * Get the  previous login ip.
     */
    public function previousLoginIp()
    {

        return $this->authentications()
            ->skip(1)
            ->first()
            ?->ip_address;
    }

    /**
     *  Register logout.
     */
    public function registerLogout(): int|bool
    {

        return $this->latestAuthentication()
            ->update(['logout_at' => now(), 'cleared_by_user' => true]);
    }
}
