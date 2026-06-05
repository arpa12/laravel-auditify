# Auditify — High-Performance, Decoupled Audit Logging for Laravel

<p align="center">
  <img src="art/laravel_auditify_banner_v2.png" alt="Laravel Auditify Banner" width="100%">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Total Downloads](https://img.shields.io/packagist/dt/arpanihan/auditify.svg?style=flat-square)](https://packagist.org/packages/arpanihan/auditify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Auditify** is an easy-to-use, high-performance audit logging and threat detection package for Laravel. 

🔗 **Official Landing Page & Demo:** [arpa12.github.io/laravel-auditify-landingPage](https://arpa12.github.io/laravel-auditify-landingPage/)

Unlike standard logging libraries, Auditify uses a **decoupled database design** to separate logs into three distinct tables. This optimizes database table indexing, reduces write congestion, and guarantees clean scale organization as your application grows.

---

## Table of Contents
* [Requirements](#-requirements)
* [Installation](#-installation)
* [Configuration](#-configuration)
* [Features](#-features)
  * [Dashboard](#-dashboard)
  * [Reports & Analytics Module](#-reports--analytics-module)
  * [Decoupled Log Modules](#-decoupled-log-modules)
  * [Real-Time Threat Engine](#-real-time-threat-engine)
  * [XSS Attack Shield](#-xss-attack-shield)
  * [Frontend Event Logging API](#-frontend-event-logging-api-optional)
  * [Admin-Only Access & Custom Authorization Gate](#-admin-only-access--custom-authorization-gate)
* [Manual Logging & Helper Methods](#-manual-logging--helper-methods)
* [Artisan Commands](#%EF%B8%8F-artisan-commands)
* [Routes Reference](#-routes-reference)
* [Testing](#-testing)
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

## 🚀 Features

### 📊 Dashboard
URL: `/auditify`

The main glassmorphic dashboard aggregates action logs, page visits, and threat alerts into an interactive screen:
* **Metrics counters** — total action logs, activity logs, and security logs with unread indicators.
* **Log summaries** — breakdowns of operations (Create, Update, Delete) and authentication events (Login, Logout).
* **7-day trend graph** — comparative daily counts of Actions vs Activities plotted on Chart.js.
* **Top active users & top modified modules** — lists of most active user IDs and frequently changed models.
* **Live recent logs** — lists of the most recent visitor actions and security alerts.

### 📈 Reports & Analytics Module
URL: `/auditify/reports`

A comprehensive reporting panel offering detailed statistical breakdowns over custom timeframes (Last 7 Days, Last 30 Days, or Last 90 Days) featuring interactive Chart.js charts:
* **Overview Analytics:** System log activity trends mapped using multi-line charts.
* **Action Reports:** Actions by type (Doughnut Chart) and most changed models (Horizontal Bar Chart), coupled with a detailed database modifications table and CSV/Excel/PDF download options.
* **Activity Reports:** Top visited pages/URLs (Horizontal Bar Chart) and peak activity hours (Bar Chart), coupled with a detailed activity table and CSV/Excel/PDF download options.
* **Security Reports:** Alerts by severity (Doughnut Chart), top threat origin IPs (Bar Chart), and resolution status distribution (Pie Chart), coupled with a detailed security incident table and CSV/Excel/PDF download options.
* **Format Exports:** Support for exporting timeframe-filtered reports in CSV, Excel (xlsx), and clean printable PDF formats.

### 🗄️ Decoupled Log Modules
Auditify separates data logging into three target models under `Auditify\Models` to avoid write bottlenecks. Here is the exact database schema and attributes captured for each log type:

#### Action Logs (`ActionLog`)
*   **Table Name**: `audit_action_logs`
*   **Purpose**: Tracks Eloquent database modifications (inserts, updates, deletes, and restores).
*   **Captured Attributes / Database Columns**:
    *   `id` (BigInt, Primary Key)
    *   `user_id` & `user_type` (Nullable Morphs) — Polymorphic relationship to the user making the database modification.
    *   `subject_id` & `subject_type` (Nullable Morphs) — Polymorphic relationship to the actual model being modified.
    *   `action` (String) — The query event (`created`, `updated`, `deleted`, `restored`).
    *   `module` (String) — Name of the affected model module/class (e.g., `Post`, `User`, `Role`).
    *   `description` (Text, Nullable) — Friendly readable log summary description.
    *   `old_values` (JSON, Nullable) — Casted array of model attribute values before the operation.
    *   `new_values` (JSON, Nullable) — Casted array of model attribute values after the operation.
    *   `ip_address` (String, Nullable) — IP address of the client triggering the event.
    *   `url` (Text, Nullable) — HTTP Request URL where the change originated.
    *   `user_agent` (Text, Nullable) — HTTP User-Agent string.
    *   `created_at` & `updated_at` (Timestamps)

#### Activity Logs (`ActivityLog`)
*   **Table Name**: `audit_activity_logs`
*   **Purpose**: Logs visitor requests, navigation, custom application actions, and auth flows.
*   **Captured Attributes / Database Columns**:
    *   `id` (BigInt, Primary Key)
    *   `user_id` & `user_type` (Nullable Morphs) — Polymorphic relationship to the visitor (if authenticated).
    *   `activity` (String) — The type of operation/activity (e.g., `Page Visit`, `Login`, `Logout`, `Failed Login`, or custom events).
    *   `properties` (JSON, Nullable) — Casted array storing context metadata or custom event payloads.
    *   `url` (Text, Nullable) — Request URL path.
    *   `ip_address` (String, Nullable) — Visitor IP address.
    *   `user_agent` (Text, Nullable) — Visitor User-Agent string.
    *   `created_at` & `updated_at` (Timestamps)

#### Security Logs (`SecurityLog`)
*   **Table Name**: `audit_security_logs`
*   **Purpose**: Logs alerts generated by the XSS protection middleware, rate limits, or automated threat rules.
*   **Captured Attributes / Database Columns**:
    *   `id` (BigInt, Primary Key)
    *   `user_id` & `user_type` (Nullable Morphs) — Polymorphic relationship to the user (if authenticated).
    *   `severity` (String) — Alert priority (`low`, `medium`, `high`, `critical`).
    *   `title` (String) — Brief name of the incident (e.g., `XSS Attack Blocked`, `Failed Logins Peak`).
    *   `description` (Text, Nullable) — In-depth details regarding why the threat was flagged.
    *   `is_read` (Boolean) — Read/unread toggle flag for admin dashboard alerts.
    *   `status` (String) — Alert resolution state (`pending` or `resolved`).
    *   `resolved_at` (DateTime, Nullable) — Timestamp when the incident was marked resolved/read.
    *   `resolution_notes` (Text, Nullable) — Text explaining the resolution strategy.
    *   `method` (String, Nullable) — Request HTTP method (`GET`, `POST`, etc.).
    *   `route_name` (String, Nullable) — Named route where the attack/alert occurred.
    *   `payload` (JSON, Nullable) — Casted request body payload parameters scanned by the security shield.
    *   `ip_address` (String, Nullable) — Origin IP address of the incident.
    *   `user_agent` (Text, Nullable) — Incident Client User-Agent.
    *   `created_at` & `updated_at` (Timestamps)

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

### 🔌 Frontend Event Logging API (Optional)

> [!NOTE]
> **What is this for?**
> Standard backend code (like Laravel/PHP) cannot see what happens inside the user's browser. Out-of-the-box, it cannot track when a user clicks a button, closes a modal, or downloads a file.
> 
> This API provides a built-in route (`/auditify/api/events`) so you can easily send browser actions straight into your **Activity Logs** via JavaScript—without needing to write your own custom API controllers and routes. You can ignore this if you don't need to track frontend actions.

#### How to use it:

1. **Create a reusable JavaScript helper** to send a `POST` request (which automatically attaches Laravel's CSRF security token):

```javascript
function logEvent(eventName, description) {
    fetch('/auditify/api/events', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            event_name: eventName,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => console.log('Event logged:', data))
    .catch(error => console.error('Error logging event:', error));
}
```

2. **Trigger it on user actions** (like clicking a button or a download link):

```html
<!-- Example 1: Track a File Download -->
<a href="/downloads/guide.pdf" onclick="logEvent('File Download', 'User downloaded the User_Guide.pdf document')">
    Download User Guide
</a>

<!-- Example 2: Track a checkout button click -->
<button onclick="logEvent('Checkout Init', 'User clicked the Checkout button')">
    Proceed to Payment
</button>
```

#### ⚛️ Vue & React Integration (SPAs)
If your application uses a frontend framework like React or Vue.js:
- **Same-domain or Laravel Inertia**: You can call the `/auditify/api/events` endpoint directly using `axios` or `fetch`. Session cookies and CSRF security are handled automatically by the browser.
- **Decoupled Setup (Different Domains)**: If your React/Vue frontend is hosted separately from your Laravel API:
  1. Add your frontend domain to the allowed origins list in Laravel's CORS configuration (`config/cors.php`).
  2. Authenticate requests via Laravel Sanctum or standard authorization headers so Auditify can link the logged events to the correct user.

##### ⚛️ React Component Example (using Axios)
```jsx
import React, { useState } from 'react';
import axios from 'axios';

// IMPORTANT: Enable session cookie sharing across domains if your frontend 
// runs on a different port/subdomain from your Laravel backend (e.g. localhost:3000 vs localhost:8000).
axios.defaults.withCredentials = true;

// Configure this URL to match your Laravel application route
const AUDITIFY_API_URL = 'http://localhost:8000/auditify/api/events';

export default function UpgradeButton() {
    // Track loading state to prevent double clicks and duplicate event logging
    const [isLoading, setIsLoading] = useState(false);

    const handleUpgrade = async () => {
        setIsLoading(true);
        try {
            // 1. Run your standard application business logic (e.g. payment gateway checkout)
            console.log("Upgrading plan...");

            // 2. Fetch the CSRF validation token from the host HTML meta tag.
            // Note: If running decoupled (CORS), you may need to fetch the token via Sanctum.
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // 3. Log the frontend event directly to the Auditify package
            await axios.post(AUDITIFY_API_URL, {
                event_name: 'Upgrade Plan Clicked',
                description: 'User initiated subscription upgrade to Gold tier.'
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // Pass the CSRF token to pass Laravel's VerifyCsrfToken middleware
                }
            });

            alert('Plan upgraded successfully and logged in Auditify!');
        } catch (error) {
            console.error('Error logging event to Auditify backend:', error);
        } finally {
            // Reset the loading state
            setIsLoading(false);
        }
    };

    return (
        <button onClick={handleUpgrade} disabled={isLoading}>
            {isLoading ? 'Processing...' : 'Upgrade to Gold Plan 🚀'}
        </button>
    );
}
```

##### 💚 Vue 3 Component Example (using Fetch API)
```vue
<template>
  <div class="pricing-card">
    <!-- Bind disabled state to loading to prevent double submissions -->
    <button @click="handleUpgrade" :disabled="isLoading">
      {{ isLoading ? 'Processing...' : 'Upgrade to Gold Plan 🚀' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Reactive state to manage user action processing
const isLoading = ref(false);

// Configure this URL to match your Laravel application route
const AUDITIFY_API_URL = 'http://localhost:8000/auditify/api/events';

const handleUpgrade = async () => {
  isLoading.value = true;
  try {
    // 1. Run your standard application business logic (e.g. payment gateway checkout)
    console.log("Upgrading plan...");

    // 2. Retrieve the CSRF token from the meta tag to authenticate the session request
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // 3. Dispatch the event payload to the Auditify package logs endpoint
    const response = await fetch(AUDITIFY_API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken // Pass the CSRF token to pass Laravel's VerifyCsrfToken middleware
      },
      body: JSON.stringify({
        event_name: 'Upgrade Plan Clicked',
        description: 'User initiated subscription upgrade to Gold tier in Vue application.'
      })
    });

    if (response.ok) {
      alert('Plan upgraded successfully and logged in Auditify!');
    } else {
      console.error('Auditify endpoint returned an error response:', await response.text());
    }
  } catch (error) {
    console.error('Network error attempting to log event:', error);
  } finally {
    // Reset status
    isLoading.value = false;
  }
};
</script>
```

### 🔐 Admin-Only Access & Custom Authorization Gate

> [!IMPORTANT]
> **Who should access the logs?**
> The Auditify log database and visual dashboard (`/auditify`) contain highly sensitive information including user IP addresses, request payloads, model modifications, and security alert histories.
>
> **Regular application users must never have access to this package.** It is designed exclusively for system administrators, super-admins, and security officers.

By default, the package enables access control if configured in `config/auditify.php`. **You should register an authorization callback** inside your application so developers can custom-define exactly who is classified as an authorized admin.

To restrict dashboard views and log downloads to administrators only, register an authorization callback inside the `boot` method of your `app/Providers/AppServiceProvider.php`:

```php
use Auditify\Facades\Auditify;

public function boot()
{
    // Restrict all Auditify dashboard and API routes to Admin users
    Auditify::auth(function ($request) {
        // Option A: Check user role (e.g. if using Spatie Role package)
        return $request->user() && $request->user()->hasRole('admin');

        // Option B: Check simple is_admin database flag
        // return $request->user() && $request->user()->is_admin;

        // Option C: Check a specific company email domain
        // return $request->user() && str_ends_with($request->user()->email, '@yourcompany.com');
    });
}
```

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

### 3. (Optional) Selective Model Auditing
By default, Auditify automatically audits **all Eloquent models** globally without any manual setup. 

However, if you turn off global auditing (`'auto_audit_models' => false`) and prefer to manually select which models to audit, add the `Auditable` trait:
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

    // Global model auditing
    'auto_audit_models' => true, // Tracks all model lifecycle changes globally
    'exclude_models' => [        // Model classes to exclude from global auditing
        // App\Models\Session::class,
    ],

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

## 🔌 Manual Logging & Helper Methods

You can manually trigger Auditify logs or temporarily pause auditing from your own Laravel controllers, background jobs, or seeders using the `Auditify` facade.

> [!TIP]
> **Why use manual logging?**
> * **Capture Intent over CRUD:** Automated auditing only tracks database changes (inserts/updates). It cannot know when a user performs a non-CRUD action like downloading a PDF report, requesting a password reset, or opening a modal.
> * **Standardize Multi-step Workflows:** Instead of cluttering logs with 10 automated database change entries during a checkout flow, you can pause automatic logging and write a single, clean entry (e.g., `User Completed Purchase`).
> * **Log Custom Threat Metrics:** Manually document custom suspicious behaviors (like rate-limit hits, coupon code abuse, or restricted API probes) using `Auditify::logSecurity`.
> * **Optimize Seeding & Imports:** Turn off auditing during massive database seeding or CSV user imports to prevent database write congestion, performance lags, and log spam.

### 1. Manual Log Generation

Import the facade in your file:
```php
use Auditify\Facades\Auditify;
```

#### Manually Log a Database/Module Action
```php
Auditify::logAction(
    action: 'PUBLISH',
    module: 'Article',
    description: 'User published a new article',
    oldValues: ['status' => 'draft'],
    newValues: ['status' => 'published'],
    userId: auth()->id(), // optional, defaults to current authenticated user
    subject: $article     // optional, polymorphic eloquent model instance
);
```

#### Manually Log a User Activity
```php
Auditify::logActivity(
    activity: 'Exported Reports (PDF)',
    url: request()->fullUrl(), // optional, defaults to request URL
    userId: auth()->id(),      // optional
    properties: ['timeframe' => '30_days'] // optional metadata payload
);
```

#### Manually Log a Security Alert
```php
Auditify::logSecurity(
    title: 'Suspicious Endpoint Access',
    description: 'Blocked access attempt to restricted legacy endpoint',
    severity: 'high', // options: low, medium, high, critical
    payload: request()->all() // optional JSON request context
);
```

#### Real-World Controller Example
Here is a practical example of how to implement manual logging inside a standard Laravel controller, complete with code comments:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Auditify\Facades\Auditify; // Import the Auditify facade

class OrderController extends Controller
{
    /**
     * Cancel a customer order and log the audit trail.
     */
    public function cancel(Request $request, $id)
    {
        // 1. Retrieve the order record from the database
        $order = Order::findOrFail($id);

        // Capture the original status for audit comparison
        $oldStatus = $order->status;

        // 2. Perform the cancellation business logic
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason')
        ]);

        // 3. Manually Log a database Action showing the status transition
        Auditify::logAction(
            action: 'CANCEL_ORDER',
            module: 'Order',
            description: "Order #{$order->id} cancelled by user due to: " . $request->input('reason'),
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'cancelled'],
            userId: auth()->id(), // Associate log with the logged-in administrator
            subject: $order      // Link the polymorphic subject relation to this order model
        );

        // 4. Log a general user activity for dashboard stats tracking
        Auditify::logActivity(
            activity: 'Cancelled Order',
            properties: [
                'order_id' => $order->id,
                'total_amount' => $order->total_price
            ]
        );

        // 5. Send a redirect response back to the admin portal
        return redirect()->back()->with('success', 'Order cancelled and action audited.');
    }
}
```

---

### 2. Pausing Auditing (Seeders & Batch Imports)

When running data migrations, database seeders, or large CSV imports, you should temporarily disable logging:
*   **Prevent Database Congestion (Performance):** Auditing bulk inserts doubles database queries (100k records = 200k queries). Pausing logs prevents database lockups and speeds up imports.
*   **Prevent Table Bloat (Log Spam):** Seeders generate fake mock data. Pausing auditing prevents these fake records from filling up your audit log tables and slowing down search speeds.

#### Option A: Running a closure (Recommended for Seeders & Migrations)
**Where to use:** In **`database/seeders/DatabaseSeeder.php`** or data migration files.

This helper automatically pauses auditing for the duration of the callback execution and safely restores the previous auditing state.

> [!TIP]
> **Why Option A is recommended (Exception Safe):**
> If an error occurs inside your import/seeder logic, this helper uses an underlying PHP `finally` block to **guarantee** that auditing is safely re-enabled for all subsequent requests.

```php
use Auditify\Facades\Auditify;

// Pauses auditing automatically for the duration of the callback function
Auditify::withoutAuditing(function () {
    // Generate 1,000 dummy articles silently without log spam
    Article::factory()->count(1000)->create();
});
```

#### Option B: Manually toggle auditing (Recommended for Artisan Commands & Test Suites)
**Where to use:** In custom Artisan console commands (`app/Console/Commands/ImportData.php`) or PHPUnit test setups (`tests/TestCase.php`).

Directly turns the auditing flag on or off.

> [!WARNING]
> **Use Option B with caution:**
> If your code throws an exception after calling `disableAuditing()`, it will crash *before* reaching `enableAuditing()`, leaving auditing permanently disabled on that PHP worker/process. Only use this option when a closure structure cannot be used.

```php
use Auditify\Facades\Auditify;

// 1. Manually disable auditing
Auditify::disableAuditing();

// 2. Perform sequential operations...
User::insert($massiveCsvData);

// 3. Manually re-enable auditing
Auditify::enableAuditing();
```

---

## 🛠️ Artisan Commands

Auditify provides dedicated commands to automate installation and database size optimization:

| Command | Description |
|---|---|
| `auditify:install` | Runs migrations and publishes configuration files automatically. |
| `auditify:prune {--days=}` | Deletes old audit log records older than N days (defaults to `keep_days` config value). |

### 1. Why use the commands?
*   **`auditify:install` (Simpler Onboarding):** Eliminates manual setup. Rather than forcing developers to copy configurations, move migration files, and migrate tables separately, this sets up the entire package in a single terminal line.
*   **`auditify:prune` (Optimize DB Speed & Storage):** High-traffic production systems generate millions of logs. Storing logs indefinitely slows down database queries, increases backup sizes, and increases storage costs. Pruning deletes outdated entries, ensuring instant dashboard rendering and compliance retention alignment (e.g., GDPR, SOC2).

### 2. Automated Database Pruning Setup
To keep database tables small and performant automatically, schedule the pruning command in your application's task scheduler in `routes/console.php` (or `app/Console/Kernel.php`):

> [!TIP]
> **Set It and Forget It:** Automating the pruning command to run daily ensures your database size remains constrained and healthy without manual administrator intervention. It is recommended to schedule it during low-traffic night hours.

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
| GET | `/action-logs/export/pdf` | `ActionLogController@exportPdf` | Export action logs in PDF format |
| GET | `/activity-logs` | `ActivityLogController@index` | View list of activity logs |
| GET | `/activity-logs/export/csv` | `ActivityLogController@exportCsv` | Export activity logs in CSV format |
| GET | `/activity-logs/export/excel` | `ActivityLogController@exportExcel` | Export activity logs in Excel format |
| GET | `/activity-logs/export/pdf` | `ActivityLogController@exportPdf` | Export activity logs in PDF format |
| GET | `/security-logs` | `SecurityLogController@index` | View list of security logs |
| GET | `/security-logs/unread-check` | `SecurityLogController@checkUnreadAlerts` | Live alert poll check |
| GET | `/security-logs/{id}` | `SecurityLogController@show` | View security log details |
| POST | `/security-logs/{id}/read` | `SecurityLogController@markAsRead` | Toggle log read state |
| GET | `/security-logs/export/csv` | `SecurityLogController@exportCsv` | Export security logs in CSV format |
| GET | `/security-logs/export/excel` | `SecurityLogController@exportExcel` | Export security logs in Excel format |
| GET | `/security-logs/export/pdf` | `SecurityLogController@exportPdf` | Export security logs in PDF format |
| POST | `/api/events` | `ActivityLogController@storeFrontendEvent` | Frontend client-side interaction logging |
| GET | `/reports` | `ReportController@index` | View detailed log analytics and reports |

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
