# Auditify — High-Performance, Decoupled Audit Logging for Laravel

<p align="center">
  <img src="art/laravel_auditify_banner_v2.png" alt="Laravel Auditify Banner" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Total Downloads](https://img.shields.io/packagist/dt/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is an easy-to-use, high-performance audit logging and threat detection package for Laravel. 

Unlike standard logging libraries, Auditify uses a **decoupled database design** to separate logs into three distinct tables. This optimizes database table indexing, reduces write congestion, and guarantees clean scale organization as your application grows.

---

## Table of Contents
* [Requirements](#-requirements)
* [Installation](#-installation)
* [Configuration](#-configuration)
* [Features](#-features)
  * [Dashboard](#-dashboard)
  * [Decoupled Log Modules](#-decoupled-log-modules)
  * [Real-Time Threat Engine](#-real-time-threat-engine)
  * [XSS Attack Shield](#-xss-attack-shield)
  * [Frontend Event Logging API](#-frontend-event-logging-api)
  * [Custom Dashboard Authorization Gate](#-custom-dashboard-authorization-gate)
* [Artisan Commands](#%EF%B8%8F-artisan-commands)
* [Routes Reference](#-routes-reference)
* [Testing](#-testing)
* [Support](#-support)
* [Author](#-author)
* [License](#-license)

---

## 🖥️ Requirements

| Laravel | PHP |
|---|---|
| 13.x | 8.3 – 8.4 |
| 11.x, 12.x | 8.2 – 8.4 |
| 10.x | 8.2 – 8.3 |

---

## 📦 Installation

### 1. Install via Composer
Run this command in your project root:
```bash
composer require arpanihan/auditify
```

### 2. Run the Installer
Run the installation command to publish configuration files, copy migrations, and set up your database automatically:
```bash
php artisan auditify:install
```

### 3. Add the Trait to Your Models
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

---

## ⚙️ Configuration

After publishing, customize your settings in `config/auditify.php`:

```php
return [
    // Base URL route prefix: https://your-domain.com/auditify
    'route_prefix' => 'auditify',

    // Dashboard visual layout theme: 'dark' or 'light'
    'theme' => 'dark',

    // Middlewares applied to the dashboard routes
    'middleware' => [
        'web',
    ],

    // Log entries shown per page
    'pagination' => 20,

    // Track details
    'track_ip' => true,
    'track_user_agent' => true,
    'track_url' => true,

    // Authorization configuration
    'authorization' => [
        'enabled' => false,
        'gate' => 'view-auditify',
    ],

    // Automatic tracking configurations
    'track_auth_events' => true, // Login, Logout, Failed logins
    'track_page_visits' => true, // Page visits

    // Firewall scanning
    'xss_protection' => [
        'enabled' => true,
        'block' => true,         // Abort requests with HTTP 403 when script is found
        'exclude_routes' => [
            // 'admin/rich-text/*',
        ],
    ],

    // Pruning configuration
    'pruning' => [
        'keep_days' => 90,       // Default age in days for keeping historical log rows
    ],
];
```

---

## 🚀 Features

### 📊 Dashboard
URL: `/auditify`

The main glassmorphic dashboard aggregates action logs, page visits, and threat alerts into an interactive screen:
* **Metrics counters** — total action logs, activity logs, and security logs with unread indicators.
* **Log summaries** — breakdowns of operations (Create, Update, Delete) and authentication events (Login, Logout).
* **7-day trend graph** — comparative daily counts of Actions vs Activities plotted on Chart.js.
* **Top active users & top modified modules** — lists of most active user IDs and frequently changed models.
* **Live recent logs** — lists of the most recent visitor actions and security alerts.

### 🗄️ Decoupled Log Modules
Auditify separates data logging into three target models under `Auditify\Models` to avoid write bottlenecks:

#### Action Logs (`ActionLog`)
*   **Table Name**: `audit_action_logs`
*   **Purpose**: Logs database modifications.
*   **Captured Attributes**: Action type, model description, side-by-side attributes difference (`old_values` and `new_values` JSON structures), URL, user agent, IP address, and authenticated user.

#### Activity Logs (`ActivityLog`)
*   **Table Name**: `audit_activity_logs`
*   **Purpose**: Logs user interaction, navigation, and auth events.
*   **Captured Attributes**: Auth status events (logins, logouts, login failures), visited pages, page request URLs, user agent, IP address, and user ID.

#### Security Logs (`SecurityLog`)
*   **Table Name**: `audit_security_logs`
*   **Purpose**: Logs security alerts triggered by the XSS firewall or threat engine.
*   **Captured Attributes**: Alert title, threat severity (low, medium, high, critical), description, IP address, user agent, read status, and user ID.

### 📈 Real-Time Threat Engine
Auditify automatically monitors activity logs and logs high-priority Security entries when rules are broken:
* **Mass Delete Shield**: Fires a `critical` security log if a user deletes 5 or more records (default) in a single model within 5 minutes.
* **Bulk Update Shield**: Fires a `high` security log if a user updates 10 or more records (default) in a single model within 5 minutes.
* **Failed Logins monitor**: Tracks failed logins. Fires a `high` security log if 3 or more failed login attempts are recorded within 5 minutes.
* **Sensitive Module monitor**: Triggers a `medium` security log whenever models listed in `sensitive_modules` (e.g. `User`, `Role`, `Permission`, `Setting`, `Config`) are modified.
* **Permission Changes**: Triggers a `high` security log whenever a permission, role, or gate mapping is added, modified, or deleted.

### 🛡️ XSS Attack Shield
Auditify has built-in XSS protection. It automatically scans all incoming request parameters (such as `$_GET` or `$_POST`) and route variables. 

If it detects common XSS patterns (like `<script>`, `javascript:`, or SVG events), it:
1. Logs a **critical** security log entry.
2. Returns an HTTP `403 Forbidden` response to block the request.

If you have pages that require rich text input (e.g. admin markdown or HTML editors), exclude them in your `config/auditify.php` file:
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

### 🔌 Frontend Event Logging API
Track button clicks, mouse behaviors, or client-side Javascript actions by sending a POST request to `/auditify/api/events`.

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

### 🔐 Custom Dashboard Authorization Gate
By default, the Auditify dashboard uses your default web authentication and guards. To define custom access control, register an authorization callback in the `boot` method of your `AppServiceProvider.php`:

```php
use Auditify\Facades\Auditify;

public function boot()
{
    // Restrict dashboard to Super Admins only
    Auditify::auth(function ($request) {
        return $request->user() && $request->user()->hasRole('super-admin');
    });
}
```

---

## 🛠️ Artisan Commands

| Command | Description |
|---|---|
| `auditify:install` | Runs migrations and publishes configuration files automatically. |
| `auditify:prune {--days=}` | Deletes old audit log records older than N days (defaults to `keep_days` config value). |

### Automated Database Pruning Setup
To keep database tables small and performant, automate pruning by scheduling the command in `routes/console.php` (or `app/Console/Kernel.php`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('auditify:prune --days=90')->daily();
```

---

## 🗺️ Routes Reference
All routes are grouped under the configured `route_prefix` (default: `auditify`) with the configured middleware.

| Method | URI | Controller Action | Description |
|---|---|---|---|
| GET | `/` | `DashboardController@index` | Main logs dashboard index |
| GET | `/action-logs` | `ActionLogController@index` | View list of database action logs |
| GET | `/action-logs/{id}` | `ActionLogController@show` | View details with side-by-side attributes difference |
| GET | `/action-logs/export/csv` | `ActionLogController@exportCsv` | Export action logs in CSV format |
| GET | `/action-logs/export/excel` | `ActionLogController@exportExcel` | Export action logs in Excel format |
| GET | `/activity-logs` | `ActivityLogController@index` | View list of activity logs |
| GET | `/activity-logs/export/csv` | `ActivityLogController@exportCsv` | Export activity logs in CSV format |
| GET | `/activity-logs/export/excel` | `ActivityLogController@exportExcel` | Export activity logs in Excel format |
| GET | `/security-logs` | `SecurityLogController@index` | View list of security logs |
| GET | `/security-logs/unread-check` | `SecurityLogController@checkUnreadAlerts` | Live alert poll check |
| GET | `/security-logs/{id}` | `SecurityLogController@show` | View security log details |
| POST | `/security-logs/{id}/read` | `SecurityLogController@markAsRead` | Toggle log read state |
| GET | `/security-logs/export/csv` | `SecurityLogController@exportCsv` | Export security logs in CSV format |
| GET | `/security-logs/export/excel` | `SecurityLogController@exportExcel` | Export security logs in Excel format |
| POST | `/api/events` | `ActivityLogController@storeFrontendEvent` | Frontend client-side interaction logging |

---

## 🧪 Testing
The package features a comprehensive PHPUnit test suite covering models, middlewares, controller endpoints, and Artisan commands. Standalone tests run via `orchestra/testbench` without requiring a parent Laravel installation.

Clone the repository and install development dependencies:
```bash
git clone https://github.com/arpa12/laravel-auditify.git
cd laravel-auditify
composer install
```

Run all tests:
```bash
./vendor/bin/phpunit
```

---

## 👤 Author

<p align="center">
  <strong>Arpa Nihan</strong><br>
  <em>Full Stack Developer</em>
</p>

<p align="center">
  <a href="mailto:arpanihan8@gmail.com"><img src="https://img.shields.io/badge/Email-arpanihan8@gmail.com-D14836?style=for-the-badge&logo=gmail&logoColor=white" alt="Email"></a>
  <a href="https://www.linkedin.com/in/arpanihan/"><img src="https://img.shields.io/badge/LinkedIn-arpanihan-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white" alt="LinkedIn"></a>
  <a href="https://github.com/arpa12"><img src="https://img.shields.io/badge/GitHub-arpa12-181717?style=for-the-badge&logo=github&logoColor=white" alt="GitHub"></a>
</p>

---

## 📄 License

Released under the [MIT License](LICENSE.md).

```text
MIT License

Copyright (c) 2026 Arpa Nihan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

<p align="center">
  ⭐ If Auditify saves you time, please give it a star on GitHub! ⭐
</p>

<p align="center">
  <em>Made with ❤️ for the Laravel Community</em>
</p>

<p align="center">
  Copyright &copy; 2026 <a href="https://github.com/arpa12">Arpa Nihan</a>. All rights reserved.
</p>
