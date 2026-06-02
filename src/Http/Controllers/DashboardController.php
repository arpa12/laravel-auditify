<?php

namespace Auditify\Http\Controllers;

use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;

class DashboardController
{
    public function index()
    {
        // Counts
        $totalActionLogs = ActionLog::count();
        $totalActivityLogs = ActivityLog::count();
        $totalSecurityLogs = SecurityLog::count();
        $unreadSecurityLogsCount = SecurityLog::unread()->count();

        // Action Breakdown
        $createLogs = ActionLog::where('action', 'CREATE')->count();
        $updateLogs = ActionLog::where('action', 'UPDATE')->count();
        $deleteLogs = ActionLog::where('action', 'DELETE')->count();
        $restoreLogs = ActionLog::where('action', 'RESTORE')->count();
        $approveLogs = ActionLog::where('action', 'APPROVE')->count();
        $rejectLogs = ActionLog::where('action', 'REJECT')->count();

        // Activity Breakdown
        $loginLogs = ActivityLog::where('activity', 'like', 'Login%')->count();
        $logoutLogs = ActivityLog::where('activity', 'like', 'Logout%')->count();
        $failedLoginLogs = ActivityLog::where('activity', 'like', 'Failed Login%')->count();
        $visitLogs = ActivityLog::where('activity', 'like', 'Page Visit%')->count();

        // Daily Charts (Last 7 Days)
        $actionChartData = collect();
        $activityChartData = collect();

        $actionGroup = ActionLog::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        $activityGroup = ActivityLog::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $actionChartData->put($date, $actionGroup->get($date, collect())->count());
            $activityChartData->put($date, $activityGroup->get($date, collect())->count());
        }

        // Top Active Users (Combined Action + Activity logs count, grouped by user_id)
        $topUsersList = ActionLog::selectRaw('user_id, count(*) as count')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get()
            ->concat(
                ActivityLog::selectRaw('user_id, count(*) as count')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->get()
            )
            ->groupBy('user_id')
            ->map(fn ($items) => $items->sum('count'))
            ->sortDesc()
            ->take(5);

        $userModel = config('auth.providers.users.model');
        $topUsers = collect();
        foreach ($topUsersList as $userId => $count) {
            $user = $userModel::find($userId);
            if ($user) {
                $topUsers->push((object)[
                    'user' => $user,
                    'count' => $count
                ]);
            }
        }

        // Top Modified Modules
        $topModules = ActionLog::selectRaw('module, count(*) as count')
            ->groupBy('module')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Recent Critical Alerts
        $recentSecurityLogs = SecurityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('auditify::dashboard.index', compact(
            'totalActionLogs',
            'totalActivityLogs',
            'totalSecurityLogs',
            'unreadSecurityLogsCount',
            'createLogs',
            'updateLogs',
            'deleteLogs',
            'restoreLogs',
            'approveLogs',
            'rejectLogs',
            'loginLogs',
            'logoutLogs',
            'failedLoginLogs',
            'visitLogs',
            'actionChartData',
            'activityChartData',
            'topUsers',
            'topModules',
            'recentSecurityLogs',
            'recentActivities'
        ));
    }
}
