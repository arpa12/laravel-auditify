# Auditify — Simple, High-Performance Audit Logging for Laravel

<p align="center">
  <img src="art/dashboard_mockup.png" alt="Auditify Dashboard Mockup" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is an audit logging and threat detection package for Laravel. 

Instead of saving everything into one slow table, it splits logs into **three separate tables** to keep your database fast:
1. **Action Logs**: Saves database changes (Create, Update, Delete, Restore).
2. **Activity Logs**: Saves user visits and authentication actions (Logins, Logouts).
3. **Security Logs**: Saves security threats (XSS attacks, mass updates).

---

## 1. Quick Start

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

Now, go to `your-app.test/auditify` to see the live dashboard!

---

## 2. Basic Usage Examples

### Manual Logging
You can manually log actions, activities, or security alerts using the `Auditify` facade:

```php
use Auditify\Facades\Auditify;

// 1. Log an action (like database writes)
Auditify::logAction(
    action: 'APPROVE',
    module: 'Invoice',
    description: 'Invoice #105 approved by Supervisor',
    oldValues: ['status' => 'pending'],
    newValues: ['status' => 'approved']
);

// 2. Log general activity (like button clicks)
Auditify::logActivity('User clicked checkout button');

// 3. Log a security event
Auditify::logSecurity(
    title: 'Access Blocked',
    description: 'User tried to open admin settings without permission',
    severity: 'high'
);
```

### Disable Logging Temporarily
If you are running large database seeders or importing data, disable logging so it doesn't slow down:

```php
use Auditify\Facades\Auditify;
use App\Models\Product;

Auditify::withoutAuditing(function () {
    Product::factory()->count(1000)->create();
});
```

---

## 3. Features & Configuration

### XSS Attack Protection
Auditify automatically scans request parameters for XSS scripts. If found, it blocks the request (`403 Forbidden`) and logs a threat.

If you have pages that need HTML inputs (like text editors), exclude them in `config/auditify.php`:
```php
'xss_protection' => [
    'enabled' => true,
    'block' => true,
    'exclude_routes' => [
        'blog/posts/*', // Exclude blog post editor routes
    ],
],
```

### Dashboard Access Protection
By default, anyone can open `/auditify`. To restrict access, register an authentication rule in your `AppServiceProvider.php`:

```php
use Auditify\Facades\Auditify;

public function boot()
{
    Auditify::auth(function ($request) {
        return $request->user() && $request->user()->is_admin;
    });
}
```

### Log Pruning (Database Maintenance)
Logs can grow quickly. The `auditify:prune` command deletes logs older than 90 days to keep your database fast:
```bash
php artisan auditify:prune --days=90
```

Add this command to your scheduler in `routes/console.php` to run it automatically every day:
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('auditify:prune --days=90')->daily();
```

---

## 4. Configuration Options

Here is the default file published at `config/auditify.php`:

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
