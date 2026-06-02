<?php

namespace Auditify\Http\Controllers;

use Auditify\Models\ActionLog;
use Auditify\Facades\Auditify;
use Illuminate\Http\Request;
use Auditify\Exports\ActionLogsExport;
use Maatwebsite\Excel\Facades\Excel;

class ActionLogController
{
    protected function buildFilterQuery()
    {
        $query = ActionLog::query();

        if (request('search')) {
            $query->where(function ($q) {
                $q->where('module', 'like', '%'.request('search').'%')
                    ->orWhere('description', 'like', '%'.request('search').'%');
            });
        }

        if (request('action')) {
            $query->where('action', request('action'));
        }

        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        if (request('module')) {
            $query->where('module', request('module'));
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
        if (request('search') || request('action') || request('user_id') || request('module') || request('ip_address')) {
            Auditify::logActivity('Search Action Logs');
        }

        $query = $this->buildFilterQuery();

        $logs = $query
            ->with('user')
            ->latest()
            ->paginate(config('auditify.pagination', 20))
            ->withQueryString();

        $users = ActionLog::query()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        $modules = ActionLog::query()
            ->distinct()
            ->pluck('module')
            ->filter()
            ->values();

        return view('auditify::action-logs.index', compact('logs', 'users', 'modules'));
    }

    public function show($id)
    {
        $log = ActionLog::findOrFail($id);

        return view('auditify::action-logs.show', compact('log'));
    }

    public function exportCsv()
    {
        Auditify::logActivity('Export Action Logs (CSV)');

        $query = $this->buildFilterQuery()->with('user')->latest();
        $fileName = 'action_logs_' . now()->format('Ymd_His') . '.csv';

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
                'ID', 'User', 'Action', 'Module', 'Description', 
                'IP Address', 'Request URL', 'User Agent', 'Created At'
            ]);

            foreach ($query->cursor() as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Guest' . ($log->user_id ? ' (ID: ' . $log->user_id . ')' : ''),
                    $log->action,
                    $log->module,
                    $log->description,
                    $log->ip_address ?? '-',
                    $log->url ?? '-',
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
        Auditify::logActivity('Export Action Logs (Excel)');

        $query = $this->buildFilterQuery()->latest();
        $fileName = 'action_logs_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new ActionLogsExport($query), 
            $fileName
        );
    }
}
