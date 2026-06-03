# Auditify — High-Performance, Decoupled Audit Logging for Laravel

<p align="center">
  <img src="art/dashboard_mockup.png" alt="Auditify Dashboard Mockup" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is a modern, high-performance audit logging and real-time threat detection package for Laravel. 

Unlike standard logging libraries, Auditify uses a **decoupled database design** to separate logs into three distinct tables. This optimizes database table indexing, reduces write congestion, and guarantees clean scale organization as your application grows.

---

## Key Features

- **Decoupled Database Architecture**: Splitting logs into Action, Activity, and Security tables.
- **Side-by-Side Model Diffs**: Automatic tracking of database modifications (Create, Update, Delete, Restore) showing before/after attribute arrays.
- **Real-Time Threat Detection**: Triggers alerts for mass deletions, rapid updates, and bulk login failures.
- **XSS Attack Shield**: Global scanner detecting cross-site scripting attempts, immediately blocking requests and saving critical security logs.
- **Flexible Multi-User / Guard Association**: Automatically traces actions from any authenticatable model schema (`User`, `Admin`, `Customer`, etc.) dynamically.
- **Custom Authorization Gateways**: Easily register runtime callbacks to secure access to the dashboard.
- **Console Pruning System**: Automated cleanup commands to delete historical log tables without database bloat.
- **Premium Glassmorphic Dashboard**: Fully-responsive dashboard featuring toggling light/dark themes and real-time Chart.js trend graphs.

---

## 1. Quick Start (3-Minute Setup)

### Step 1: Install the Package
Run this command in your project root:
```bash
composer require arpanihan/auditify
```

### Step 2: Run the Installer
Run the installation command to publish configuration files, copy migrations, and set up your database automatically:
```bash
php artisan auditify:install
```

### Step 3: Add Trait to Your Models
Add the `Auditable` trait to any Eloquent model you want to track:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auditify\Traits\Auditable;

class Product extends Model
{
    use Auditable;
}
```

### Step 4: Access the Dashboard
Navigate to `/auditify` in your browser to view your logs.

---

## 2. Decoupled Log Modules

Auditify separates data tracking into three target models under `Auditify\Models` to prevent database bottlenecks:

### Action Logs (`ActionLog`)
* **Table Name**: `audit_action_logs`
* **Purpose**: Captures database write processes.
* **Captured Attributes**: Action type, model description, side-by-side attributes difference (`old_values` and `new_values` JSON structures), URL, user agent, and originating IP address.

### Activity Logs (`ActivityLog`)
* **Table Name**: `audit_activity_logs`
* **Purpose**: Tracks session states and application user navigation.
* **Captured Attributes**: Auth actions (logins, logouts, failure states), visited route paths, user agent, and IP address.

### Security Logs (`SecurityLog`)
* **Table Name**: `audit_security_logs`
* **Purpose**: Records security violations flagged by the threat engine or XSS firewall.
* **Captured Attributes**: Alert title, threat severity (low, medium, high, critical), description, IP address, and User-Agent.

---

## 3. Step-by-Step Installation (Manual)

If you prefer to perform setup steps manually instead of using the `auditify:install` shortcut:

### Step 1: Publish Configuration
```bash
php artisan vendor:publish --tag="auditify-config"
```

### Step 2: Publish Migrations
```bash
php artisan vendor:publish --tag="auditify-migrations"
```

### Step 3: Run Migrations
```bash
php artisan migrate
```

---

## 4. Basic Usage & Code Examples

### Manual Actions Logging
Write to the action logs at any time using the facade helper:
```php
use Auditify\Facades\Auditify;

Auditify::logAction(
    action: 'APPROVE',
    module: 'Invoice',
    description: 'Invoice #204 approved by billing supervisor',
    oldValues: ['status' => 'pending'],
    newValues: ['status' => 'approved']
);
```

### Manual Session/Activity Logging
Log custom visitor actions inside controller endpoints:
```php
use Auditify\Facades\Auditify;

Auditify::logActivity('Initiated shopping cart checkout process');
```

### Manual Security Logging
Log custom security warnings or threat detections:
```php
use Auditify\Facades\Auditify;

Auditify::logSecurity(
    title: 'Unauthorized API Access',
    description: 'IP tried to query private admin API routes without permissions',
    severity: 'high'
);
```

---

## 5. Advanced Extensibility

### Multi-User and Multi-Guard Tracking
Auditify utilizes polymorphic relationships under the hood. It automatically resolves the currently authenticated user across any guard (e.g. `web`, `admin`, `api`). 

You can also explicitly pass custom authenticatable models to be logged:
```php
use Auditify\Facades\Auditify;

Auditify::logActivity(
    activity: 'Updated system security setting profile',
    url: request()->fullUrl(),
    userId: $adminModelInstance // Custom authenticatable model instance
);
```

Or pass a specific user ID and type structure manually:
```php
Auditify::logActivity(
    activity: 'Updated system security setting profile',
    url: request()->fullUrl(),
    userId: ['id' => 12, 'type' => \App\Models\Admin::class]
);
```

### Bypassing Auditing
If you are running large database seeds, data imports, or system migrations, execute your code inside the `withoutAuditing` closure to bypass action logs and maintain high performance:
```php
use Auditify\Facades\Auditify;

Auditify::withoutAuditing(function () {
    // Run database seeder without generating database log records
    Project::factory()->count(1000)->create();
});
```

Alternatively, you can disable and enable auditing manually:
```php
Auditify::disableAuditing();
// Run your bulk queries...
Auditify::enableAuditing();
```

### XSS Attack Shield Exclusions
If you have input forms that require rich HTML or code blocks (e.g., markdown editors), add route exclusion patterns in your configuration file to prevent the XSS middleware from blocking requests:

```php
'xss_protection' => [
    'enabled' => true,
    'block' => true,
    'exclude_routes' => [
        'admin/rich-text/*',
        'posts/*/edit',
    ],
],
```

### Frontend Event Logging API
Track user interactions, clicks, and page events directly from client-side JavaScript by calling the API event endpoint:

```javascript
fetch('/auditify/api/events', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        event_name: 'Document Download',
        description: 'User downloaded the fiscal_report_2026.pdf'
    })
});
```

### Custom Dashboard Authorization
By default, the Auditify dashboard requires standard web authentication. You can override and write custom authorization closures inside the `boot()` method of `AppServiceProvider`:
```php
use Auditify\Facades\Auditify;

Auditify::auth(function ($request) {
    // Return true if access is authorized
    return $request->user() && $request->user()->hasRole('super-admin');
});
```

---

## 6. Maintenance & Performance

### Log Pruning Console Command
To prevent the audit tables from bloating your database, configure the retention window (default is 90 days) in `config/auditify.php` and schedule the console command inside your console routes file:

```php
// In routes/console.php or app/Console/Kernel.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('auditify:prune --days=90')->daily();
```

You can also run pruning manually on demand:
```bash
php artisan auditify:prune --days=30
```

---

## 7. Configuration Reference

The configuration options table for `config/auditify.php`:

| Option Key | Default | Description |
|---|---|---|
| `route_prefix` | `'auditify'` | The URL path prefix for accessing the log dashboard (e.g. `/auditify`). |
| `theme` | `'dark'` | Visual layout style theme: `'dark'` or `'light'`. |
| `middleware` | `['web']` | Middleware classes applied to dashboard routes. |
| `pagination` | `20` | Number of items shown per page in the log tables. |
| `track_ip` | `true` | Save client IP addresses with logs. |
| `track_user_agent`| `true` | Save client web browsers with logs. |
| `track_url` | `true` | Save request URL paths with logs. |
| `authorization.enabled`| `false` | When true, requires the gate authorization below to open the dashboard. |
| `authorization.gate`| `'view-auditify'`| Standard Laravel gate required to access the dashboard. |
| `track_auth_events`| `true` | Auto-log logins, logouts, and login failures. |
| `track_page_visits`| `true` | Auto-log GET page visits (ignores AJAX, PJAX, and Auditify dashboard routes). |
| `xss_protection.enabled` | `true` | Turn request XSS scanning on or off. |
| `xss_protection.block` | `true` | Return HTTP 403 Forbidden to abort requests when XSS is found. |
| `xss_protection.exclude_routes` | `[]` | Routes to skip during XSS scans (supports wildcard paths like `admin/*`). |
| `pruning.keep_days`| `90` | Default age in days for keeping historical log rows. |

---

## 8. Troubleshooting & FAQs

### Q: The dashboard returns a "403 Forbidden" error page.
* **Reason**: Access to the dashboard is protected and the current user fails the gate or callback checks.
* **Solution**: Check your configuration settings. Make sure your user passes the gate defined under `authorization.gate` or your custom `Auditify::auth()` callback.

### Q: Legitimate HTML input submissions are being blocked by the XSS scanner.
* **Reason**: The payload is flagged by the scanner as an XSS vector.
* **Solution**: Add exclusions to `exclude_routes` in the config file to bypass XSS scanning for specific endpoints.

### Q: Database console migrations or seeds fail or write empty user fields.
* **Reason**: Inside console commands, there is no active HTTP session or logged-in user context.
* **Solution**: Wrap seed routines in `Auditify::withoutAuditing()` to bypass logs entirely, or pass model users explicitly inside console commands.

---

## 9. Best Practices & Performance

1. **Schedule Regular Pruning**: Schedule the `auditify:prune` command daily to keep tables small and responsive.
2. **Optimize Bulk Database Writes**: Always bypass auditing using `withoutAuditing` when running seeders, data imports, or heavy backend cleanup processes.
3. **Index Core Fields**: If your log tables grow to millions of rows, ensure key fields (like `user_id`, `user_type`, and `created_at`) are properly indexed to match dashboard query patterns.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more details.
