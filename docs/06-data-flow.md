# Data Flow

This document describes runtime flows from Laravel authentication events to database records and notifications.

## Successful Login

```mermaid
flowchart TD
    A["Laravel dispatches Login"] --> B["LoginListener"]
    B --> C["Device::isKnownFor(user, ip, user-agent)"]
    B --> D["CreateAuthenticationLog::for(user, true)"]
    D --> E["authentication_logs row"]
    C --> F{"Known device?"}
    E --> G{"Notification enabled and user is not new?"}
    F --> G
    G -->|yes, unknown device| H["SendNotification::make(... NewDevice ...)"]
    H --> I["AuthLogsNotification queued mail"]
    G -->|no| J["No notification"]
```

Inputs:

- event user
- current request IP
- current request user-agent
- current `request()->location`
- `auth-logs.templates.new_device`

Outputs:

- a successful authentication log
- optionally a queued mail notification

## Failed Login

```mermaid
flowchart TD
    A["Laravel dispatches Failed"] --> B["FailedLoginListener"]
    B --> C{"Event has user?"}
    C -->|no| D["Return without log"]
    C -->|yes| E["CreateAuthenticationLog::for(user, false)"]
    E --> F["authentication_logs row"]
    F --> G{"Failed login notification enabled?"}
    G -->|yes| H["SendNotification::make(... FailedLogin ...)"]
    H --> I["AuthLogsNotification queued mail"]
    G -->|no| J["No notification"]
```

Inputs:

- failed auth event
- resolved user, if Laravel provides one
- current request context
- `auth-logs.templates.failed_login`

Outputs:

- a failed authentication log when a user exists
- optionally a queued mail notification

## Logout

```mermaid
flowchart TD
    A["Laravel dispatches Logout"] --> B["LogoutListener"]
    B --> C{"User has registerLogout()?"}
    C -->|no| D["Return"]
    C -->|yes| E["user->registerLogout()"]
    E --> F["Update latest auth log with logout_at and cleared_by_user"]
```

Logout updates the latest authentication log. It does not create a separate logout record.

## Other Device Logout

```mermaid
flowchart TD
    A["Laravel dispatches OtherDeviceLogout"] --> B["OtherDeviceLogoutListener"]
    B --> C["Default listener has no behavior"]
```

This subscription exists so applications can replace the listener in config.

## Notification Rendering

```mermaid
flowchart TD
    A["Queued AuthLogsNotification"] --> B["via(notifiable)"]
    B --> C{"Every channel is mail?"}
    C -->|no| D["RuntimeException"]
    C -->|yes| E["toMail(notifiable)"]
    E --> F{"Template instance or class-string?"}
    F -->|instance| G["Call template->toMail()"]
    F -->|class-string| H["Resolve template from container using log data"]
    H --> I["InteractsWithLogs builds loginAt, ipAddress, location, userAgent"]
    I --> G
```

If a template class-string is used, the notification needs an `AuthenticationLog` so it can resolve constructor data when the queued job runs.

## Geolocation

```mermaid
flowchart TD
    A["Template needs location"] --> B["Use stored log location if city or country exists"]
    B --> C{"Stored location found?"}
    C -->|yes| D["Format City, Country"]
    C -->|no| E["GetLocation::make(ip)"]
    E --> F{"Lookup result empty?"}
    F -->|yes| G["Unknown"]
    F -->|no| H["Persist location to log"]
    H --> D
```

Geolocation is best-effort. It can be disabled with `auth-logs.geolocation_api = null`.

## Retention Flow

The package does not delete records on its own.

```mermaid
flowchart TD
    A["Application scheduler"] --> B["Read config('auth-logs.purge')"]
    B --> C["Delete or archive old AuthenticationLog rows"]
```

The host application should implement retention according to its privacy and compliance requirements.

**Previous:** [Architecture](05-architecture.md) | **Next:** [Security](07-security.md)
