<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs;

use Akira\LaravelAuthLogs\Commands\AuthLogsInstallCommand;
use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Akira\LaravelAuthLogs\Listeners\LoginListener;
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Akira\LaravelAuthLogs\Listeners\OtherDeviceLogoutListener;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LaravelAuthLogsServiceProvider extends PackageServiceProvider
{
    
    /**
     * Configure the package's behavior, such as its name, configuration, views,
     * migrations, and custom commands. Additionally, register event listeners
     * for handling authentication-related events.
     *
     * @throws BindingResolutionException If the Dispatcher cannot be resolved.
     */
    public function configurePackage(Package $package): void
    {
        
        $package
            ->name('laravel-auth-logs')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_auth_logs_table')
            ->hasCommand(AuthLogsInstallCommand::class);
        
        $event = $this->app->make(Dispatcher::class);
        
        $this->subscribeToLoginEvent($event);
        
        $this->subscribeToFailedLoginEvent($event);
        
        $this->subscribeLogoutEvent($event);
        
        $this->subscribeToLogoutOtherDeviceEvent($event);
    }
    
    
    /**
     * Subscribe to the login event to log successful authentication attempts.
     *
     * @param  Dispatcher  $event  The event dispatcher instance.
     */
    private function subscribeToLoginEvent(Dispatcher $event): void
    {
        
        $event->listen(
            events  : config('auth-logs.events.login', Login::class),
            listener: config('auth-logs.listeners.login', LoginListener::class),
        );
    }
    
    
    /**
     * Subscribe to the failed login event to log authentication failures.
     *
     * @param  Dispatcher  $event  The event dispatcher instance.
     */
    private function subscribeToFailedLoginEvent(Dispatcher $event): void
    {
        
        $event->listen(
            events  : config('auth-logs.events.failed', Failed::class),
            listener: config('auth-logs.listeners.failed', FailedLoginListener::class),
        );
    }
    
    
    /**
     * Subscribe to the logout event to log user logout activities.
     *
     * @param  Dispatcher  $event  The event dispatcher instance.
     */
    private function subscribeLogoutEvent(Dispatcher $event): void
    {
        
        $event->listen(
            events  : config('auth-logs.events.logout', Logout::class),
            listener: config('auth-logs.listeners.logout', LogoutListener::class),
        );
    }
    
    
    /**
     * Subscribe to the "logout other devices" event to log when a user logs out
     * from other active sessions on different devices.
     *
     * @param  Dispatcher  $event  The event dispatcher instance.
     */
    private function subscribeToLogoutOtherDeviceEvent(Dispatcher $event): void
    {
        
        $event->listen(
            events  : config('auth-logs.events.logout-other-devices', OtherDeviceLogout::class),
            listener: config('auth-logs.listeners.other_device_logout', OtherDeviceLogoutListener::class),
        );
    }
    
}
