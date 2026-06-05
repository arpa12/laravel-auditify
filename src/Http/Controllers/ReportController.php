<?php

namespace Auditify\Http\Controllers;

use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController
{
    public function index(Request $request)
    {
        $timeframe = $request->query('timeframe', '30'); // Default to 30 days
        $days = (int) $timeframe;
        $startDate = now()->subDays($days - 1)->startOfDay();

        // 1. OVERVIEW DATA
        $totalActionLogs = ActionLog::where('created_at', '>=', $startDate)->count();
        $totalActivityLogs = ActivityLog::where('created_at', '>=', $startDate)->count();
        $totalSecurityLogs = SecurityLog::where('created_at', '>=', $startDate)->count();

        // 2. DAILY TRENDS DATA
        $actionChartData = collect();
        $activityChartData = collect();
        $securityChartData = collect();

        $actionGroup = ActionLog::where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        $activityGroup = ActivityLog::where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        $securityGroup = SecurityLog::where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $actionChartData->put($date, $actionGroup->get($date, collect())->count());
            $activityChartData->put($date, $activityGroup->get($date, collect())->count());
            $securityChartData->put($date, $securityGroup->get($date, collect())->count());
        }

        // 3. ACTION REPORTS DATA
        // Action Breakdown (CREATE, UPDATE, DELETE)
        $actionBreakdown = ActionLog::where('created_at', '>=', $startDate)
            ->selectRaw('action, count(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->pluck('count', 'action')
            ->toArray();

        // Actions by Module/Model
        $actionsByModule = ActionLog::where('created_at', '>=', $startDate)
            ->selectRaw('module, count(*) as count')
            ->groupBy('module')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'module')
            ->toArray();

        // 4. ACTIVITY REPORTS DATA
        // Top Visited Pages
        $topPages = ActivityLog::where('created_at', '>=', $startDate)
            ->where('activity', 'like', 'Page Visit:%')
            ->selectRaw('activity, count(*) as count')
            ->groupBy('activity')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                // Strip the "Page Visit: " prefix
                $item->page = str_replace('Page Visit: ', '', $item->activity);
                return $item;
            })
            ->pluck('count', 'page')
            ->toArray();

        // Hourly Activity Distribution (Peak hours)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $hourlyData = ActivityLog::where('created_at', '>=', $startDate)
                ->selectRaw("strftime('%H', created_at) as hour, count(*) as count")
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('count', 'hour')
                ->toArray();
        } else {
            $hourlyData = ActivityLog::where('created_at', '>=', $startDate)
                ->selectRaw("HOUR(created_at) as hour, count(*) as count")
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('count', 'hour')
                ->toArray();
        }

        // Pad hours with 0 counts for missing hours
        $hourlyDistribution = [];
        for ($h = 0; $h < 24; $h++) {
            $hourStr = sprintf('%02d', $h);
            $hourlyDistribution[$hourStr . ':00'] = $hourlyData[$hourStr] ?? ($hourlyData[$h] ?? 0);
        }

        // 5. SECURITY REPORTS DATA
        // Severity Breakdown
        $securitySeverity = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Top Threat Origins (IP Addresses)
        $topSecurityIps = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('ip_address, count(*) as count')
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'ip_address')
            ->toArray();

        // Resolution Statuses
        $resolutionStatus = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Active Warning Incidents
        $unreadSecurityCount = SecurityLog::unread()->count();

        // 6. DETAILED RECENT LOGS TABLES FOR REPORTS
        $recentActionLogs = ActionLog::with('user', 'subject')
            ->where('created_at', '>=', $startDate)
            ->latest()
            ->limit(10)
            ->get();

        $recentActivityLogs = ActivityLog::with('user')
            ->where('created_at', '>=', $startDate)
            ->latest()
            ->limit(10)
            ->get();

        $recentSecurityLogs = SecurityLog::with('user')
            ->where('created_at', '>=', $startDate)
            ->latest()
            ->limit(10)
            ->get();

        $startDateString = $startDate->format('Y-m-d');

        return view('auditify::reports.index', compact(
            'timeframe',
            'days',
            'totalActionLogs',
            'totalActivityLogs',
            'totalSecurityLogs',
            'actionChartData',
            'activityChartData',
            'securityChartData',
            'actionBreakdown',
            'actionsByModule',
            'topPages',
            'hourlyDistribution',
            'securitySeverity',
            'topSecurityIps',
            'resolutionStatus',
            'unreadSecurityCount',
            'recentActionLogs',
            'recentActivityLogs',
            'recentSecurityLogs',
            'startDateString'
        ));
    }
}
