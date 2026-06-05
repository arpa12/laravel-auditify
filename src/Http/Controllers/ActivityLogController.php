<?php

namespace Auditify\Http\Controllers;

use Auditify\Models\ActivityLog;
use Auditify\Facades\Auditify;
use Illuminate\Http\Request;
use Auditify\Exports\ActivityLogsExport;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogController
{
    protected function buildFilterQuery()
    {
        $query = ActivityLog::query();

        if (request('search')) {
            $query->where(function ($q) {
                $q->where('activity', 'like', '%'.request('search').'%')
                    ->orWhere('url', 'like', '%'.request('search').'%');
            });
        }

        if (request('activity')) {
            $query->where('activity', 'like', request('activity').'%');
        }

        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        if (request('ip_address')) {
            $query->where('ip_address', 'like', '%'.request('ip_address').'%');
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
        if (request('search') || request('activity') || request('user_id') || request('ip_address')) {
            Auditify::logActivity('Search Activity Logs');
        }

        $query = $this->buildFilterQuery();

        $logs = $query
            ->with('user')
            ->latest()
            ->paginate(config('auditify.pagination', 20))
            ->withQueryString();

        $users = ActivityLog::query()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        $activities = ActivityLog::query()
            ->selectRaw('DISTINCT CASE 
                WHEN activity LIKE "Page Visit%" THEN "Page Visit"
                WHEN activity LIKE "Login%" THEN "Login"
                WHEN activity LIKE "Logout%" THEN "Logout"
                WHEN activity LIKE "Failed Login%" THEN "Failed Login"
                ELSE activity END as activity_group')
            ->pluck('activity_group')
            ->filter()
            ->values();

        return view('auditify::activity-logs.index', compact('logs', 'users', 'activities'));
    }

    public function storeFrontendEvent(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        Auditify::logActivity(
            $validated['event_name'] . ': ' . $validated['description']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Event logged successfully'
        ], 201);
    }

    public function exportCsv()
    {
        Auditify::logActivity('Export Activity Logs (CSV)');

        $query = $this->buildFilterQuery()->with('user')->latest();
        $fileName = 'activity_logs_' . now()->format('Ymd_His') . '.csv';

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
                'ID', 'User', 'Activity', 'Request URL', 'IP Address', 'User Agent', 'Created At'
            ]);

            foreach ($query->cursor() as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Guest' . ($log->user_id ? ' (ID: ' . $log->user_id . ')' : ''),
                    $log->activity,
                    $log->url ?? '-',
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel()
    {
        Auditify::logActivity('Export Activity Logs (Excel)');

        $query = $this->buildFilterQuery()->latest();
        $fileName = 'activity_logs_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new ActivityLogsExport($query), 
            $fileName
        );
    }

    public function exportPdf()
    {
        \Auditify\Facades\Auditify::logActivity('Export Activity Logs (PDF)');

        $query = $this->buildFilterQuery()->with('user')->latest();
        $logs = $query->limit(200)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('auditify::reports.pdf-activity', compact('logs'));

        return $pdf->download('activity_logs_' . now()->format('Ymd_His') . '.pdf');
    }
}
