<?php

return [

    'route_prefix' => 'auditify',

    'theme' => 'dark', // Options: 'dark', 'light'

    'middleware' => [
        'web',
    ],

    'pagination' => 20,

    'track_ip' => true,

    'track_user_agent' => true,

    'track_url' => true,

    'authorization' => [
        'enabled' => false,
        'gate' => 'view-auditify',
    ],

    'track_auth_events' => true,

    /*
     * Mappings of user attributes for email, username, and phone numbers.
     * These will be dynamically extracted from your User model/login credentials.
     */
    'user_fields' => [
        'email' => 'email',
        'username' => 'username',
        'phone' => 'phone',
    ],

    'track_page_visits' => true,

    'alerts' => [
        'enabled' => false,
        'recipients' => ['admin@example.com'],
        'channels' => ['mail', 'log'],
        'sensitive_modules' => ['User', 'Role', 'Permission', 'Setting', 'Config'],
        'thresholds' => [
            'failed_logins' => 3,
            'failed_logins_timeframe' => 5, // minutes
            'mass_delete' => 5,
            'mass_delete_timeframe' => 5, // minutes
            'bulk_update' => 10,
            'bulk_update_timeframe' => 5, // minutes
        ],
    ],

    'xss_protection' => [
        'enabled' => true,
        'block' => true,
        'exclude_routes' => [
            // List route path patterns to exclude from scanning, e.g.:
            // 'admin/rich-text/*',
        ],
    ],

    'auto_audit_models' => true,

    /*
     * Interval (in seconds) for frontend auto-polling of new unread security alerts.
     * Set to 60 or higher for better performance, or set to 0 to disable polling entirely.
     */
    'security_polling_interval' => 0,

    'exclude_models' => [
        // List model classes to exclude from global auditing here, e.g.:
        // App\Models\Session::class,
    ],

    'pruning' => [
        'keep_days' => 90,
    ],

];
