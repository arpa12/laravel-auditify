# Auditify — Simple, High-Performance Audit Logging for Laravel

<p align="center">
  <img src="art/dashboard_mockup.png" alt="Auditify Dashboard Mockup" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is an easy-to-use, high-performance audit logging and threat detection package for Laravel. 

Instead of dumping all events into one slow table, it splits logs into **three separate tables** to keep your database fast:
1. **Action Logs**: Saves database changes (Create, Update, Delete, Restore).
2. **Activity Logs**: Saves user visits and authentication actions (Logins, Logouts).
3. **Security Logs**: Saves security threats (XSS attacks, mass updates).

---

## 1. Quick Start (3-Minute Setup)

### Step 1: Install the package
```bash
composer require arpanihan/auditify
```

### Step 2: Run the installer
This command copies settings, creates database tables, and sets up everything:
```bash
php artisan auditify:install
```

### Step 3: Add the Trait to your Models
Add the `Auditable` trait to any Eloquent model you want to track changes for:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auditify\Traits\Auditable;

class Product extends Model
{
    use Auditable;
}
```

Now, go to `your-app.test/auditify` to see your live log dashboard!

---

## 2. Decoupled Log Modules

Auditify separates data logs into three dedicated tables under `Auditify\Models` to avoid bottlenecks:

### Action Logs (`ActionLog`)
*   **Table**: `audit_action_logs`
*   **Purpose**: Logs database modifications.
*   **Saved Fields**: Model type, action (CREATE/UPDATE/DELETE/RESTORE), side-by-side attributes difference (`old_values` and `new_values` JSON structures), URL, user agent, IP address, and authenticated user.

### Activity Logs (`ActivityLog`)
*   **Table**: `audit_activity_logs`
*   **Purpose**: Logs user interaction, navigation, and auth events.
*   **Saved Fields**: Auth status events (logins, logouts, login failures), visited pages, page request URLs, user agent, IP address, and user ID.

### Security Logs (`SecurityLog`)
*   **Table**: `audit_security_logs`
*   **Purpose**: Logs security alerts triggered by the XSS firewall or threat engine.
*   **Saved Fields**: Alert title, threat severity (low, medium, high, critical), description, IP address, user agent, read status, and user ID.

---

## 3. Basic & Advanced Usage Examples

### Auto-Auditing Models
Once you add the `Auditable` trait to an Eloquent model, it will automatically track events (`created`, `updated`, `deleted`, `restored`) and log the modifications. It automatically tracks dirty field states (except for the `updated_at` field).

### Manual Logging API
You can write to any of the three log modules at any time using the `Auditify` facade:

#### 1. Action Logs (`Auditify::logAction`)
Use this to log database writes or data changes manually:
```php
use Auditify\Facades\Auditify;

Auditify::logAction(
    action: 'APPROVE',
    module: 'Invoice',
    description: 'Invoice #1042 approved by supervisor',
    oldValues: ['status' => 'pending'],
    newValues: ['status' => 'approved']
);
```

#### 2. Activity Logs (`Auditify::logActivity`)
Use this to track custom page actions or process milestones:
```php
use Auditify\Facades\Auditify;

Auditify::logActivity('Clicked checkout button');
```

#### 3. Security Logs (`Auditify::logSecurity`)
Use this to track custom warnings or violations:
```php
use Auditify\Facades\Auditify;

Auditify::logSecurity(
    title: 'Unauthorized Access Attempt',
    description: 'User tried to load billing page without subscription',
    severity: 'medium'
);
```

### Logging Custom Users or Guards
Auditify automatically resolves the logged-in user. However, you can explicitly pass a user to be associated with the log:

```php
use Auditify\Facades\Auditify;
use App\Models\User;

$user = User::find(5);

// Pass the Eloquent model instance directly
Auditify::logActivity(
    activity: 'Updated profile settings',
    url: request()->fullUrl(),
    userId: $user
);

// Or pass specific User ID and Model types as an array
Auditify::logActivity(
    activity: 'Admin action',
    url: request()->fullUrl(),
    userId: ['id' => 1, 'type' => \App\Models\Admin::class]
);
```

### Bypassing Auditing (Disable Logging Temporarily)
When running database seeders, importing large CSV files, or running console migrations, wrap your code in the `withoutAuditing` closure helper to prevent logs from clogging your database:

```php
use Auditify\Facades\Auditify;
use App\Models\Product;

Auditify::withoutAuditing(function () {
    // Audit logs will NOT be written for these operations
    Product::factory()->count(1000)->create();
});
```

You can also toggle it manually:
```php
Auditify::disableAuditing();

// Perform operations here...

Auditify::enableAuditing();
```

---

## 4. Key Features

### XSS Attack Shield
Auditify automatically scans all incoming request parameters (such as `$_GET` or `$_POST`) and route parameters. If it detects common XSS patterns (like `<script>`, `javascript:`, or SVG events), it:
1. Logs a **critical** security log entry.
2. Returns an HTTP `403 Forbidden` response to block the request.

If you have pages that need HTML inputs (like rich text editors), exclude them in your `config/auditify.php` file:
```php
'xss_protection' => [
    'enabled' => true,
    'block' => true,
    'exclude_routes' => [
        'admin/articles/*',
        'posts/*/edit',
    ],
],
```

### Real-Time Threat Engine
Auditify monitors events and logs high-priority Security entries when rules are broken:
*   **Mass Delete Shield**: Logs a `critical` threat if a user deletes 5 or more records in a single module within 5 minutes.
*   **Bulk Update Shield**: Logs a `high` threat if a user updates 10 or more records in a single module within 5 minutes.
*   **Failed Logins Monitor**: Logs a `high` threat if 3 or more failed login attempts are recorded within 5 minutes.
*   **Sensitive Module Monitor**: Logs a `medium` threat when models listed in config (e.g. `User`, `Role`, `Permission`, `Setting`, `Config`) are modified.
*   **Permission Changes**: Logs a `high` threat whenever a permission, role, or gate mapping is added, modified, or deleted.

### Frontend Event Logging API
Track button clicks or client-side actions by sending a POST request to `/auditify/api/events`.

```javascript
fetch('/auditify/api/events', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        event_name: 'File Download',
        description: 'User downloaded the User_Guide.pdf document'
    })
});
```

### Dashboard Custom Authorization Gate
By default, the Auditify dashboard requires standard web authentication. You can define custom access control in the `boot` method of your `AppServiceProvider.php`:

```php
use Auditify\Facades\Auditify;

public function boot()
{
    // Restrict dashboard to Admins only
    Auditify::auth(function ($request) {
        return $request->user() && $request->user()->is_admin;
    });
}
```

---

## 5. Log Pruning (Database Maintenance)

Log tables grow quickly on busy websites. Prevent database bloat by scheduling a daily pruning command.

To run it manually:
```bash
php artisan auditify:prune --days=90
```

To run it automatically, add the command to your scheduler configuration (`routes/console.php` or `app/Console/Kernel.php`):
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('auditify:prune --days=90')->daily();
```

---

## 6. Configuration Options

The settings file is located at `config/auditify.php`. Here is the default setup:

```php
return [
    'route_prefix' => 'auditify', // URL path to load the dashboard (e.g. /auditify)
    'theme' => 'dark',           // Dashboard look: 'dark' or 'light'
    'middleware' => ['web'],     // Middlewares applied to the dashboard
    'pagination' => 20,          // Log entries shown per page

    'track_ip' => true,          // Log client IP address
    'track_user_agent' => true,  // Log client browser
    'track_url' => true,         // Log current request URL

    'authorization' => [
        'enabled' => false,      // Require gate checks for dashboard
        'gate' => 'view-auditify',
    ],

    'track_auth_events' => true, // Log login, logout, and failed logins automatically
    'track_page_visits' => true, // Log page visits automatically

    'xss_protection' => [
        'enabled' => true,
        'block' => true,         // Block request with 403 status if XSS is found
        'exclude_routes' => [],  // Paths to exclude from scanning
    ],

    'pruning' => [
        'keep_days' => 90,       // Keep logs for 90 days by default
    ],
];
```

---

## License

The MIT License (MIT). See [License File](LICENSE.md) for details.
