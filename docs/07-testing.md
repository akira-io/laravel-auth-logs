# Testing

Guidelines and examples for testing authentication logging in your Laravel application.

## Testing Setup

### Base Test Case

Create a base test case with common setup:

```php
<?php

namespace Tests\Feature;

use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AuthLogTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure User model has the AuthLogs trait
        $this->assertTrue(
            in_array(AuthLogs::class, class_uses_recursive(\App\Models\User::class))
        );
    }
}
```

## Testing Authentication Logging

### Test Successful Login Logging

```php
public function test_successful_login_is_logged()
{
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertDatabaseHas('authentication_logs', [
        'authenticatable_id' => $user->id,
        'authenticatable_type' => User::class,
        'login_successful' => true,
    ]);
}
```

### Test Failed Login Logging

```php
public function test_failed_login_is_logged()
{
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertDatabaseHas('authentication_logs', [
        'authenticatable_id' => $user->id,
        'authenticatable_type' => User::class,
        'login_successful' => false,
    ]);
}
```

### Test Logout Registration

```php
public function test_logout_is_registered()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $log = $user->authenticationLogs()->create([
        'login_at' => now(),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
        'login_successful' => true,
        'location' => [],
    ]);

    $user->registerLogout();

    $this->assertNotNull($log->fresh()->logout_at);
    $this->assertTrue($log->fresh()->cleared_by_user);
}
```

## Testing Notifications

### Test New Device Notification

```php
use Illuminate\Support\Facades\Notification;
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;

public function test_new_device_notification_is_sent()
{
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subDays(1), // Not a new user
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    Notification::assertSentTo($user, AuthLogsNotification::class);
}
```

### Test New Device Notification Not Sent for New Users

```php
public function test_new_device_notification_not_sent_for_new_users()
{
    Notification::fake();

    $user = User::factory()->create(); // Just created

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    Notification::assertNotSentTo($user, AuthLogsNotification::class);
}
```

### Test Failed Login Notification

```php
public function test_failed_login_notification_is_sent()
{
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    Notification::assertSentTo($user, AuthLogsNotification::class);
}
```

### Test Notification Channels

```php
public function test_notification_uses_correct_channels()
{
    Notification::fake();

    $user = User::factory()->create([
        'created_at' => now()->subDays(1),
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    Notification::assertSentTo(
        $user,
        AuthLogsNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels);
        }
    );
}
```

## Testing Helper Methods

### Test Last Login Methods

```php
public function test_last_login_at_returns_latest_login()
{
    $user = User::factory()->create();

    $firstLogin = $user->authenticationLogs()->create([
        'login_at' => now()->subHours(2),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test',
        'login_successful' => true,
        'location' => [],
    ]);

    $secondLogin = $user->authenticationLogs()->create([
        'login_at' => now()->subHour(),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test',
        'login_successful' => true,
        'location' => [],
    ]);

    $this->assertTrue($user->lastLoginAt()->equalTo($secondLogin->login_at));
}
```

### Test Previous Login Methods

```php
public function test_previous_login_at_returns_second_latest()
{
    $user = User::factory()->create();

    $firstLogin = $user->authenticationLogs()->create([
        'login_at' => now()->subHours(2),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Test',
        'login_successful' => true,
        'location' => [],
    ]);

    $secondLogin = $user->authenticationLogs()->create([
        'login_at' => now()->subHour(),
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Test',
        'login_successful' => true,
        'location' => [],
    ]);

    $this->assertTrue($user->previousLoginAt()->equalTo($firstLogin->login_at));
    $this->assertEquals('192.168.1.1', $user->previousLoginIp());
}
```

### Test isNew Method

```php
public function test_is_new_returns_true_for_recent_users()
{
    $user = User::factory()->create();
    
    $this->assertTrue($user->isNew());
}

public function test_is_new_returns_false_for_old_users()
{
    $user = User::factory()->create([
        'created_at' => now()->subMinutes(2),
    ]);
    
    $this->assertFalse($user->isNew());
}
```

## Testing Actions

### Test CreateAuthenticationLog

```php
use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;

public function test_create_authentication_log_action()
{
    $user = User::factory()->create();

    $log = CreateAuthenticationLog::for($user, isSuccessFull: true);

    $this->assertInstanceOf(\Akira\LaravelAuthLogs\AuthenticationLog::class, $log);
    $this->assertTrue($log->login_successful);
    $this->assertEquals($user->id, $log->authenticatable_id);
}
```

### Test Device Recognition

```php
use Akira\LaravelAuthLogs\Actions\Device;

public function test_device_is_known_for_returning_user()
{
    $user = User::factory()->create();

    $user->authenticationLogs()->create([
        'login_at' => now()->subDay(),
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'login_successful' => true,
        'location' => [],
    ]);

    $knownDevice = Device::isKnownFor($user, '192.168.1.1', 'Mozilla/5.0');

    $this->assertNotNull($knownDevice);
}

public function test_device_is_not_known_for_new_combination()
{
    $user = User::factory()->create();

    $knownDevice = Device::isKnownFor($user, '192.168.1.1', 'Mozilla/5.0');

    $this->assertNull($knownDevice);
}
```

### Test GetLocation

```php
use Akira\LaravelAuthLogs\Actions\GetLocation;
use Illuminate\Support\Facades\Http;

public function test_get_location_returns_data()
{
    Http::fake([
        'ip-api.com/*' => Http::response([
            'status' => 'success',
            'country' => 'United States',
            'city' => 'New York',
            'lat' => 40.7128,
            'lon' => -74.0060,
            'timezone' => 'America/New_York',
        ]),
    ]);

    $location = GetLocation::make('8.8.8.8');

    $this->assertEquals('United States', $location['country']);
    $this->assertEquals('New York', $location['city']);
}
```

## Testing Custom Templates

### Test Custom Template Structure

```php
use App\Notifications\AuthLogs\CustomNewDevice;
use Illuminate\Notifications\Messages\MailMessage;

public function test_custom_template_implements_to_mail()
{
    $template = new CustomNewDevice(
        loginAt: '2025-01-01 12:00:00',
        ipAddress: '192.168.1.1',
        location: 'New York, USA',
        userAgent: 'Mozilla/5.0'
    );

    $user = User::factory()->create();
    $message = $template->toMail($user);

    $this->assertInstanceOf(MailMessage::class, $message);
}
```

## Mocking Geolocation

### Mock IP Geolocation in Tests

```php
protected function setUp(): void
{
    parent::setUp();

    // Mock location data
    $this->app->instance('request', tap(request(), function ($request) {
        $request->location = [
            'city' => 'Test City',
            'country' => 'Test Country',
            'lat' => '0.0',
            'lon' => '0.0',
            'timezone' => 'UTC',
        ];
    }));
}
```

## Database Assertions

### Assert Log Count

```php
public function test_multiple_logins_create_multiple_logs()
{
    $user = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $this->post('/logout');
    }

    $this->assertCount(3, $user->authenticationLogs);
}
```

### Assert Log Data

```php
public function test_log_contains_correct_data()
{
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $log = $user->authenticationLogs()->first();

    $this->assertNotNull($log->login_at);
    $this->assertNotNull($log->ip_address);
    $this->assertNotNull($log->user_agent);
    $this->assertTrue($log->login_successful);
}
```

## Integration Tests

### Test Complete Authentication Flow

```php
public function test_complete_authentication_flow()
{
    Notification::fake();

    // Register
    $user = User::factory()->create();

    // First login - should create log but no notification (new user)
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertCount(1, $user->authenticationLogs);
    Notification::assertNotSentTo($user, AuthLogsNotification::class);

    // Logout
    $this->post('/logout');

    // Second login after 2 minutes - should send notification
    $this->travel(2)->minutes();
    
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertCount(2, $user->fresh()->authenticationLogs);
    Notification::assertSentTo($user, AuthLogsNotification::class);
}
```

**Previous:** [API Reference](06-api-reference.md) | **Next:** [Troubleshooting](08-troubleshooting.md)
