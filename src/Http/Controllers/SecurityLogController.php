<?php

namespace Auditify\Http\Controllers;

use Auditify\Models\SecurityLog;
use Auditify\Facades\Auditify;
use Illuminate\Http\Request;
use Auditify\Exports\SecurityLogsExport;
use Maatwebsite\Excel\Facades\Excel;

class SecurityLogController
{
    protected function buildFilterQuery()
    {
        $query = SecurityLog::query();

        if (request('search')) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.request('search').'%')
                    ->orWhere('description', 'like', '%'.request('search').'%');
            });
        }

        if (request('severity')) {
            $query->where('severity', request('severity'));
        }

        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        if (request()->has('is_read') && request('is_read') !== '') {
            $query->where('is_read', request('is_read'));
        }

        if (request('start_date')) {
            $query->whereDate('created_at', '>=', request('start_date'));
        }

        if (request('end_date')) {
            $query->whereDate('created_at', '<=', request('end_date'));
        }

        return $query;
    }

    public function index()
    {
        if (request('search') || request('severity') || request('user_id')) {
            Auditify::logActivity('Search Security Logs');
        }

        $query = $this->buildFilterQuery();

        $logs = $query
            ->with('user')
            ->latest()
            ->paginate(config('auditify.pagination', 20))
            ->withQueryString();

        $users = SecurityLog::query()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        return view('auditify::security-logs.index', compact('logs', 'users'));
    }

    public function show($id)
    {
        $log = SecurityLog::findOrFail($id);

        return view('auditify::security-logs.show', compact('log'));
    }

    public function markAsRead($id)
    {
        $log = SecurityLog::findOrFail($id);
        $log->update(['is_read' => true]);

        Auditify::logActivity("Resolved Security Alert #{$log->id}");

        return redirect()->back()->with('success', 'Security log marked as read.');
    }

    public function exportCsv()
    {
        Auditify::logActivity('Export Security Logs (CSV)');

        $query = $this->buildFilterQuery()->with('user')->latest();
        $fileName = 'security_logs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($query) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'User', 'Severity', 'Title', 'Description', 'IP Address', 'User Agent', 'Status', 'Created At'
            ]);

            foreach ($query->cursor() as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Guest' . ($log->user_id ? ' (ID: ' . $log->user_id . ')' : ''),
                    strtoupper($log->severity),
                    $log->title,
                    $log->description,
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                    $log->is_read ? 'Read' : 'Unread',
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel()
    {
        Auditify::logActivity('Export Security Logs (Excel)');

        $query = $this->buildFilterQuery()->latest();
        $fileName = 'security_logs_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new SecurityLogsExport($query), 
            $fileName
        );
    }

    public function checkUnreadAlerts()
    {
        $unreadCount = SecurityLog::unread()->count();
        $recentAlerts = SecurityLog::unread()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'severity' => $alert->severity,
                    'time_ago' => $alert->created_at->diffForHumans(),
                    'url' => url(config('auditify.route_prefix', 'auditify') . '/security-logs/' . $alert->id)
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'recent_alerts' => $recentAlerts
        ]);
    }
}
