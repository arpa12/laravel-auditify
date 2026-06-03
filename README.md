# Auditify — High-Performance, Decoupled Audit Logging for Laravel

<p align="center">
  <img src="art/dashboard_mockup.png" alt="Auditify Dashboard Mockup" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Total Downloads](https://img.shields.io/packagist/dt/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is a modern, high-performance audit logging and real-time threat detection package for Laravel. Unlike standard logging libraries, Auditify uses a **decoupled database design** to separate actions into three distinct modules. This optimizes table indexing, reduces write congestion, and guarantees clean scale organization.

---

## Key Features

- **Decoupled Database Architecture**: Splitting logs into Action, Activity, and Security tables.
- **Side-by-Side Model Diffs**: Automatic tracking of database modifications (Create, Update, Delete, Restore) showing before/after attribute arrays.
- **Real-Time Threat Detection**: Triggers alerts for mass deletions, rapid updates, and bulk login failures.
- **XSS Attack Shield**: Global scanner detecting cross-site scripting attempts, immediately blocking requests and saving critical security logs.
- **Asynchronous Mail Alerts**: Integrates queue listeners to dispatch security alerts to administrators without delaying requests.
- **Flexible Multi-User / Guard Association**: Automatically traces actions from any authenticatable model schema (`User`, `Admin`, `Customer`, etc.) dynamically.
- **Custom Authorization Gateways**: Easily register runtime callbacks to secure access to the dashboard.
- **Console Pruning System**: Automated cleanup commands to delete historical log tables without database bloat.
- **Premium Glassmorphic Dashboard**: Fully-responsive dashboard featuring toggling light/dark themes and real-time Chart.js trend graphs.

---

## 1. Installation

You can install the package via Composer:

```bash
composer require arpanihan/auditify
```

After installing the package, publish the migration files:

```bash
php artisan vendor:publish --tag="auditify-migrations"
```

Run the database migrations:

```bash
php artisan migrate
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="auditify-config"
```

The config file will be available at `config/auditify.php`.

---

## 2. Decoupled Log Modules

Auditify separates data tracking into three target models under `Auditify\Models`:

### Action Logs (`ActionLog`)
* **Purpose**: Captures database write processes.
* **Captured Attributes**: Action type, model description, side-by-side attributes difference (`old_values` and `new_values` JSON structures), URL, user agent, and originating IP address.

### Activity Logs (`ActivityLog`)
* **Purpose**: Tracks session states and application user navigation.
* **Captured Attributes**: Auth actions (logins, logouts, failure states), visited route paths, user agent, and IP address.

### Security Logs (`SecurityLog`)
* **Purpose**: Records security violations flagged by the threat engine or XSS firewall.
* **Captured Attributes**: Alert title, threat severity (low, medium, high, critical), description, IP address, and User-Agent.

---

## 3. Basic Usage

### Auto-Auditing Models

To track database changes on an Eloquent model, simply add the `Auditable` trait:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auditify\Traits\Auditable;

class Project extends Model
{
    use Auditable;
}
```

This will automatically hook into standard model events (`created`, `updated`, `deleted`, `restored`) and log the modifications.

### Manual Actions Logging

You can write to the action logs at any time using the facade helper:

```php
use Auditify;

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
use Auditify;

Auditify::logActivity('Initiated shopping cart checkout process');
```

---

## 4. Advanced Extensibility

### Multi-User and Multi-Guard Tracking

Auditify utilizes polymorphic relationships under the hood. It automatically resolves the currently authenticated user across any guard (e.g. `web`, `admin`, `api`). 

You can also explicitly pass custom authenticatable models to be logged:

```php
use Auditify;

Auditify::logActivity(
    activity: 'Updated system security setting profile',
    url: request()->fullUrl(),
    user: $adminModelInstance // Custom authenticatable model
);
```

### Bypassing Auditing

If you are running large database seeds, data imports, or system migrations, execute your code inside the `withoutAuditing` closure to bypass action logs and maintain high performance:

```php
use Auditify;

Auditify::withoutAuditing(function () {
    // Run database seeder without generating database log records
    Project::factory()->count(1000)->create();
});
```

### Custom Dashboard Authorization

By default, the Auditify dashboard requires standard gate validation. You can override and write custom authorization closures inside `boot()` method of `AppServiceProvider`:

```php
use Auditify;

Auditify::auth(function ($request) {
    // Return true if access is authorized
    return $request->user() && $request->user()->hasRole('super-admin');
});
```

---

## 5. Maintenance & Performance

### Log Pruning Console Command

To prevent the audit tables from bloating your database, configure the retention window (default is 90 days) in `config/auditify.php` and schedule the console command inside your console routes file:

```php
// In routes/console.php or app/Console/Kernel.php
$schedule->command('auditify:prune --days=90')->daily();
```

You can also run pruning manually on demand:

```bash
php artisan auditify:prune --days=30
```

### Queued Alert Mail Notifications

Whenever high or critical severity threat security logs are created, Auditify dispatches alert emails asynchronously to developers to avoid introducing request latency.

Ensure your background queue runner is active in your production servers:

```bash
php artisan queue:work
```

---

## 6. Previews & Pre-designed UI

Auditify ships with a modern, fully-integrated admin panel dashboard accessible by default at `/auditify`. 

The dashboard provides visual metrics of actions vs activity levels, side-by-side database diff comparisons, interactive CSV exports, and dynamic light/dark theme toggles.

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more details.
