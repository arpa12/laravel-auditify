<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #111; }
        .meta { font-size: 9px; color: #666; margin-top: 4px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .report-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 6px 8px; font-weight: bold; text-align: left; }
        .report-table td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        .code { font-family: monospace; background-color: #f8f8f8; padding: 1px 3px; border-radius: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Auditify User Activity Logs Report</div>
        <div class="meta">Generated at: {{ now()->format('Y-m-d H:i:s') }} | Total Records: {{ count($logs) }}</div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">User</th>
                <th style="width: 30%">Activity</th>
                <th style="width: 20%">URL Path</th>
                <th style="width: 10%">IP Address</th>
                <th style="width: 15%">Date/Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->user->name ?? 'Guest User' }}</td>
                    <td><span class="code">{{ $log->activity }}</span></td>
                    <td style="word-break: break-all;">
                        {{ $log->url ? parse_url($log->url, PHP_URL_PATH) : '-' }}
                    </td>
                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                    <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No activity logs recorded in this timeframe.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
