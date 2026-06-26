# Security

Authentication logs are security-sensitive operational data. Treat them as audit data and personal data.

## Stored Data

The default table stores:

- authenticatable model type and id
- IP address
- user-agent
- login timestamp
- login success flag
- logout timestamp
- cleared-by-user flag
- geolocation JSON

IP addresses, user agents, and geolocation can identify users or devices. Protect database access, backups, exports, and logs that include this data.

## Authentication and Authorization

The package records authentication events. It does not provide a UI or API for viewing logs.

If your application exposes logs:

- require authentication
- authorize access per user or role
- avoid exposing logs across tenants
- paginate results
- avoid leaking full user-agent or IP data to unauthorized users

## Notification Boundaries

Built-in notifications use only the `mail` channel. This avoids accidentally sending authentication data to unsupported channels.

For Slack, SMS, database, push, or webhooks, create application-owned listeners and notifications. Review what data leaves the application.

## Geolocation Privacy

The default geolocation endpoint sends IP addresses to an external service. Review privacy requirements before enabling this behavior in production.

Options:

- keep the default endpoint
- switch to an HTTPS-compatible provider that returns compatible JSON
- set `geolocation_api` to `null`
- provide location data from an internal service before log creation
- self-host a geolocation database

## IP and User-Agent Limits

Device recognition is based on exact IP address and user-agent matches from prior successful logs.

Do not treat this as strong device identity:

- IP addresses can change or be shared
- user-agent strings can be spoofed
- VPNs and proxies can hide location
- corporate networks can make many users appear similar

Use MFA, risk scoring, and application-specific controls for high-risk decisions.

## Retention

The package provides a `purge` configuration value but does not enforce retention. The host application must schedule deletion or archival.

Choose retention based on:

- security audit needs
- legal requirements
- privacy policy
- support and incident response workflows

## Exports

CSV, JSON, dashboard, and API exports should be considered sensitive. Avoid placing raw exports in public storage. Apply the same authorization and retention rules as the database table.

## Vulnerability Reporting

Follow the repository security policy in [SECURITY.md](../SECURITY.md). Do not open public issues for vulnerabilities.

**Previous:** [Data Flow](06-data-flow.md) | **Next:** [Advanced Usage](08-advanced-usage.md)
