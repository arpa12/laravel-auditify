<?php

use Illuminate\Support\Facades\Route;
use Auditify\Http\Controllers\DashboardController;
use Auditify\Http\Controllers\ActionLogController;
use Auditify\Http\Controllers\ActivityLogController;
use Auditify\Http\Controllers\SecurityLogController;
use Auditify\Http\Middleware\Authorize;

$middleware = config('auditify.middleware', ['web']);
$middleware[] = Authorize::class;

Route::middleware($middleware)
    ->prefix(config('auditify.route_prefix', 'auditify'))
    ->group(function () {

        // Redirect root to Action Logs (Landing/Dashboard Overview removed)
        Route::get('/', function () {
            return redirect()->to(url(config('auditify.route_prefix', 'auditify') . '/action-logs'));
        });

        // Module 1: Action Logs
        Route::get('/action-logs', [ActionLogController::class, 'index']);
        Route::get('/action-logs/export/csv', [ActionLogController::class, 'exportCsv']);
        Route::get('/action-logs/export/excel', [ActionLogController::class, 'exportExcel']);
        Route::get('/action-logs/{id}', [ActionLogController::class, 'show']);

        // Module 2: Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/export/csv', [ActivityLogController::class, 'exportCsv']);
        Route::get('/activity-logs/export/excel', [ActivityLogController::class, 'exportExcel']);

        // Module 3: Security Logs
        Route::get('/security-logs', [SecurityLogController::class, 'index']);
        Route::get('/security-logs/unread-check', [SecurityLogController::class, 'checkUnreadAlerts']);
        Route::get('/security-logs/{id}', [SecurityLogController::class, 'show']);
        Route::post('/security-logs/{id}/read', [SecurityLogController::class, 'markAsRead']);
        Route::get('/security-logs/export/csv', [SecurityLogController::class, 'exportCsv']);
        Route::get('/security-logs/export/excel', [SecurityLogController::class, 'exportExcel']);

        // API Events (Module 2 Activity)
        Route::post('/api/events', [ActivityLogController::class, 'storeFrontendEvent']);

    });
