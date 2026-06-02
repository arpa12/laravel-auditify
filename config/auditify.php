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

    'pruning' => [
        'keep_days' => 90,
    ],

];
