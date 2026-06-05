<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Logs Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #111; }
        .meta { font-size: 9px; color: #666; margin-top: 4px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .report-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 6px 8px; font-weight: bold; text-align: left; }
        .report-table td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .severity-critical { background-color: #fce8e6; color: #c5221f; }
        .severity-high { background-color: #fef7e0; color: #b06000; }
        .severity-medium { background-color: #fff9e6; color: #b08500; }
        .severity-low { background-color: #e8f0fe; color: #1a73e8; }
        .status-resolved { background-color: #e6f4ea; color: #137333; }
        .status-pending { background-color: #fef7e0; color: #b06000; }
        .code { font-family: monospace; background-color: #f8f8f8; padding: 1px 3px; border-radius: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Auditify Security Incident Logs Report</div>
        <div class="meta">Generated at: {{ now()->format('Y-m-d H:i:s') }} | Total Records: {{ count($logs) }}</div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 10%">Severity</th>
                <th style="width: 20%">Title</th>
                <th style="width: 30%">Description</th>
                <th style="width: 10%">Origin IP</th>
                <th style="width: 10%">Status</th>
                <th style="width: 15%">Date/Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>
                        <span class="badge severity-{{ strtolower($log->severity) }}">
                            {{ $log->severity }}
                        </span>
                    </td>
                    <td><strong>{{ $log->title }}</strong></td>
                    <td>{{ $log->description }}</td>
                    <td><code>{{ $log->ip_address ?? 'System' }}</code></td>
                    <td>
                        <span class="badge status-{{ strtolower($log->status) === 'resolved' ? 'resolved' : 'pending' }}">
                            {{ $log->status }}
                        </span>
                    </td>
                    <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No security incidents reported. Clean sheet!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
