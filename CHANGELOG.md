# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.2] - 2026-06-16

### Fixed
- **Payload Serialization**: Sanitized `UploadedFile` and general PHP object instances inside request payloads to prevent JSON encoding crashes in the database when logging security events during file uploads (e.g., uploading logo images in setting updates).

---

## [1.0.1] - 2026-06-16

### Added
- **Activity Log Detail Page**: Added a dedicated show view (`GET /activity-logs/{id}`) allowing administrators to drill down into activity details (request URLs, IP address, user-agent metadata, user mappings, and custom JSON property payloads).
- **Dynamic Multi-Credential Support**: Added the `user_fields` configuration setting to dynamically map and audit multiple user identifiers (email, username, phone) simultaneously in successful login, logout, and failed login events.
- **Configurable Polling Interval**: Added `security_polling_interval` config to make the frontend unread security alerts polling interval configurable. Defaults to `0` (disabled) to prevent background loop execution and resource consumption in dev/production.
- **Automated Cache Clearing**: Integrated `php artisan optimize:clear` execution directly inside the `auditify:install` installer command.

### Fixed
- **UI Column Wrapping**: Disabled text truncation in the Security Reports description column to allow long threat details to wrap naturally.
- **Horizontal Scrollbars**: Constrained dashboard and log list table column widths to prevent unwanted horizontal scrolling.
- **Dashboard Version Badge**: Bumped the visual layout footer brand badge to `v1.0.1`.

---

## [1.0.0] - 2026-06-15

### Added
- Initial release of Laravel Auditify.
- Decoupled database schema logging (Action Logs, Activity Logs, Security Logs).
- Interactive Glassmorphic Admin Dashboard (`/auditify`) with live alerts, 7-day analytics charts, and trend metrics.
- Reporting & Analytics Panel (`/reports`) with exports (CSV, Excel, PDF).
- Built-in XSS Protection Firewall Middleware.
- Real-time Threat Engine (Failed Logins monitoring, Mass Delete Shield, Bulk Update Shield, Sensitive Module checks).
- Client-side Frontend Logging API (`/auditify/api/events`).
- Custom authorization callback registration.
- Standalone PHPUnit test suite.
