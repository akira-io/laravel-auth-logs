# Security Policy

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
|---------|--------------------|
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

We take the security of Laravel Auth Logs seriously. If you discover a security vulnerability, please follow these
steps:

### DO NOT

- **Do not** open a public GitHub issue
- **Do not** disclose the vulnerability publicly until it has been addressed
- **Do not** exploit the vulnerability beyond what is necessary to demonstrate the issue

### DO

1. **Email** the security vulnerability to [kidiatoliny@akira-io.com](mailto:kidiatoliny@akira-io.com)
2. **Include** the following information:
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if you have one)
- Your contact information

### What to Expect

- **Acknowledgment**: You will receive an acknowledgment of your report within 48 hours
- **Investigation**: We will investigate the issue and determine its severity
- **Updates**: You will receive regular updates on the progress (at least every 5 business days)
- **Resolution**: We aim to release a fix within 30 days for critical issues
- **Credit**: We will credit you in the security advisory (unless you prefer to remain anonymous)

## Security Considerations

### Authentication Logs

This package stores sensitive authentication data including:

- IP addresses
- User agents
- Login timestamps
- Geolocation data

### Best Practices

#### Data Retention

Configure appropriate log retention periods to comply with privacy regulations:

```php
// config/auth-logs.php
'purge' => 90, // Days to retain logs
```

Schedule automatic purging:

```bash
php artisan schedule:run
```

#### Database Security

1. **Use encrypted connections** to your database
2. **Restrict database access** to authorized services only
3. **Consider encryption at rest** for sensitive data
4. **Regular backups** with secure storage

#### Privacy Compliance

If you operate in regions with privacy laws (GDPR, CCPA, etc.):

1. **Inform users** about authentication logging in your privacy policy
2. **Allow users to request** their authentication logs
3. **Implement data deletion** upon user request
4. **Anonymize or pseudonymize** IP addresses if required

Example anonymization:

```php
// In a custom listener
$log->update([
    'ip_address' => $this->anonymizeIp($log->ip_address),
]);

private function anonymizeIp(string $ip): string
{
    // For IPv4: 192.168.1.1 -> 192.168.0.0
    $parts = explode('.', $ip);
    if (count($parts) === 4) {
        return $parts[0] . '.' . $parts[1] . '.0.0';
    }
    return $ip;
}
```

#### Notification Security

1. **Use HTTPS** for all email links
2. **Don't include passwords** or sensitive credentials in notifications
3. **Rate limit** notification sending to prevent abuse
4. **Validate email addresses** before sending

#### API Security

If you expose authentication logs via API:

1. **Require authentication** for all endpoints
2. **Implement rate limiting** to prevent enumeration
3. **Use authorization** to ensure users can only access their own logs
4. **Sanitize output** to prevent information leakage

Example:

```php
public function index(Request $request)
{
    $this->authorize('viewAuthLogs', $request->user());
    
    return $request->user()
        ->authenticationLogs()
        ->paginate(20);
}
```

#### Geolocation API

The default geolocation API (`ip-api.com`) makes external HTTP requests:

1. **Consider privacy implications** of sharing IP addresses with third parties
2. **Use HTTPS** for API requests if available
3. **Consider self-hosted solutions** like MaxMind GeoIP2
4. **Implement caching** to reduce external calls

#### Environment Variables

Never commit sensitive configuration to version control:

```env
# .env
AUTH_LOGS_DB_CONNECTION=secure_connection
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
```

Add to `.gitignore`:

```
.env
.env.backup
.env.production
```

## Known Security Considerations

### IP Address Spoofing

IP addresses can be spoofed. Do not rely solely on IP addresses for security decisions. Use additional factors like:

- Device fingerprinting
- Multi-factor authentication
- Behavioral analysis

### User Agent Manipulation

User agents are client-provided and can be modified. Consider them informational rather than authoritative.

### Geolocation Accuracy

Geolocation data is approximate and may not represent the user's actual location, especially with:

- VPNs
- Proxies
- Corporate networks
- Mobile networks

### SQL Injection

The package uses Eloquent ORM which provides protection against SQL injection. Always use parameterized queries:

```php
// ✅ Safe
$logs = AuthenticationLog::where('ip_address', $ip)->get();

// ❌ Unsafe - never do this
$logs = DB::select("SELECT * FROM authentication_logs WHERE ip_address = '$ip'");
```

### Cross-Site Scripting (XSS)

When displaying authentication logs in web interfaces, always escape output:

```php
// In Blade templates
{{ $log->user_agent }} // Safe - automatically escaped
{!! $log->user_agent !!} // Unsafe - unescaped
```

## Secure Coding Guidelines

### Input Validation

Always validate and sanitize input:

```php
$request->validate([
    'ip_address' => 'required|ip',
    'user_agent' => 'required|string|max:500',
]);
```

### Output Encoding

Encode data appropriately for the output context (HTML, JSON, etc.)

### Error Handling

Don't expose sensitive information in error messages:

```php
try {
    // code
} catch (\Exception $e) {
    // ❌ Don't do this
    return response()->json(['error' => $e->getMessage()]);
    
    // ✅ Do this
    \Log::error('Authentication log error', ['exception' => $e]);
    return response()->json(['error' => 'An error occurred'], 500);
}
```

## Security Updates

Subscribe to security updates:

- Watch the [GitHub repository](https://github.com/akira-io/laravel-auth-logs)
- Follow release notes in `CHANGELOG.md`
- Check [Packagist](https://packagist.org/packages/akira/laravel-auth-logs)

## Disclosure Policy

When a security vulnerability is addressed:

1. A security advisory will be published
2. A new version will be released with the fix
3. The CHANGELOG will document the security fix
4. Credit will be given to the reporter (if desired)

## Contact

For security concerns, contact: [kidiatoliny@gmail.com](mailto:kidiatoliny@gmail.com)

For general issues, use: [GitHub Issues](https://github.com/kidiatoliny/laravel-auth-logs/issues)

## Acknowledgments

We appreciate the security researchers who help keep Laravel Auth Logs safe. Thank you for responsible disclosure.
